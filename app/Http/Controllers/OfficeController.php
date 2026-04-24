<?php

namespace App\Http\Controllers;

use App\Mail\UserMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
            // Prepare a query for office_staff with optional joins to users and office
            $sTable = 'office_staff';
            $staffQuery = DB::table($sTable . ' as s');

            // decide user join key
            $userJoinColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
            $staffUserColumn = Schema::hasColumn($sTable, 'user_id') ? 's.user_id' : (Schema::hasColumn($sTable, 'user') ? 's.user' : 's.users_user_id');

            // join users if possible
            if (Schema::hasTable('users')) {
                $staffQuery->leftJoin('users as u', "u.{$userJoinColumn}", '=', DB::raw($staffUserColumn));

                // show only active/non-recycled office users in main table
                if (Schema::hasColumn('users', 'status')) {
                    $staffQuery->where(function ($q) {
                        $q->whereNull('u.status')->orWhere('u.status', '!=', 'recycle_bin');
                    });
                } elseif (Schema::hasColumn('users', 'deleted_at')) {
                    $staffQuery->whereNull('u.deleted_at');
                }
            }

            // join office table for office name
            $officeJoinColumn = Schema::hasColumn('office', 'office_id') ? 'office_id' : 'id';
            $staffOfficeColumn = Schema::hasColumn($sTable, 'office_id') ? 's.office_id' : (Schema::hasColumn($sTable, 'office') ? 's.office' : null);
            if ($staffOfficeColumn) {
                $staffQuery->leftJoin('office as o', "o.{$officeJoinColumn}", '=', DB::raw($staffOfficeColumn));
            }

            // select columns we need (use aliases to ensure consistent access)
            $selects = ['s.*'];
            if (Schema::hasTable('users')) {
                if (Schema::hasColumn('users', 'first_name')) $selects[] = 'u.first_name';
                if (Schema::hasColumn('users', 'last_name')) $selects[] = 'u.last_name';
                if (Schema::hasColumn('users', 'name')) $selects[] = 'u.name';
                if (Schema::hasColumn('users', 'email')) $selects[] = 'u.email';
                // include the user's primary id (either user_id or id) so the view can perform edits
                $selects[] = "u.{$userJoinColumn} as u_id";
            }
            if ($staffOfficeColumn) {
                if (Schema::hasColumn('office', 'office_name')) $selects[] = 'o.office_name';
                if (Schema::hasColumn('office', 'name')) $selects[] = 'o.name as office_name_alt';
            }

            $staffQuery->select($selects);

            // Apply filters from request: office, position, search
            $filterOffice = $request->query('office');
            $filterPosition = $request->query('position');
            $filterSearch = trim((string) $request->query('search', ''));

            if ($filterOffice) {
                // apply to s.office_id or s.office depending on column
                if (Str::contains($staffOfficeColumn, 'office_id')) {
                    $staffQuery->where('s.office_id', $filterOffice);
                } else {
                    $staffQuery->where(DB::raw($staffOfficeColumn), $filterOffice);
                }
            }

            if ($filterPosition) {
                $staffQuery->where('s.position', $filterPosition);
            }

            if ($filterSearch !== '') {
                // build search across available user columns
                $staffQuery->where(function ($q) use ($filterSearch) {
                    if (Schema::hasColumn('users', 'first_name')) {
                        $q->orWhere('u.first_name', 'ilike', "%{$filterSearch}%");
                    }
                    if (Schema::hasColumn('users', 'last_name')) {
                        $q->orWhere('u.last_name', 'ilike', "%{$filterSearch}%");
                    }
                    if (Schema::hasColumn('users', 'name')) {
                        $q->orWhere('u.name', 'ilike', "%{$filterSearch}%");
                    }
                    if (Schema::hasColumn('users', 'email')) {
                        $q->orWhere('u.email', 'ilike', "%{$filterSearch}%");
                    }
                    // fallback: search in office_staff email column if exists
                    if (Schema::hasColumn('office_staff', 'email')) {
                        $q->orWhere('s.email', 'ilike', "%{$filterSearch}%");
                    }
                });
            }

            // paginate with current query string so filters persist on links
            $staffRows = $staffQuery->orderBy('s.office_id')->paginate(10)->withQueryString();

            // map over the paginator's collection and then set it back on the paginator
            $mapped = $staffRows->getCollection()->map(function ($r) {
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
                // try to use joined columns first
                if (isset($r->office_name) && $r->office_name) {
                    $office = (object) ['office_name' => $r->office_name];
                } elseif (isset($r->office_name_alt) && $r->office_name_alt) {
                    $office = (object) ['office_name' => $r->office_name_alt];
                } else {
                    $officeId = $r->office_id ?? $r->office ?? null;
                    if ($officeId) {
                        $office = DB::table('office')->where('office_id', $officeId)->first();
                        if (! $office) {
                            $office = DB::table('office')->where('id', $officeId)->first();
                        }
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
                    'user_id' => $userId ?? ($r->u_id ?? null),
                    'name' => $name,
                    'email' => $user->email ?? ($r->email ?? ''),
                    'office_id' => $r->office_id ?? $r->office ?? null,
                    'office_name' => $office->office_name ?? ($office->name ?? ($r->office_name ?? '—')),
                    'position' => $r->position ?? '—',
                ];
            });

            $mapped = $mapped->filter(function ($x) {
                // filter out rows without any identifying data
                return ! empty($x->email) || ! empty($x->name);
            })->values();

            // set the transformed collection back to the paginator and use that as officeUsers
            $staffRows->setCollection($mapped);
            $officeUsers = $staffRows;
        } catch (\Exception $e) {
            logger()->error('Failed to load office users for OfficeController@index: ' . $e->getMessage());
            $officeUsers = collect([]);
        }

        // fetch distinct positions for filter dropdown (non-recycled users only)
        try {
            $positionsQuery = DB::table('office_staff as s');
            if (Schema::hasTable('users')) {
                $userJoinColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
                $staffUserColumn = Schema::hasColumn('office_staff', 'user_id') ? 's.user_id' : (Schema::hasColumn('office_staff', 'user') ? 's.user' : 's.users_user_id');
                $positionsQuery->leftJoin('users as u', "u.{$userJoinColumn}", '=', DB::raw($staffUserColumn));

                if (Schema::hasColumn('users', 'status')) {
                    $positionsQuery->where(function ($q) {
                        $q->whereNull('u.status')->orWhere('u.status', '!=', 'recycle_bin');
                    });
                } elseif (Schema::hasColumn('users', 'deleted_at')) {
                    $positionsQuery->whereNull('u.deleted_at');
                }
            }

            $positions = $positionsQuery->distinct()->pluck('s.position')->filter()->values();
        } catch (\Exception $e) {
            $positions = collect([]);
        }

        // Compute totals for display: number of office users and number of offices.
        // Use collection count for office users (we always make $officeUsers a collection).
        // If we have a paginator, use its total() for the overall count; otherwise fallback to previous behaviour
        if ($officeUsers instanceof \Illuminate\Pagination\LengthAwarePaginator || $officeUsers instanceof \Illuminate\Contracts\Pagination\Paginator) {
            $totalUsers = method_exists($officeUsers, 'total') ? $officeUsers->total() : ($officeUsers->count() ?? 0);
        } else {
            $totalUsers = is_countable($officeUsers) ? count($officeUsers) : (method_exists($officeUsers, 'count') ? $officeUsers->count() : 0);
        }

        // Try a direct DB count for offices (more reliable and avoids potential analyzer complaints).
        try {
            $totalOffices = DB::table('office')->where('is_active', true)->count();
        } catch (\Exception $e) {
            // fallback to 0 if counting fails
            $totalOffices = 0;
        }

        // Build overall office summaries (counts per office) for the summary cards (non-recycled users only).
        try {
            $summaryRowsQuery = DB::table('office_staff as s')
                ->select('s.office_id', DB::raw('count(*) as cnt'))
                ->groupBy('s.office_id');

            if (Schema::hasTable('users')) {
                $userJoinColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
                $staffUserColumn = Schema::hasColumn('office_staff', 'user_id') ? 's.user_id' : (Schema::hasColumn('office_staff', 'user') ? 's.user' : 's.users_user_id');
                $summaryRowsQuery->leftJoin('users as u', "u.{$userJoinColumn}", '=', DB::raw($staffUserColumn));

                if (Schema::hasColumn('users', 'status')) {
                    $summaryRowsQuery->where(function ($q) {
                        $q->whereNull('u.status')->orWhere('u.status', '!=', 'recycle_bin');
                    });
                } elseif (Schema::hasColumn('users', 'deleted_at')) {
                    $summaryRowsQuery->whereNull('u.deleted_at');
                }
            }

            $summaryRows = $summaryRowsQuery->get();

            $officeSummaries = $summaryRows->map(function ($r) {
                $office = DB::table('office')->where('office_id', $r->office_id)->first();
                if (! $office) {
                    $office = DB::table('office')->where('id', $r->office_id)->first();
                }

                $name = $office->office_name ?? $office->name ?? 'Unknown Office';
                return ['name' => $name, 'count' => (int) ($r->cnt ?? 0)];
            })->values();
        } catch (\Exception $e) {
            logger()->debug('Failed to build office summaries: ' . $e->getMessage());
            $officeSummaries = collect([]);
        }

        // Build recycle bin listing for office users.
        try {
            $recycledQuery = DB::table('office_staff as s');
            $userJoinColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
            $staffUserColumn = Schema::hasColumn('office_staff', 'user_id') ? 's.user_id' : (Schema::hasColumn('office_staff', 'user') ? 's.user' : 's.users_user_id');
            $recycledQuery->leftJoin('users as u', "u.{$userJoinColumn}", '=', DB::raw($staffUserColumn));

            $officeJoinColumn = Schema::hasColumn('office', 'office_id') ? 'office_id' : 'id';
            $staffOfficeColumn = Schema::hasColumn('office_staff', 'office_id') ? 's.office_id' : (Schema::hasColumn('office_staff', 'office') ? 's.office' : null);
            if ($staffOfficeColumn) {
                $recycledQuery->leftJoin('office as o', "o.{$officeJoinColumn}", '=', DB::raw($staffOfficeColumn));
            }

            if (Schema::hasColumn('users', 'status')) {
                $recycledQuery->where('u.status', 'recycle_bin');
            } elseif (Schema::hasColumn('users', 'deleted_at')) {
                $recycledQuery->whereNotNull('u.deleted_at');
            } else {
                $recycledQuery->whereRaw('1 = 0');
            }

            $officeNameCandidates = [];
            if (Schema::hasColumn('office', 'office_name')) {
                $officeNameCandidates[] = 'o.office_name';
            }
            if (Schema::hasColumn('office', 'name')) {
                $officeNameCandidates[] = 'o.name';
            }
            $officeNameExpr = count($officeNameCandidates) > 0
                ? ('COALESCE(' . implode(', ', $officeNameCandidates) . ", '—')")
                : "'—'";

            $recycleSelects = [
                DB::raw("u.{$userJoinColumn} as user_id"),
                DB::raw("{$officeNameExpr} as office_name"),
            ];

            if (Schema::hasColumn('users', 'first_name')) {
                $recycleSelects[] = 'u.first_name';
            }
            if (Schema::hasColumn('users', 'last_name')) {
                $recycleSelects[] = 'u.last_name';
            }
            if (Schema::hasColumn('users', 'name')) {
                $recycleSelects[] = 'u.name';
            }
            if (Schema::hasColumn('users', 'email')) {
                $recycleSelects[] = 'u.email';
            }
            if (Schema::hasColumn('office_staff', 'position')) {
                $recycleSelects[] = 's.position';
            }

            $recycledRows = $recycledQuery
                ->select($recycleSelects)
                ->orderBy('u.email')
                ->get();

            $recycledOffices = $recycledRows->map(function ($r) {
                $fullName = trim((($r->first_name ?? '') . ' ' . ($r->last_name ?? '')));
                if ($fullName === '') {
                    $fullName = $r->name ?? ($r->email ?? '');
                }

                return (object) [
                    'user_id' => $r->user_id,
                    'name' => $fullName,
                    'email' => $r->email ?? '',
                    'office_name' => $r->office_name ?? '—',
                    'position' => $r->position ?? '—',
                ];
            })->values();
        } catch (\Exception $e) {
            logger()->debug('Failed to load recycle bin office users: ' . $e->getMessage());
            $recycledOffices = collect([]);
        }

        return view('admin.user', [
            'section' => 'offices',
            // pass officeOptions for dropdown
            'officeOptions' => $offices,
            // pass officeUsers to populate the main table
            'offices' => $officeUsers,
            // overall office summaries (counts across all users)
            'officeSummaries' => $officeSummaries,
            // filter dropdown options
            'positions' => $positions,
            // totals for header display
            'totalUsers' => $totalUsers,
            'totalOffices' => $totalOffices,
            // recycle bin data
            'recycledOffices' => $recycledOffices,
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

            // Create password setup token and email office user the onboarding link.
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

            return redirect()->back()->with('success', 'Office user created successfully. Login details and password setup link were sent by email.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Failed to create office user: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to create office user: ' . $e->getMessage()]);
        }
    }

    /**
     * Update an existing office user (name, office assignment, position).
     * Email is intentionally not editable in update flow.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'office_id' => ['required', 'integer'],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            // Try to locate the user model by common PKs
            $userModel = \App\Models\User::where('user_id', $id)->first();
            if (! $userModel) {
                $userModel = \App\Models\User::find($id);
            }

            if (! $userModel) {
                return redirect()->back()->withErrors(['error' => 'User not found.']);
            }

            // Update name fields depending on schema
            if (Schema::hasColumn('users', 'first_name') && Schema::hasColumn('users', 'last_name')) {
                $userModel->first_name = $data['first_name'];
                $userModel->last_name = $data['last_name'];
            } elseif (Schema::hasColumn('users', 'name')) {
                $userModel->name = trim($data['first_name'] . ' ' . $data['last_name']);
            } else {
                // Fallback
                $userModel->name = trim($data['first_name'] . ' ' . $data['last_name']);
            }

            $userModel->save();

            // Update or insert office_staff row
            $uId = $userModel->user_id ?? $userModel->id ?? null;
            if ($uId) {
                $updated = DB::table('office_staff')->where('user_id', $uId)->update([
                    'office_id' => $data['office_id'],
                    'position' => $data['position'] ?? null,
                ]);

                if (! $updated) {
                    // try other possible column names
                    $updated = DB::table('office_staff')->where('user', $uId)->update([
                        'office_id' => $data['office_id'],
                        'position' => $data['position'] ?? null,
                    ]);
                }

                if (! $updated) {
                    // insert if no existing row
                    DB::table('office_staff')->insert([
                        'user_id' => $uId,
                        'office_id' => $data['office_id'],
                        'position' => $data['position'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Office user updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Failed to update office user: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update office user: ' . $e->getMessage()]);
        }
    }

    /**
     * Move an office user to recycle bin (soft delete style).
     */
    public function recycle($id)
    {
        DB::beginTransaction();
        try {
            $userModel = \App\Models\User::where('user_id', $id)->first();
            if (! $userModel) {
                $userModel = \App\Models\User::find($id);
            }

            if (! $userModel) {
                return redirect()->back()->withErrors(['error' => 'User not found.']);
            }

            if (Schema::hasColumn('users', 'status')) {
                $userModel->status = 'recycle_bin';
            } elseif (Schema::hasColumn('users', 'deleted_at')) {
                $userModel->deleted_at = now();
            } else {
                return redirect()->back()->withErrors(['error' => 'Recycle bin is not supported by current users table schema.']);
            }

            $userModel->save();

            DB::commit();
            return redirect()->back()->with('success', 'Office user moved to recycle bin.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Failed to recycle office user: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to recycle office user: ' . $e->getMessage()]);
        }
    }

    /**
     * Restore an office user from recycle bin.
     */
    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $userModel = \App\Models\User::where('user_id', $id)->first();
            if (! $userModel) {
                $userModel = \App\Models\User::find($id);
            }

            if (! $userModel) {
                return redirect()->back()->withErrors(['error' => 'User not found.']);
            }

            if (Schema::hasColumn('users', 'status')) {
                $userModel->status = 'active';
            } elseif (Schema::hasColumn('users', 'deleted_at')) {
                $userModel->deleted_at = null;
            } else {
                return redirect()->back()->withErrors(['error' => 'Recycle bin is not supported by current users table schema.']);
            }

            $userModel->save();

            DB::commit();
            return redirect()->back()->with('success', 'Office user restored from recycle bin.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Failed to restore office user: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to restore office user: ' . $e->getMessage()]);
        }
    }
}
