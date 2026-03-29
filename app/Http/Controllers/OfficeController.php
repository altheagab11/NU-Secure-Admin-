<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class OfficeController extends Controller
{
    /**
     * Display the offices page with office list for dropdowns.
     */
    public function index(Request $request)
    {
        // Fetch office list from `office` table. Select only the columns we need.
        // Keep it defensive: if table doesn't exist this will throw — caller can adjust.
        $offices = [];

        try {
            $offices = DB::table('office')
                ->select('office_id', 'office_name')
                ->where('is_active', true)
                ->orderBy('office_name')
                ->get();
        } catch (\Exception $e) {
            // Log and continue with empty list so UI doesn't break
            logger()->error('Failed to load offices for OfficeController@index: ' . $e->getMessage());
            $offices = collect([]);
        }

        // Also fetch office staff users for the Office User Accounts table
        // Use a resilient approach: load office_staff rows then fetch related user and office per-row.
        $officeUsers = collect([]);
        try {
            $staffRows = DB::table('office_staff')->get();

            $officeUsers = $staffRows->map(function ($r) {
                // determine user id prop name
                $userId = $r->user_id ?? $r->user ?? $r->users_user_id ?? null;

                $user = null;
                if ($userId) {
                    $user = DB::table('users')->where('user_id', $userId)->first();
                    if (! $user) {
                        // try fallback primary key 'id'
                        $user = DB::table('users')->where('id', $userId)->first();
                    }
                }

                $office = null;
                $officeId = $r->office_id ?? $r->office ?? null;
                if ($officeId) {
                    $office = DB::table('office')->where('office_id', $officeId)->first();
                    if (! $office) {
                        $office = DB::table('office')->where('id', $officeId)->first();
                    }
                }

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
                    'name' => $name,
                    'email' => $user->email ?? ($r->email ?? ''),
                    'office_name' => $office->office_name ?? ($office->name ?? ($r->office_name ?? '—')),
                    'position' => $r->position ?? '—',
                ];
            })->filter(function ($x) {
                // filter out rows without any identifying data
                return ! empty($x->email) || ! empty($x->name);
            })->values();
        } catch (\Exception $e) {
            logger()->error('Failed to load office users for OfficeController@index: ' . $e->getMessage());
            $officeUsers = collect([]);
        }

        // Compute totals for display: number of office users and number of offices.
        // Use collection count for office users (we always make $officeUsers a collection).
        $totalUsers = is_countable($officeUsers) ? count($officeUsers) : (method_exists($officeUsers, 'count') ? $officeUsers->count() : 0);

        // Try a direct DB count for offices (more reliable and avoids potential analyzer complaints).
        try {
            $totalOffices = DB::table('office')->where('is_active', true)->count();
        } catch (\Exception $e) {
            // fallback to 0 if counting fails
            $totalOffices = 0;
        }

        return view('admin.user', [
            'section' => 'offices',
            // pass officeOptions for dropdown
            'officeOptions' => $offices,
            // pass officeUsers to populate the main table
            'offices' => $officeUsers,
            // totals for header display
            'totalUsers' => $totalUsers,
            'totalOffices' => $totalOffices,
        ]);
    }

    /**
     * Store a newly created office user and office_staff row.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'office_id' => ['required', 'integer'],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();

        try {
            $fullName = trim($data['first_name'] . ' ' . $data['last_name']);
            $passwordPlain = Str::random(12);

            $useNameColumn = Schema::hasColumn('users', 'name');
            $useFirstLast = Schema::hasColumn('users', 'first_name') && Schema::hasColumn('users', 'last_name');
            $usePasswordHash = Schema::hasColumn('users', 'password_hash');

            $user = new \App\Models\User();
            if ($useFirstLast) {
                $user->first_name = $data['first_name'];
                $user->last_name = $data['last_name'];
            } elseif ($useNameColumn) {
                $user->name = $fullName;
            } else {
                $user->name = $fullName;
            }

            $user->email = $data['email'];

            // assign role_id for office users if column exists; assume role_id 3 for office staff
            if (Schema::hasColumn('users', 'role_id')) {
                $user->role_id = 3; // assumption: 3 = office staff
            }

            // set status to active when user is created if supported
            if (Schema::hasColumn('users', 'status')) {
                $user->status = 'active';
            }

            if ($usePasswordHash) {
                $user->password_hash = Hash::make($passwordPlain);
            } else {
                $user->password = Hash::make($passwordPlain);
            }

            $hasCreated = Schema::hasColumn('users', 'created_at');
            $hasUpdated = Schema::hasColumn('users', 'updated_at');
            if (! $hasUpdated) {
                $user->timestamps = false;
                if ($hasCreated) $user->created_at = now();
            }

            $user->save();

            $userId = $user->{$user->getKeyName()} ?? ($user->id ?? $user->user_id ?? null);

            // Insert into office_staff table. We assume columns: user_id, office_id, position
            DB::table('office_staff')->insert([
                'user_id' => $userId,
                'office_id' => $data['office_id'],
                'position' => $data['position'] ?? null,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Office user created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Failed to create office user: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to create office user: ' . $e->getMessage()]);
        }
    }
}
