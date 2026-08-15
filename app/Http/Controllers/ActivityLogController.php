<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class ActivityLogController extends Controller
{
    /**
     * @var list<string>
     */
    protected const MODULES = [
        'Authentication',
        'Visitor Monitoring',
        'Office Scanning',
        'Alerts',
        'User Management',
        'Enrollee Processing',
        'Reports',
        'Office Management',
        'Notifications',
        'System',
    ];

    /**
     * @var list<string>
     */
    protected const ACTIONS = [
        'Login',
        'Logout',
        'Failed Login',
        'Created User',
        'Updated User',
        'Changed User Status',
        'Visitor Registered',
        'Visitor Entered',
        'Visitor Exited',
        'QR Scan',
        'Wrong Office Detected',
        'Scan Rejected',
        'Alert Generated',
        'Resolved Alert',
        'Enrollee Progress Updated',
        'Generated Daily Report',
        'Generated Date-Range Report',
        'Regenerated Report',
        'Report Downloaded',
        'Failed Report Generation',
    ];

    /**
     * @var list<string>
     */
    protected const SORTABLE = [
        'created_at',
        'user',
        'module',
        'action',
        'status',
    ];

    public function index(): View
    {
        return view('admin.activity-logs');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateFilters($request);
            $query = $this->filteredQuery($validated);
            $sortBy = $validated['sort_by'];
            $sortDirection = $validated['sort_direction'];

            if ($sortBy === 'user') {
                $query->leftJoin('users as activity_log_users', 'activity_log_users.user_id', '=', 'activity_logs.user_id')
                    ->orderByRaw("LOWER(TRIM(COALESCE(activity_log_users.first_name, activity_log_users.name, ''))) {$sortDirection}")
                    ->orderBy('activity_logs.log_id', 'desc')
                    ->select('activity_logs.*');
            } else {
                $query->orderBy('activity_logs.'.$sortBy, $sortDirection)
                    ->orderBy('activity_logs.log_id', 'desc');
            }

            $paginator = $query
                ->with('user')
                ->paginate($validated['per_page'])
                ->withQueryString();

            $rows = $paginator->getCollection()->map(fn (ActivityLog $log) => $this->serializeListRow($log));

            return response()->json([
                'success' => true,
                'data' => $rows,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => max(1, $paginator->lastPage()),
                    'per_page' => $paginator->perPage(),
                    'from' => $paginator->firstItem() ?? 0,
                    'to' => $paginator->lastItem() ?? 0,
                    'total' => $paginator->total(),
                ],
                'empty_reason' => $paginator->total() === 0
                    ? ($this->hasActiveFilters($validated) ? 'filtered' : 'empty')
                    : null,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load activity logs. Please try again.',
            ], 500);
        }
    }

    public function summary(Request $request): JsonResponse
    {
        try {
            [$todayStart, $todayEnd] = $this->todayBounds();

            $todayQuery = ActivityLog::query()
                ->whereBetween('created_at', [$todayStart, $todayEnd]);

            $totalToday = (clone $todayQuery)->count();
            $activeUsersToday = (int) (clone $todayQuery)
                ->whereNotNull('user_id')
                ->selectRaw('COUNT(DISTINCT user_id) as aggregate_count')
                ->value('aggregate_count');
            $failedToday = (clone $todayQuery)
                ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", ['failed'])
                ->count();

            $topModule = (clone $todayQuery)
                ->selectRaw('module, COUNT(*) as aggregate_count')
                ->groupBy('module')
                ->orderByDesc('aggregate_count')
                ->orderBy('module')
                ->value('module');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_today' => $totalToday,
                    'active_users_today' => $activeUsersToday,
                    'failed_today' => $failedToday,
                    'most_active_module' => $topModule ?: '—',
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load activity summary. Please try again.',
            ], 500);
        }
    }

    public function filters(): JsonResponse
    {
        try {
            $userColumns = ['user_id', 'first_name', 'last_name', 'email', 'role_id'];
            if (Schema::hasColumn('users', 'name')) {
                $userColumns[] = 'name';
            }

            $users = User::query()
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->orderBy('email')
                ->get($userColumns)
                ->map(function (User $user) {
                    return [
                        'user_id' => (int) $user->user_id,
                        'name' => ActivityLogService::userDisplayName($user),
                        'email' => (string) ($user->email ?? ''),
                        'role' => ActivityLogService::roleLabel((int) ($user->role_id ?? 0)),
                    ];
                })
                ->values();

            $distinctActions = ActivityLog::query()
                ->whereNotNull('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action')
                ->filter()
                ->values();

            $actions = collect(self::ACTIONS)
                ->merge($distinctActions)
                ->unique()
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'roles' => [
                        ['id' => '', 'label' => 'All Roles'],
                        ['id' => '1', 'label' => 'Admin'],
                        ['id' => '2', 'label' => 'Guard'],
                        ['id' => '3', 'label' => 'Office Staff'],
                        ['id' => 'system', 'label' => 'System'],
                    ],
                    'modules' => self::MODULES,
                    'actions' => $actions,
                    'statuses' => ['Success', 'Failed', 'Warning'],
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load activity log filters. Please try again.',
            ], 500);
        }
    }

    public function show(int $log): JsonResponse
    {
        try {
            $record = ActivityLog::query()->with('user')->find($log);

            if (! $record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity log not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->serializeDetail($record),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load activity log details. Please try again.',
            ], 500);
        }
    }

    /**
     * @return array{
     *     page: int,
     *     per_page: int,
     *     search: string,
     *     user_id: string,
     *     role_id: string,
     *     module: string,
     *     action: string,
     *     status: string,
     *     date_range: string,
     *     date_from: ?string,
     *     date_to: ?string,
     *     sort_by: string,
     *     sort_direction: string
     * }
     */
    protected function validateFilters(Request $request): array
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:5,10,25,50,75,100'],
            'search' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'string', 'max:50'],
            'role_id' => ['nullable', 'string', 'max:20'],
            'module' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:30'],
            'date_range' => ['nullable', 'string', 'in:today,yesterday,last_7_days,last_30_days,custom,all'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'sort_by' => ['nullable', 'string', 'in:created_at,user,module,action,status'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        return [
            'page' => (int) ($validated['page'] ?? 1),
            'per_page' => (int) ($validated['per_page'] ?? 5),
            'search' => trim((string) ($validated['search'] ?? '')),
            'user_id' => trim((string) ($validated['user_id'] ?? '')),
            'role_id' => trim((string) ($validated['role_id'] ?? '')),
            'module' => trim((string) ($validated['module'] ?? '')),
            'action' => trim((string) ($validated['action'] ?? '')),
            'status' => trim((string) ($validated['status'] ?? '')),
            'date_range' => trim((string) ($validated['date_range'] ?? 'all')),
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'sort_by' => in_array(($validated['sort_by'] ?? 'created_at'), self::SORTABLE, true)
                ? ($validated['sort_by'] ?? 'created_at')
                : 'created_at',
            'sort_direction' => strtolower((string) ($validated['sort_direction'] ?? 'desc')) === 'asc' ? 'asc' : 'desc',
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function filteredQuery(array $filters)
    {
        $query = ActivityLog::query()->select('activity_logs.*');

        [$from, $to] = $this->resolveDateBounds(
            (string) $filters['date_range'],
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null
        );

        if ($from !== null) {
            $query->where('activity_logs.created_at', '>=', $from);
        }
        if ($to !== null) {
            $query->where('activity_logs.created_at', '<=', $to);
        }

        if ($filters['user_id'] !== '') {
            $query->where('activity_logs.user_id', (int) $filters['user_id']);
        }

        if ($filters['role_id'] === 'system') {
            $query->whereNull('activity_logs.user_id');
        } elseif ($filters['role_id'] !== '') {
            $roleId = (int) $filters['role_id'];
            $query->whereHas('user', function ($builder) use ($roleId) {
                if ($roleId === 2) {
                    $builder->whereIn('role_id', [2, 4]);
                } else {
                    $builder->where('role_id', $roleId);
                }
            });
        }

        if ($filters['module'] !== '') {
            $query->where('activity_logs.module', $filters['module']);
        }

        if ($filters['action'] !== '') {
            $query->where('activity_logs.action', $filters['action']);
        }

        if ($filters['status'] !== '') {
            $query->whereRaw("LOWER(TRIM(COALESCE(activity_logs.status, ''))) = ?", [strtolower($filters['status'])]);
        }

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

            $query->where(function ($builder) use ($like, $search) {
                $builder->where('activity_logs.action', 'ilike', $like)
                    ->orWhere('activity_logs.module', 'ilike', $like)
                    ->orWhere('activity_logs.description', 'ilike', $like);

                if (is_numeric($search)) {
                    $builder->orWhere('activity_logs.entity_id', (int) $search)
                        ->orWhere('activity_logs.log_id', (int) $search);
                }

                if (strcasecmp($search, 'system') === 0) {
                    $builder->orWhereNull('activity_logs.user_id');
                }

                $builder->orWhereHas('user', function ($userQuery) use ($like) {
                    $userQuery->where('first_name', 'ilike', $like)
                        ->orWhere('last_name', 'ilike', $like)
                        ->orWhere('email', 'ilike', $like);

                    if (Schema::hasColumn('users', 'name')) {
                        $userQuery->orWhere('name', 'ilike', $like);
                    }
                });
            });
        }

        return $query;
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function resolveDateBounds(string $range, ?string $dateFrom, ?string $dateTo): array
    {
        $now = Carbon::now('Asia/Manila');

        return match ($range) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'last_7_days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            'last_30_days' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'custom' => [
                $dateFrom ? Carbon::createFromFormat('Y-m-d', $dateFrom, 'Asia/Manila')->startOfDay() : null,
                $dateTo ? Carbon::createFromFormat('Y-m-d', $dateTo, 'Asia/Manila')->endOfDay() : null,
            ],
            default => [null, null],
        };
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function todayBounds(): array
    {
        $now = Carbon::now('Asia/Manila');

        return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function hasActiveFilters(array $filters): bool
    {
        return $filters['search'] !== ''
            || $filters['user_id'] !== ''
            || $filters['role_id'] !== ''
            || $filters['module'] !== ''
            || $filters['action'] !== ''
            || $filters['status'] !== ''
            || ($filters['date_range'] !== '' && $filters['date_range'] !== 'all');
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeListRow(ActivityLog $log): array
    {
        $created = $log->created_at?->timezone('Asia/Manila');
        $user = $log->user;
        $isSystem = $user === null;

        return [
            'log_id' => (int) $log->log_id,
            'date_label' => $created ? $created->format('M j, Y') : '—',
            'time_label' => $created ? $created->format('h:i:s A') : '—',
            'user_name' => $isSystem ? 'System' : ActivityLogService::userDisplayName($user),
            'role' => $isSystem ? 'System' : ActivityLogService::roleLabel((int) ($user->role_id ?? 0)),
            'module' => (string) $log->module,
            'action' => (string) $log->action,
            'description' => (string) $log->description,
            'ip_address' => (string) ($log->ip_address ?: '—'),
            'status' => (string) ($log->status ?: ActivityLog::STATUS_SUCCESS),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeDetail(ActivityLog $log): array
    {
        $created = $log->created_at?->timezone('Asia/Manila');
        $user = $log->user;
        $isSystem = $user === null;
        $oldValues = ActivityLogService::sanitize($log->old_values) ?? [];
        $newValues = ActivityLogService::sanitize($log->new_values) ?? [];

        return [
            'log_id' => (int) $log->log_id,
            'date_label' => $created ? $created->format('F j, Y') : '—',
            'time_label' => $created ? $created->format('h:i:s A') : '—',
            'action' => (string) $log->action,
            'module' => (string) $log->module,
            'status' => (string) ($log->status ?: ActivityLog::STATUS_SUCCESS),
            'description' => (string) $log->description,
            'performed_by_system' => $isSystem,
            'user' => [
                'full_name' => $isSystem ? 'System' : ActivityLogService::userDisplayName($user),
                'email' => $isSystem ? '—' : (string) ($user->email ?? '—'),
                'role' => $isSystem ? 'System' : ActivityLogService::roleLabel((int) ($user->role_id ?? 0)),
                'user_id' => $isSystem ? null : (int) ($user->user_id ?? 0),
            ],
            'request' => [
                'ip_address' => (string) ($log->ip_address ?: '—'),
                'user_agent' => (string) ($log->user_agent ?: '—'),
                'method' => (string) ($log->request_method ?: '—'),
                'url' => (string) ($log->request_url ?: '—'),
            ],
            'record' => [
                'entity_type' => (string) ($log->entity_type ?: '—'),
                'entity_id' => $log->entity_id !== null ? (int) $log->entity_id : null,
            ],
            'changes' => $this->buildChangeRows($oldValues, $newValues),
        ];
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     * @return list<array{field: string, previous: string, new: string, changed: bool}>
     */
    protected function buildChangeRows(array $oldValues, array $newValues): array
    {
        $oldFlat = $this->flattenValues($oldValues);
        $newFlat = $this->flattenValues($newValues);
        $keys = array_values(array_unique(array_merge(array_keys($oldFlat), array_keys($newFlat))));
        sort($keys);

        $rows = [];
        foreach ($keys as $key) {
            $previous = $this->stringifyValue($oldFlat[$key] ?? null);
            $next = $this->stringifyValue($newFlat[$key] ?? null);
            $rows[] = [
                'field' => $this->humanizeField($key),
                'previous' => $previous,
                'new' => $next,
                'changed' => $previous !== $next,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function flattenValues(array $values, string $prefix = ''): array
    {
        $flat = [];

        foreach ($values as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value) && $this->isAssoc($value)) {
                $flat = array_merge($flat, $this->flattenValues($value, $path));

                continue;
            }

            $flat[$path] = $value;
        }

        return $flat;
    }

    /**
     * @param  array<mixed>  $value
     */
    protected function isAssoc(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    protected function stringifyValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return is_string($encoded) ? $encoded : '—';
        }

        return (string) $value;
    }

    protected function humanizeField(string $key): string
    {
        $label = str_replace(['_', '.'], ' ', $key);

        return ucwords($label);
    }
}
