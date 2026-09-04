<?php

namespace App\Http\Controllers;

use App\Mail\UserMail;
use App\Models\User;
use App\Models\Guard;
use App\Services\ActivityLogService;
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
    public function index(Request $request)
    {
        // load active (non-recycled) guard rows with related user accounts
        $guardsQuery = Guard::with('user');

        if (Schema::hasColumn('users', 'status')) {
            $guardsQuery->whereHas('user', function ($q) {
                $q->whereRaw("LOWER(COALESCE(status, '')) != ?", ['recycle_bin']);
            });
        } elseif (Schema::hasColumn('users', 'deleted_at')) {
            $guardsQuery->whereHas('user', function ($q) {
                $q->whereNull('deleted_at');
            });
        }

        $guards = $guardsQuery
            ->orderByDesc('guard_id')
            ->paginate($this->resolvePerPage($request))
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
                    'user_id' => $user->user_id ?? $user->id ?? null,
                    'guard_id' => $g->guard_id ?? null,
                    'name' => $name,
                    'first_name' => $user->first_name ?? null,
                    'last_name' => $user->last_name ?? null,
                    'email' => $user->email ?? '',
                    'badge_number' => $g->badge_number ?? $g->badge ?? null,
                    'station' => $g->station ?? null,
                ];
            });

        // Build recycle bin list for guard accounts
        $recycledGuards = collect([]);
        try {
            $recycledQuery = Guard::with('user')->orderByDesc('guard_id');

            if (Schema::hasColumn('users', 'status')) {
                $recycledQuery->whereHas('user', function ($q) {
                    $q->whereRaw("LOWER(COALESCE(status, '')) = ?", ['recycle_bin']);
                });
            } elseif (Schema::hasColumn('users', 'deleted_at')) {
                $recycledQuery->whereHas('user', function ($q) {
                    $q->whereNotNull('deleted_at');
                });
            } else {
                $recycledQuery->whereRaw('1 = 0');
            }

            $recycledGuards = $recycledQuery->get()->map(function ($g) {
                $user = $g->user;

                $name = '';
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
                    'user_id' => $user->user_id ?? $user->id ?? null,
                    'name' => $name,
                    'email' => $user->email ?? '—',
                    'badge_number' => $g->badge_number ?? $g->badge ?? '—',
                    'station' => $g->station ?? '—',
                ];
            })->values();
        } catch (\Exception $e) {
            logger()->debug('Failed to load recycled guards: ' . $e->getMessage());
            $recycledGuards = collect([]);
        }

        return view('admin.user', [
            'section' => 'guards',
            'guards' => $guards,
            'recycledGuards' => $recycledGuards,
        ]);
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

            ActivityLogService::log(
                action: 'Created User',
                module: 'User Management',
                description: ActivityLogService::actorLabel().' created user '.$fullName.' with role Guard.',
                entityType: 'User',
                entityId: $userId,
                newValues: [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'role' => 'Guard',
                    'badge_number' => $data['badge_number'] ?? null,
                    'station' => $data['station'] ?? null,
                    'status' => 'Active',
                ]
            );

            return redirect()->back()->with('success', 'Guard account created successfully. Login details and password setup link were sent by email.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to create guard: ' . $e->getMessage()]);
        }
    }

    /**
     * Update an existing guard (and related user) account.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'badge_number' => ['nullable', 'string', 'max:255'],
            'station' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();

        try {
            $user = User::where('user_id', $id)->first();
            if (! $user) {
                $user = User::find($id);
            }

            if (! $user) {
                DB::rollBack();

                return redirect()->back()->withErrors(['error' => 'Guard user not found.']);
            }

            $targetName = ActivityLogService::userDisplayName($user);
            $userId = $user->{$user->getKeyName()} ?? ($user->id ?? $user->user_id ?? null);

            $guard = Guard::where('user_id', $userId)->first();
            if (! $guard) {
                DB::rollBack();

                return redirect()->back()->withErrors(['error' => 'Guard profile not found.']);
            }

            $oldValues = [
                'first_name' => $user->first_name ?? null,
                'last_name' => $user->last_name ?? null,
                'name' => $user->name ?? null,
                'badge_number' => $guard->badge_number ?? null,
                'station' => $guard->station ?? null,
            ];

            if (Schema::hasColumn('users', 'first_name') && Schema::hasColumn('users', 'last_name')) {
                $user->first_name = $data['first_name'];
                $user->last_name = $data['last_name'];
            } elseif (Schema::hasColumn('users', 'name')) {
                $user->name = trim($data['first_name'].' '.$data['last_name']);
            } else {
                $user->name = trim($data['first_name'].' '.$data['last_name']);
            }

            $user->save();

            $guard->badge_number = $data['badge_number'] ?? null;
            $guard->station = $data['station'] ?? null;
            $guard->save();

            DB::commit();

            ActivityLogService::log(
                action: 'Updated User',
                module: 'User Management',
                description: ActivityLogService::actorLabel().' updated the account information of '.$targetName.'.',
                entityType: 'User',
                entityId: $userId,
                oldValues: $oldValues,
                newValues: [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'badge_number' => $data['badge_number'] ?? null,
                    'station' => $data['station'] ?? null,
                ]
            );

            return redirect()->back()->with('success', 'Guard account updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Failed to update guard: '.$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'Failed to update guard: '.$e->getMessage()]);
        }
    }

    /**
     * Move a guard account to recycle bin.
     */
    public function recycle($id)
    {
        DB::beginTransaction();

        try {
            $user = User::where('user_id', $id)->first();
            if (! $user) {
                $user = User::find($id);
            }

            if (! $user) {
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => 'Guard user not found.']);
            }

            $targetName = ActivityLogService::userDisplayName($user);
            $previousStatus = Schema::hasColumn('users', 'status')
                ? ((string) ($user->status ?? 'Active') ?: 'Active')
                : 'Active';

            if (Schema::hasColumn('users', 'status')) {
                $user->status = 'recycle_bin';
            } elseif (Schema::hasColumn('users', 'deleted_at')) {
                $user->deleted_at = now();
            } else {
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => 'Recycle bin is not supported by current users table schema.']);
            }

            $user->save();

            DB::commit();

            ActivityLogService::log(
                action: 'Changed User Status',
                module: 'User Management',
                description: ActivityLogService::actorLabel()." changed {$targetName}'s account status from {$previousStatus} to Inactive.",
                entityType: 'User',
                entityId: $user->user_id ?? $id,
                oldValues: ['status' => $previousStatus],
                newValues: ['status' => 'Inactive']
            );

            return redirect()->back()->with('success', 'Guard account moved to recycle bin.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to recycle guard account: ' . $e->getMessage()]);
        }
    }

    /**
     * Restore a guard account from recycle bin.
     */
    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $user = User::where('user_id', $id)->first();
            if (! $user) {
                $user = User::find($id);
            }

            if (! $user) {
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => 'Guard user not found.']);
            }

            $targetName = ActivityLogService::userDisplayName($user);

            if (Schema::hasColumn('users', 'status')) {
                $user->status = 'Active';
            } elseif (Schema::hasColumn('users', 'deleted_at')) {
                $user->deleted_at = null;
            } else {
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => 'Recycle bin is not supported by current users table schema.']);
            }

            $user->save();

            DB::commit();

            ActivityLogService::log(
                action: 'Changed User Status',
                module: 'User Management',
                description: ActivityLogService::actorLabel()." changed {$targetName}'s account status from Inactive to Active.",
                entityType: 'User',
                entityId: $user->user_id ?? $id,
                oldValues: ['status' => 'Inactive'],
                newValues: ['status' => 'Active']
            );

            return redirect()->back()->with('success', 'Guard account restored from recycle bin.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to restore guard account: ' . $e->getMessage()]);
        }
    }

    /**
     * Permanently delete a guard account from the recycle bin.
     */
    public function forceDestroy($id)
    {
        DB::beginTransaction();

        try {
            $user = User::where('user_id', $id)->first();
            if (! $user) {
                $user = User::find($id);
            }

            if (! $user) {
                DB::rollBack();

                return redirect()->back()->withErrors(['error' => 'Guard user not found.']);
            }

            if (! $this->isInRecycleBin($user)) {
                DB::rollBack();

                return redirect()->back()->withErrors(['error' => 'Only recycled guard accounts can be permanently deleted.']);
            }

            $targetName = ActivityLogService::userDisplayName($user);
            $userId = $user->{$user->getKeyName()} ?? ($user->id ?? $user->user_id ?? null);
            $email = (string) ($user->email ?? '');

            $oldValues = [
                'first_name' => $user->first_name ?? null,
                'last_name' => $user->last_name ?? null,
                'name' => $user->name ?? null,
                'email' => $email,
                'role' => 'Guard',
                'status' => $user->status ?? 'recycle_bin',
            ];

            if ($userId !== null) {
                if (Schema::hasTable('guard')) {
                    Guard::where('user_id', $userId)->delete();
                }

                $this->nullifyUserReferences((int) $userId);
            }

            if ($email !== '' && Schema::hasTable('password_reset_tokens')) {
                DB::table('password_reset_tokens')->where('email', $email)->delete();
            }

            $user->delete();

            DB::commit();

            ActivityLogService::log(
                action: 'Deleted User',
                module: 'User Management',
                description: ActivityLogService::actorLabel().' permanently deleted recycled guard account '.$targetName.'.',
                entityType: 'User',
                entityId: $userId,
                oldValues: $oldValues,
                newValues: null
            );

            return redirect()->back()->with('success', 'Guard account permanently deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Failed to permanently delete guard: '.$e->getMessage());

            return redirect()->back()->withErrors([
                'error' => 'Failed to permanently delete guard account. It may still be referenced by historical records.',
            ]);
        }
    }

    protected function isInRecycleBin(User $user): bool
    {
        if (Schema::hasColumn('users', 'status')) {
            return strtolower(trim((string) ($user->status ?? ''))) === 'recycle_bin';
        }

        if (Schema::hasColumn('users', 'deleted_at')) {
            return ! empty($user->deleted_at);
        }

        return false;
    }

    protected function nullifyUserReferences(int $userId): void
    {
        if (Schema::hasTable('visit') && Schema::hasColumn('visit', 'on_duty_guard_id')) {
            DB::table('visit')->where('on_duty_guard_id', $userId)->update(['on_duty_guard_id' => null]);
        }

        if (Schema::hasTable('guard_duty_shifts') && Schema::hasColumn('guard_duty_shifts', 'guard_user_id')) {
            // Historical shifts keep their row; drop the FK link so the user can be removed.
            if (Schema::hasColumn('guard_duty_shifts', 'clock_out_at')) {
                DB::table('guard_duty_shifts')
                    ->where('guard_user_id', $userId)
                    ->whereNull('clock_out_at')
                    ->update(['clock_out_at' => now()]);
            }

            // Prefer delete of orphan shifts if guard_user_id is required; otherwise nullify.
            try {
                DB::table('guard_duty_shifts')->where('guard_user_id', $userId)->update(['guard_user_id' => null]);
            } catch (\Throwable $e) {
                DB::table('guard_duty_shifts')->where('guard_user_id', $userId)->delete();
            }
        }

        if (Schema::hasTable('login_attempts') && Schema::hasColumn('login_attempts', 'user_id')) {
            DB::table('login_attempts')->where('user_id', $userId)->update(['user_id' => null]);
        }

        if (Schema::hasTable('activity_logs') && Schema::hasColumn('activity_logs', 'user_id')) {
            DB::table('activity_logs')->where('user_id', $userId)->update(['user_id' => null]);
        }
    }
}
