<?php

namespace App\Services;

use App\Exceptions\GuardDutyUnavailableException;
use App\Models\Guard;
use App\Models\GuardDutyShift;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class GuardDutyService
{
    public const GUARD_ROLE_ID = 2;

    public const INVALID_CREDENTIALS_MESSAGE = 'Invalid guard credentials.';

    public const NO_ACTIVE_SHIFT_MESSAGE = 'No active guard duty shift was found.';

    public const RATE_LIMIT_MESSAGE = 'Too many attempts. Please try again later.';

    public function payloadForKiosk(?int $kioskUserId): array
    {
        $shift = $this->activeShift($kioskUserId);

        if (! $shift) {
            return [
                'has_active_guard' => false,
                'shift' => null,
            ];
        }

        return [
            'has_active_guard' => true,
            'shift' => $this->serializeShift($shift),
        ];
    }

    public function hasActiveGuardForKiosk(?int $kioskUserId): bool
    {
        return $this->activeShift($kioskUserId) !== null;
    }

    public function activeShift(?int $kioskUserId, bool $lock = false): ?GuardDutyShift
    {
        $query = GuardDutyShift::query()
            ->with(['guardUser', 'guardProfile'])
            ->active()
            ->orderByDesc('clock_in_at')
            ->orderByDesc('shift_id');

        if ($kioskUserId !== null) {
            $query->where('kiosk_user_id', $kioskUserId);
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function lockActiveShiftForKiosk(int $kioskUserId): ?GuardDutyShift
    {
        return $this->activeShift($kioskUserId, true);
    }

    public function requireActiveShiftForKiosk(int $kioskUserId, bool $lock = true): GuardDutyShift
    {
        $shift = $this->activeShift($kioskUserId, $lock);

        if (! $shift) {
            throw GuardDutyUnavailableException::missing();
        }

        return $shift;
    }

    /**
     * @return array{has_active_guard: bool, shift: array<string, mixed>|null}
     */
    public function assignGuard(string $email, string $password, int $kioskUserId, ?string $ipAddress): array
    {
        $this->assertNotRateLimited($email, $ipAddress);

        $authenticated = $this->authenticateGuard($email, $password);

        if ($authenticated === null) {
            $this->hitRateLimiter($email, $ipAddress);
            $this->logFailedAuthentication($email);

            throw ValidationException::withMessages([
                'email' => self::INVALID_CREDENTIALS_MESSAGE,
            ]);
        }

        $this->clearRateLimiter($email, $ipAddress);

        try {
            $shift = DB::transaction(function () use ($authenticated, $kioskUserId, $ipAddress) {
                $existing = $this->lockActiveShiftForKiosk($kioskUserId);

                if ($existing) {
                    throw ValidationException::withMessages([
                        'email' => 'A security guard is already assigned.',
                    ]);
                }

                return $this->createShift(
                    (int) $authenticated['user']->user_id,
                    $kioskUserId,
                    $ipAddress
                );
            });
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'email' => 'A security guard is already assigned.',
            ]);
        }

        $shift->load(['guardUser', 'guardProfile']);
        $payload = [
            'has_active_guard' => true,
            'shift' => $this->serializeShift($shift),
        ];

        $this->logDutyStarted($authenticated['user'], $authenticated['guard'], $shift, $kioskUserId);

        return $payload;
    }

    /**
     * @return array{has_active_guard: bool, shift: array<string, mixed>|null}
     */
    public function changeGuard(string $email, string $password, int $kioskUserId, ?string $ipAddress): array
    {
        $this->assertNotRateLimited($email, $ipAddress);

        $authenticated = $this->authenticateGuard($email, $password);

        if ($authenticated === null) {
            $this->hitRateLimiter($email, $ipAddress);
            $this->logFailedAuthentication($email);

            throw ValidationException::withMessages([
                'email' => self::INVALID_CREDENTIALS_MESSAGE,
            ]);
        }

        $this->clearRateLimiter($email, $ipAddress);

        $previousName = null;
        $newName = $this->displayName($authenticated['user']);

        try {
            $shift = DB::transaction(function () use ($authenticated, $kioskUserId, $ipAddress, &$previousName) {
                $current = $this->lockActiveShiftForKiosk($kioskUserId);

                if (! $current) {
                    throw GuardDutyUnavailableException::missing();
                }

                if ((int) $current->guard_user_id === (int) $authenticated['user']->user_id) {
                    throw ValidationException::withMessages([
                        'email' => 'This guard is already on duty.',
                    ]);
                }

                $previousName = $this->displayName($current->guardUser);
                $now = $this->now();

                $closed = GuardDutyShift::query()
                    ->where('shift_id', $current->shift_id)
                    ->whereNull('clock_out_at')
                    ->update(['clock_out_at' => $now]);

                if ($closed !== 1) {
                    throw new RuntimeException('Unable to close the current guard duty shift.');
                }

                $newShift = $this->createShift(
                    (int) $authenticated['user']->user_id,
                    $kioskUserId,
                    $ipAddress,
                    $now
                );

                return [
                    'previous' => $current,
                    'next' => $newShift,
                ];
            });
        } catch (UniqueConstraintViolationException $e) {
            throw new RuntimeException('Unable to assign the new guard on duty. Please try again.');
        }

        $shift['next']->load(['guardUser', 'guardProfile']);

        $this->logDutyChanged(
            $previousName ?: 'the previous guard',
            $newName,
            $shift['next'],
            $kioskUserId,
            $authenticated['guard']
        );

        return [
            'has_active_guard' => true,
            'shift' => $this->serializeShift($shift['next']),
        ];
    }

    /**
     * Close the current kiosk shift after the on-duty guard confirms their password.
     *
     * @return array{has_active_guard: bool, shift: null}
     */
    public function endDuty(string $password, int $kioskUserId, ?string $ipAddress): array
    {
        $preview = $this->activeShift($kioskUserId);

        if (! $preview) {
            throw GuardDutyUnavailableException::missingShift();
        }

        $rateLimitIdentity = $this->rateLimitIdentity($preview->guardUser);
        $this->assertNotRateLimited($rateLimitIdentity, $ipAddress);

        $closed = DB::transaction(function () use ($password, $kioskUserId) {
            $current = $this->lockActiveShiftForKiosk($kioskUserId);

            if (! $current) {
                throw GuardDutyUnavailableException::missingShift();
            }

            $authenticated = $this->authenticateAssignedGuard($current->guardUser, $password);

            if ($authenticated === null) {
                return null;
            }

            $now = $this->now();

            $updated = GuardDutyShift::query()
                ->where('shift_id', $current->shift_id)
                ->whereNull('clock_out_at')
                ->update(['clock_out_at' => $now]);

            if ($updated !== 1) {
                throw GuardDutyUnavailableException::missingShift();
            }

            $current->clock_out_at = $now;

            return [
                'shift' => $current,
                'user' => $authenticated['user'],
                'guard' => $authenticated['guard'],
            ];
        });

        if ($closed === null) {
            $this->hitRateLimiter($rateLimitIdentity, $ipAddress);
            $this->logFailedAuthentication($rateLimitIdentity);

            throw ValidationException::withMessages([
                'password' => self::INVALID_CREDENTIALS_MESSAGE,
            ]);
        }

        $this->clearRateLimiter($rateLimitIdentity, $ipAddress);
        $this->logDutyEnded($closed['shift'], $kioskUserId, $closed['shift']->clock_out_at);

        return [
            'has_active_guard' => false,
            'shift' => null,
        ];
    }

    /**
     * @return array{user: User, guard: Guard}|null
     */
    public function authenticateGuard(string $email, string $password): ?array
    {
        return $this->authenticateAssignedGuard(User::findByEmail($email), $password);
    }

    /**
     * Verify that the given account is the current on-duty guard.
     *
     * @return array{user: User, guard: Guard}|null
     */
    public function authenticateAssignedGuard(?User $user, string $password): ?array
    {
        if (! $user || ! $this->passwordMatches($user, $password)) {
            return null;
        }

        if (! $this->isAccountActive($user)) {
            return null;
        }

        if ((int) $user->role_id !== self::GUARD_ROLE_ID) {
            return null;
        }

        $guard = Guard::query()->where('user_id', (int) $user->user_id)->first();

        if (! $guard) {
            return null;
        }

        return [
            'user' => $user,
            'guard' => $guard,
        ];
    }

    /**
     * @return array{shift_id: int, clock_in_at: string|null, guard: array{user_id: int, name: string, badge_number: mixed, station: mixed}}
     */
    public function serializeShift(GuardDutyShift $shift): array
    {
        $user = $shift->guardUser;
        $profile = $shift->guardProfile;
        $clockIn = $shift->clock_in_at;

        if ($clockIn instanceof Carbon) {
            $clockIn = $clockIn->copy()->timezone('Asia/Manila')->format('Y-m-d\TH:i:s');
        } elseif ($clockIn) {
            $clockIn = Carbon::parse($clockIn, 'Asia/Manila')->format('Y-m-d\TH:i:s');
        } else {
            $clockIn = null;
        }

        return [
            'shift_id' => (int) $shift->shift_id,
            'clock_in_at' => $clockIn,
            'guard' => [
                'user_id' => (int) $shift->guard_user_id,
                'name' => $this->displayName($user),
                'badge_number' => $profile->badge_number ?? null,
                'station' => $profile->station ?? null,
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function currentDutyShifts(): Collection
    {
        return GuardDutyShift::query()
            ->with(['guardUser', 'guardProfile'])
            ->withCount('visits')
            ->active()
            ->orderBy('clock_in_at')
            ->orderBy('shift_id')
            ->get()
            ->map(fn (GuardDutyShift $shift) => $this->serializeAdminShift($shift))
            ->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function lastCompletedShift(): ?array
    {
        $shift = GuardDutyShift::query()
            ->with(['guardUser', 'guardProfile'])
            ->withCount('visits')
            ->completed()
            ->orderByDesc('clock_out_at')
            ->orderByDesc('shift_id')
            ->first();

        return $shift ? $this->serializeAdminShift($shift) : null;
    }

    public function activeGuardCount(): int
    {
        return GuardDutyShift::query()->active()->count();
    }

    /**
     * @param  array{
     *     search?: string,
     *     station?: string,
     *     status?: string,
     *     date_range?: string,
     *     date_from?: ?string,
     *     date_to?: ?string
     * }  $filters
     */
    public function historyQuery(array $filters): Builder
    {
        $query = GuardDutyShift::query()
            ->with(['guardUser', 'guardProfile'])
            ->withCount('visits');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

            $query->where(function (Builder $builder) use ($like) {
                $builder->whereHas('guardUser', function (Builder $userQuery) use ($like) {
                    $userQuery->where('first_name', 'ilike', $like)
                        ->orWhere('last_name', 'ilike', $like)
                        ->orWhere('email', 'ilike', $like)
                        ->orWhereRaw(
                            "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) ilike ?",
                            [$like]
                        );
                })->orWhereHas('guardProfile', function (Builder $guardQuery) use ($like) {
                    $guardQuery->where('badge_number', 'ilike', $like)
                        ->orWhere('station', 'ilike', $like);
                });
            });
        }

        $station = trim((string) ($filters['station'] ?? ''));
        if ($station !== '') {
            $query->whereHas('guardProfile', function (Builder $guardQuery) use ($station) {
                $guardQuery->where('station', $station);
            });
        }

        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        if (in_array($status, ['on_duty', 'on duty'], true)) {
            $query->active();
        } elseif ($status === 'completed') {
            $query->completed();
        }

        [$from, $to] = $this->dateRangeBounds(
            (string) ($filters['date_range'] ?? 'all'),
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null
        );

        if ($from) {
            $query->where('clock_in_at', '>=', $from);
        }

        if ($to) {
            $query->where('clock_in_at', '<=', $to);
        }

        return $query->orderByRaw('CASE WHEN clock_out_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('clock_in_at')
            ->orderByDesc('shift_id');
    }

    /**
     * @return list<string>
     */
    public function stationOptions(): array
    {
        return Guard::query()
            ->whereNotNull('station')
            ->whereRaw("TRIM(COALESCE(station, '')) <> ''")
            ->orderBy('station')
            ->distinct()
            ->pluck('station')
            ->map(fn ($station) => trim((string) $station))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeAdminShift(GuardDutyShift $shift): array
    {
        $user = $shift->guardUser;
        $profile = $shift->guardProfile;
        $isActive = $shift->isActive();
        $clockIn = $this->asManila($shift->clock_in_at);
        $clockOut = $this->asManila($shift->clock_out_at);
        $end = $isActive ? $this->now() : $clockOut;
        $visitorsCount = (int) ($shift->visits_count ?? $shift->visits()->count());

        return [
            'shift_id' => (int) $shift->shift_id,
            'guard' => [
                'user_id' => (int) $shift->guard_user_id,
                'name' => $this->displayName($user),
                'badge_number' => $profile->badge_number ?? null,
                'station' => $profile->station ?? null,
            ],
            'clock_in_at' => $clockIn?->format('Y-m-d\TH:i:s'),
            'clock_out_at' => $isActive ? null : $clockOut?->format('Y-m-d\TH:i:s'),
            'clock_in_label' => $clockIn?->format('M j, Y g:i A') ?: '—',
            'clock_in_time_label' => $clockIn?->format('g:i A') ?: '—',
            'clock_in_detail_label' => $clockIn?->format('F j, Y • g:i A') ?: '—',
            'clock_out_label' => $isActive ? '—' : ($clockOut?->format('M j, Y g:i A') ?: '—'),
            'clock_out_time_label' => $isActive ? '—' : ($clockOut?->format('g:i A') ?: '—'),
            'clock_out_detail_label' => $isActive ? 'Currently On Duty' : ($clockOut?->format('F j, Y • g:i A') ?: '—'),
            'visitors_range_label' => $this->visitorsRangeLabel($clockIn, $clockOut, $isActive),
            'duration_label' => self::formatDurationMinutes($this->durationMinutes($clockIn, $end)),
            'visitors_count' => $visitorsCount,
            'is_active' => $isActive,
            'status' => $isActive ? 'On Duty' : 'Completed',
            'status_key' => $isActive ? 'on_duty' : 'completed',
        ];
    }

    public function paginateShiftVisits(GuardDutyShift $shift, int $perPage = 5): LengthAwarePaginator
    {
        $paginator = Visit::query()
            ->from('visit as v')
            ->leftJoin('visitor as vis', 'vis.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'v.primary_office_id')
            ->where('v.duty_shift_id', (int) $shift->shift_id)
            ->select([
                'v.visit_id',
                'v.control_number',
                'v.entry_time',
                'v.exit_time',
                'v.destination_text',
                'vis.first_name',
                'vis.last_name',
                'vt.visit_type_name',
                'o.office_name',
            ])
            ->orderByDesc('v.entry_time')
            ->orderByDesc('v.visit_id')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($row) => $this->serializeShiftVisit($row))
        );

        return $paginator;
    }

    public static function formatDurationMinutes(int $minutes): string
    {
        $minutes = max(0, $minutes);
        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $mins = $minutes % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = $days.'d';
        }
        if ($hours > 0) {
            $parts[] = $hours.'h';
        }
        if ($mins > 0 || $parts === []) {
            $parts[] = $mins.'m';
        }

        return implode(' ', $parts);
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    public function dateRangeBounds(string $range, ?string $dateFrom, ?string $dateTo): array
    {
        $now = $this->now();

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
     * @param  object{
     *     visit_id?: mixed,
     *     control_number?: mixed,
     *     entry_time?: mixed,
     *     exit_time?: mixed,
     *     destination_text?: mixed,
     *     first_name?: mixed,
     *     last_name?: mixed,
     *     visit_type_name?: mixed,
     *     office_name?: mixed
     * }  $row
     * @return array<string, mixed>
     */
    protected function serializeShiftVisit(object $row): array
    {
        $name = trim(((string) ($row->first_name ?? '')).' '.((string) ($row->last_name ?? '')));
        $office = trim((string) ($row->office_name ?? ''));
        $destinationText = trim((string) ($row->destination_text ?? ''));
        $destination = $office !== '' ? $office : ($destinationText !== '' ? $destinationText : '—');
        $entry = $this->asManila($row->entry_time ?? null);
        $exit = $this->asManila($row->exit_time ?? null);
        $inside = $exit === null;

        return [
            'visit_id' => (int) ($row->visit_id ?? 0),
            'control_number' => trim((string) ($row->control_number ?? '')) ?: '—',
            'visitor_name' => $name !== '' ? $name : 'Unknown Visitor',
            'visit_type' => trim((string) ($row->visit_type_name ?? '')) ?: '—',
            'destination' => $destination,
            'entry_time_label' => $entry?->format('M j, Y g:i A') ?: '—',
            'exit_time_label' => $inside ? '—' : ($exit?->format('M j, Y g:i A') ?: '—'),
            'status' => $inside ? 'Inside' : 'Exited',
            'status_key' => $inside ? 'inside' : 'exited',
        ];
    }

    protected function visitorsRangeLabel(?Carbon $clockIn, ?Carbon $clockOut, bool $isActive): string
    {
        $start = $clockIn?->format('F j, Y • g:i A') ?: '—';

        if ($isActive) {
            return $start.' – Current';
        }

        return $start.' – '.($clockOut?->format('g:i A') ?: '—');
    }

    protected function durationMinutes(?Carbon $start, ?Carbon $end): int
    {
        if (! $start || ! $end) {
            return 0;
        }

        return max(0, (int) floor(($end->getTimestamp() - $start->getTimestamp()) / 60));
    }

    protected function asManila(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy()->timezone('Asia/Manila');
        }

        if ($value) {
            return Carbon::parse($value)->timezone('Asia/Manila');
        }

        return null;
    }

    protected function createShift(int $guardUserId, int $kioskUserId, ?string $ipAddress, ?Carbon $now = null): GuardDutyShift
    {
        $now ??= $this->now();

        return GuardDutyShift::query()->create([
            'guard_user_id' => $guardUserId,
            'kiosk_user_id' => $kioskUserId,
            'clock_in_at' => $now,
            'clock_out_at' => null,
            'clock_in_ip' => $ipAddress,
            'created_at' => $now,
        ]);
    }

    protected function displayName(?User $user): string
    {
        return ActivityLogService::userDisplayName($user);
    }

    protected function kioskLabel(?Guard $guard): string
    {
        $station = $this->stationLabel($guard);

        if ($station === 'Self-Registration kiosk') {
            return $station;
        }

        return $station.' Self-Registration kiosk';
    }

    protected function stationLabel(?Guard $guard): string
    {
        $station = trim((string) ($guard->station ?? ''));

        return $station !== '' ? $station : 'Self-Registration kiosk';
    }

    protected function rateLimitIdentity(?User $user): string
    {
        $email = strtolower(trim((string) ($user->email ?? '')));

        if ($email !== '') {
            return $email;
        }

        return 'user:'.(int) ($user->user_id ?? 0);
    }

    protected function logDutyStarted(User $user, Guard $guard, GuardDutyShift $shift, int $kioskUserId): void
    {
        $name = $this->displayName($user);

        ActivityLogService::log(
            action: 'Guard Duty Started',
            module: 'Guard Duty',
            description: $name.' was assigned as the guard on duty for the '.$this->kioskLabel($guard).'.',
            entityType: 'GuardDutyShift',
            entityId: (int) $shift->shift_id,
            newValues: [
                'shift_id' => (int) $shift->shift_id,
                'guard_user_id' => (int) $user->user_id,
                'guard_name' => $name,
                'badge_number' => $guard->badge_number,
                'station' => $guard->station,
                'kiosk_user_id' => $kioskUserId,
                'clock_in_at' => optional($shift->clock_in_at)?->toDateTimeString(),
            ],
            userId: (int) $user->user_id
        );
    }

    protected function logDutyEnded(GuardDutyShift $shift, int $kioskUserId, mixed $clockOutAt = null): void
    {
        $name = $this->displayName($shift->guardUser);
        $station = $this->stationLabel($shift->guardProfile);
        $clockOut = $clockOutAt instanceof Carbon
            ? $clockOutAt
            : ($clockOutAt ? Carbon::parse($clockOutAt, 'Asia/Manila') : $this->now());

        ActivityLogService::log(
            action: 'Guard Duty Ended',
            module: 'Guard Duty',
            description: $name.' ended duty at the '.$station.'.',
            entityType: 'GuardDutyShift',
            entityId: (int) $shift->shift_id,
            oldValues: [
                'shift_id' => (int) $shift->shift_id,
                'guard_user_id' => (int) $shift->guard_user_id,
                'guard_name' => $name,
                'station' => $shift->guardProfile?->station,
                'kiosk_user_id' => $kioskUserId,
                'clock_in_at' => optional($shift->clock_in_at)?->toDateTimeString(),
            ],
            newValues: [
                'clock_out_at' => $clockOut->toDateTimeString(),
            ],
            userId: (int) $shift->guard_user_id
        );
    }

    protected function logDutyChanged(
        string $previousName,
        string $newName,
        GuardDutyShift $shift,
        int $kioskUserId,
        ?Guard $newGuard = null
    ): void {
        $station = $this->stationLabel($newGuard ?: $shift->guardProfile);

        ActivityLogService::log(
            action: 'Guard Changed',
            module: 'Guard Duty',
            description: 'Guard duty changed from '.$previousName.' to '.$newName.' at the '.$station.'.',
            entityType: 'GuardDutyShift',
            entityId: (int) $shift->shift_id,
            newValues: [
                'shift_id' => (int) $shift->shift_id,
                'previous_guard_name' => $previousName,
                'new_guard_name' => $newName,
                'station' => $newGuard?->station ?? $shift->guardProfile?->station,
                'kiosk_user_id' => $kioskUserId,
                'clock_in_at' => optional($shift->clock_in_at)?->toDateTimeString(),
            ],
            userId: (int) $shift->guard_user_id
        );
    }

    protected function logFailedAuthentication(string $email): void
    {
        ActivityLogService::log(
            action: 'Failed Login',
            module: 'Guard Duty',
            description: 'Failed guard on-duty authentication attempt for '.$email.'.',
            entityType: 'User',
            status: ActivityLogService::STATUS_FAILED,
            userId: null
        );
    }

    protected function passwordMatches(User $user, string $inputPassword): bool
    {
        $stored = (string) ($user->getAttributes()['password_hash'] ?? '');

        if ($stored === '') {
            return false;
        }

        if (str_starts_with($stored, '$')) {
            return Hash::check($inputPassword, $stored);
        }

        return hash_equals($stored, $inputPassword);
    }

    protected function isAccountActive(User $user): bool
    {
        $status = strtolower(trim((string) ($user->status ?? 'active')));

        return ! in_array($status, ['inactive', 'disabled', 'suspended', 'recycle_bin', 'deleted'], true);
    }

    protected function rateLimitKey(string $email, ?string $ipAddress): string
    {
        return 'guard-duty-auth:'.strtolower(trim($email)).'|'.($ipAddress ?: 'unknown');
    }

    protected function assertNotRateLimited(string $email, ?string $ipAddress): void
    {
        $key = $this->rateLimitKey($email, $ipAddress);
        $ipKey = 'guard-duty-auth-ip:'.($ipAddress ?: 'unknown');

        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($ipKey, 10)) {
            throw ValidationException::withMessages([
                'email' => self::RATE_LIMIT_MESSAGE,
            ]);
        }
    }

    protected function hitRateLimiter(string $email, ?string $ipAddress): void
    {
        RateLimiter::hit($this->rateLimitKey($email, $ipAddress), 15 * 60);
        RateLimiter::hit('guard-duty-auth-ip:'.($ipAddress ?: 'unknown'), 15 * 60);
    }

    protected function clearRateLimiter(string $email, ?string $ipAddress): void
    {
        RateLimiter::clear($this->rateLimitKey($email, $ipAddress));
    }

    protected function now(): Carbon
    {
        return Carbon::now('Asia/Manila');
    }
}
