<?php

namespace App\Http\Controllers;

use App\Mail\UserMail;
use App\Models\User;
use App\Models\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class GuardController extends Controller
{
    /**
     * Display a listing of guards for the admin UI.
     */
    public function index()
    {
        // load guard rows with related user accounts, 10 per page
        $guards = Guard::with('user')
            ->orderByDesc('guard_id')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($g) {
                $user = $g->user;

                // derive a display name from available user columns
                $name = null;
                if ($user) {
                    $first = $user->first_name ?? null;
                    $last = $user->last_name ?? null;
                    if ($first || $last) {
                        $name = trim(($first ?: '') . ' ' . ($last ?: ''));
                    } elseif (! empty($user->name)) {
                        $name = $user->name;
                    } else {
                        $name = $user->email ?? '';
                    }
                }

                return (object) [
                    'name' => $name,
                    'email' => $user->email ?? '',
                    'badge_number' => $g->badge_number ?? $g->badge ?? null,
                    'station' => $g->station ?? null,
                ];
            });

        return view('admin.user', ['section' => 'guards', 'guards' => $guards]);
    }
    /**
     * Store a newly created guard (and user) in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'badge_number' => ['nullable', 'string', 'max:255'],
            'station' => ['nullable', 'string', 'max:255'],
        ]);

        // Create user + guard inside transaction
        DB::beginTransaction();

        try {
            $fullName = trim($data['first_name'] . ' ' . $data['last_name']);

            $passwordPlain = Str::random(12);

            // Detect users table schema variations (some environments have first_name/last_name and password_hash)
            $useNameColumn = Schema::hasColumn('users', 'name');
            $useFirstLast = Schema::hasColumn('users', 'first_name') && Schema::hasColumn('users', 'last_name');
            $usePasswordHash = Schema::hasColumn('users', 'password_hash');

            $user = new User();
            // prefer first_name/last_name when available
            if ($useFirstLast) {
                $user->first_name = $data['first_name'];
                $user->last_name = $data['last_name'];
            } elseif ($useNameColumn) {
                // older schema with single name field
                $user->name = $fullName;
            } else {
                // fallback: set name property if model accepts it
                $user->name = $fullName;
            }

            $user->email = $data['email'];

            // If users table has role_id, assign role '2' for guard accounts
            if (Schema::hasColumn('users', 'role_id')) {
                $user->role_id = 2;
            }

            // set status to active when user is created if supported
            if (Schema::hasColumn('users', 'status')) {
                $user->status = 'active';
            }

            if ($usePasswordHash) {
                $user->password_hash = Hash::make($passwordPlain);
            } else {
                // if the model/casts expects `password` we set it (it may auto-hash via cast)
                $user->password = Hash::make($passwordPlain);
            }

            // Handle cases where users table doesn't have updated_at/created_at
            $hasCreated = Schema::hasColumn('users', 'created_at');
            $hasUpdated = Schema::hasColumn('users', 'updated_at');
            if (! $hasUpdated) {
                // prevent Eloquent from trying to write updated_at
                $user->timestamps = false;
                if ($hasCreated) {
                    $user->created_at = now();
                }
            }

            $user->save();

            // determine primary key field for user id
            // Use model primary key name to retrieve correct id field (id or user_id)
            $userId = $user->{$user->getKeyName()} ?? ($user->id ?? $user->user_id ?? null);

            // create guard record - adapt fields to your guard table
            $guard = Guard::create([
                'user_id' => $userId,
                'badge_number' => $data['badge_number'] ?? null,
                'station' => $data['station'] ?? null,
            ]);

            // create password setup/reset token and send onboarding email
            $resetToken = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($resetToken),
                    'created_at' => now(),
                ]
            );

            $setupUrl = route('password.setup.form', [
                'token' => $resetToken,
                'email' => $user->email,
            ]);

            Mail::to($user->email)->send(new UserMail(
                $fullName,
                $user->email,
                $passwordPlain,
                $setupUrl
            ));

            DB::commit();

            return redirect()->back()->with('success', 'Guard account created successfully. Login details and password setup link were sent by email.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to create guard: ' . $e->getMessage()]);
        }
    }
}
