<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\LoginAttemptService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class LoginAttemptController extends Controller
{
    /**
     * @var list<string>
     */
    protected const SORTABLE = [
        'attempted_at',
        'email',
        'role',
        'status',
        'ip_address',
        'login_source',
    ];

    public function index(): View
    {
        return view('admin.login-attempts');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateFilters($request);
            $query = $this->filteredQuery($validated);

            $query->orderBy('login_attempts.'.$validated['sort_by'], $validated['sort_direction'])
                ->orderBy('login_attempts.id', 'desc');

            $paginator = $query
                ->with('user')
                ->paginate($validated['per_page'])
                ->withQueryString();

            $rows = $paginator->getCollection()->map(fn (LoginAttempt $attempt) => $this->serializeRow($attempt));

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
                'message' => 'Unable to load login attempts. Please try again.',
            ], 500);
        }
    }

    public function summary(): JsonResponse
    {
        try {
            [$todayStart, $todayEnd] = $this->todayBounds();

            $todayQuery = LoginAttempt::query()
                ->whereBetween('attempted_at', [$todayStart, $todayEnd]);

            return response()->json([
                'success' => true,
                'data' => [
                    'successful_today' => (clone $todayQuery)
                        ->where('status', LoginAttempt::STATUS_SUCCESS)
                        ->count(),
                    'failed_today' => (clone $todayQuery)
                        ->where('status', LoginAttempt::STATUS_FAILED)
                        ->count(),
                    'blocked_today' => (clone $todayQuery)
                        ->where('status', LoginAttempt::STATUS_BLOCKED)
                        ->count(),
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load login attempt summary. Please try again.',
            ], 500);
        }
    }

    /**
     * @return array{
     *     page: int,
     *     per_page: int,
     *     search: string,
     *     role: string,
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
            'per_page' => ['nullable', 'integer', 'in:10,15,25,50'],
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'in:admin,guard,office'],
            'status' => ['nullable', 'string', 'in:success,failed,blocked'],
            'date_range' => ['nullable', 'string', 'in:today,yesterday,last_7_days,last_30_days,custom,all'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'sort_by' => ['nullable', 'string', 'in:attempted_at,email,role,status,ip_address,login_source'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $sortBy = (string) ($validated['sort_by'] ?? 'attempted_at');

        return [
            'page' => (int) ($validated['page'] ?? 1),
            'per_page' => (int) ($validated['per_page'] ?? 15),
            'search' => trim((string) ($validated['search'] ?? '')),
            'role' => trim((string) ($validated['role'] ?? '')),
            'status' => trim((string) ($validated['status'] ?? '')),
            'date_range' => trim((string) ($validated['date_range'] ?? 'all')),
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'sort_by' => in_array($sortBy, self::SORTABLE, true) ? $sortBy : 'attempted_at',
            'sort_direction' => strtolower((string) ($validated['sort_direction'] ?? 'desc')) === 'asc' ? 'asc' : 'desc',
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function filteredQuery(array $filters)
    {
        $query = LoginAttempt::query()->select('login_attempts.*');

        [$from, $to] = $this->resolveDateBounds(
            (string) $filters['date_range'],
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null
        );

        if ($from !== null) {
            $query->where('login_attempts.attempted_at', '>=', $from);
        }
        if ($to !== null) {
            $query->where('login_attempts.attempted_at', '<=', $to);
        }

        if ($filters['role'] !== '') {
            $query->where('login_attempts.role', $filters['role']);
        }

        if ($filters['status'] !== '') {
            $query->where('login_attempts.status', $filters['status']);
        }

        if ($filters['search'] !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $filters['search']).'%';

            $query->where(function ($builder) use ($like) {
                $builder->where('login_attempts.email', 'ilike', $like)
                    ->orWhere('login_attempts.ip_address', 'ilike', $like)
                    ->orWhereHas('user', function ($userQuery) use ($like) {
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
            || $filters['role'] !== ''
            || $filters['status'] !== ''
            || ($filters['date_range'] !== '' && $filters['date_range'] !== 'all');
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeRow(LoginAttempt $attempt): array
    {
        $attempted = $attempt->attempted_at?->timezone('Asia/Manila');

        return [
            'id' => (int) $attempt->id,
            'date_label' => $attempted ? $attempted->format('M j, Y') : '—',
            'time_label' => $attempted ? $attempted->format('h:i:s A') : '—',
            'account_name' => $this->accountName($attempt),
            'email' => (string) ($attempt->email ?: ($attempt->user?->email ?? '—')),
            'role' => LoginAttemptService::roleLabel($attempt->role),
            'role_slug' => (string) ($attempt->role ?: ''),
            'status' => LoginAttemptService::statusLabel((string) $attempt->status),
            'status_slug' => (string) $attempt->status,
            'failure_reason' => (string) ($attempt->status === LoginAttempt::STATUS_SUCCESS
                ? '—'
                : ($attempt->failure_reason ?: '—')),
            'ip_address' => (string) ($attempt->ip_address ?: '—'),
            'device_type' => (string) ($attempt->device_type ?: 'Unknown'),
            'login_source' => (string) ($attempt->login_source ?: '—'),
        ];
    }

    protected function accountName(LoginAttempt $attempt): string
    {
        $user = $attempt->user;

        if (! $user instanceof User) {
            return 'Unknown account';
        }

        $first = trim((string) ($user->first_name ?? ''));
        $last = trim((string) ($user->last_name ?? ''));
        $full = trim($first.' '.$last);

        if ($full !== '') {
            return $full;
        }

        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $email = trim((string) ($attempt->email ?: $user->email ?: ''));

        return $email !== '' ? $email : 'Unknown account';
    }
}
