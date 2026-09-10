<?php

namespace App\Services;

use App\Exceptions\GuardDutyUnavailableException;
use App\Models\Guard;
use App\Models\GuardDutyShift;
use App\Models\GuardPersonnel;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class GuardDutyService
{
    public const GUARD_ROLE_ID = 2;

    public const DEFAULT_STATION = 'Lobby';

    public const INVALID_CREDENTIALS_MESSAGE = 'Incorrect Duty PIN. Please verify your PIN and try again.';

    public const NO_ACTIVE_SHIFT_MESSAGE = 'No active guard duty shift was found.';

    public const RATE_LIMIT_MESSAGE = 'Too many failed attempts. Please wait about 5 minutes and try again.';

    public const NO_ACTIVE_PERSONNEL_MESSAGE = 'No active security guards are available. Please contact the administrator.';

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
            ->with(['guardUser', 'guardProfile', 'guardPersonnel'])
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
     * @return list<array{id: int, full_name: string, badge_number: string, status: string}>
     */
    public function availableGuards(): array
    {
        return app(GuardPersonnelService::class)->availableForDuty();
    }

    /**
     * @return array{has_active_guard: bool, shift: array<string, mixed>|null}
     */
    public function assignGuard(int $guardPersonnelId, string $dutyPin, int $kioskUserId, ?string $ipAddress, ?string $station = null): array
    {
        $station = $this->normalizeStation($station);
        $rateIdentity = 'personnel:'.$guardPersonnelId;
        $this->assertNotRateLimited($rateIdentity, $ipAddress);

        $personnel = $this->findActivePersonnel($guardPersonnelId);

        if ($personnel === null || ! $personnel->verifyDutyPin($dutyPin)) {
            $this->hitRateLimiter($rateIdentity, $ipAddress);
            $this->logFailedPinVerification($personnel, $guardPersonnelId);

            throw ValidationException::withMessages([
                'duty_pin' => self::INVALID_CREDENTIALS_MESSAGE,
            ]);
        }

        $this->clearRateLimiter($rateIdentity, $ipAddress);

        try {
            $shift = DB::transaction(function () use ($personnel, $kioskUserId, $ipAddress, $station) {
                $existing = $this->lockActiveShiftForKiosk($kioskUserId);

                if ($existing) {
                    throw ValidationException::withMessages([
                        'guard_personnel_id' => 'A security guard is already assigned.',
                    ]);
                }

                $fresh = GuardPersonnel::query()
                    ->whereKey($personnel->guard_personnel_id)
                    ->lockForUpdate()
                    ->first();

                if (! $fresh || ! $fresh->isActive()) {
                    throw ValidationException::withMessages([
                        'guard_personnel_id' => 'The selected security guard is no longer active.',
                    ]);
                }

                return $this->createShift(
                    (int) $fresh->guard_personnel_id,
                    $kioskUserId,
                    $ipAddress,
                    $station
                );
            });
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'guard_personnel_id' => 'A security guard is already assigned.',
            ]);
        }

        $shift->load(['guardUser', 'guardProfile', 'guardPersonnel']);
        $payload = [
            'has_active_guard' => true,
            'shift' => $this->serializeShift($shift),
        ];

        $this->logDutyStarted($shift->guardPersonnel ?? $personnel, $shift, $kioskUserId);

        return $payload;
    }

    /**
     * @return array{has_active_guard: bool, shift: array<string, mixed>|null}
     */
    public function changeGuard(int $guardPersonnelId, string $dutyPin, int $kioskUserId, ?string $ipAddress, ?string $station = null): array
    {
        $station = $this->normalizeStation($station);
        $rateIdentity = 'personnel:'.$guardPersonnelId;
        $this->assertNotRateLimited($rateIdentity, $ipAddress);

        $personnel = $this->findActivePersonnel($guardPersonnelId);

        if ($personnel === null || ! $personnel->verifyDutyPin($dutyPin)) {
            $this->hitRateLimiter($rateIdentity, $ipAddress);
            $this->logFailedPinVerification($personnel, $guardPersonnelId);

            throw ValidationException::withMessages([
                'duty_pin' => self::INVALID_CREDENTIALS_MESSAGE,
            ]);
        }

        $this->clearRateLimiter($rateIdentity, $ipAddress);

        $previousName = null;
        $newName = $personnel->displayName();

        try {
            $shift = DB::transaction(function () use ($personnel, $kioskUserId, $ipAddress, $station, &$previousName) {
                $current = $this->lockActiveShiftForKiosk($kioskUserId);

                if (! $current) {
                    throw GuardDutyUnavailableException::missing();
                }

                if ((int) $current->guard_personnel_id === (int) $personnel->guard_personnel_id) {
                    throw ValidationException::withMessages([
                        'guard_personnel_id' => 'This guard is already on duty.',
                    ]);
                }

                $previousName = $this->shiftGuardName($current);
                $now = $this->now();

                $closed = GuardDutyShift::query()
                    ->where('shift_id', $current->shift_id)
                    ->whereNull('clock_out_at')
                    ->update(['clock_out_at' => $now]);

                if ($closed !== 1) {
                    throw new RuntimeException('Unable to close the current guard duty shift.');
                }

                $fresh = GuardPersonnel::query()
                    ->whereKey($personnel->guard_personnel_id)
                    ->lockForUpdate()
                    ->first();

                if (! $fresh || ! $fresh->isActive()) {
                    throw ValidationException::withMessages([
                        'guard_personnel_id' => 'The selected security guard is no longer active.',
                    ]);
                }

                $newShift = $this->createShift(
                    (int) $fresh->guard_personnel_id,
                    $kioskUserId,
                    $ipAddress,
                    $station,
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

        $shift['next']->load(['guardUser', 'guardProfile', 'guardPersonnel']);

        $this->logDutyChanged(
            $previousName ?: 'the previous guard',
            $newName,
            $shift['next'],
            $kioskUserId
        );

        return [
            'has_active_guard' => true,
            'shift' => $this->serializeShift($shift['next']),
        ];
    }

    /**
     * Close the current kiosk shift after the on-duty guard confirms their Duty PIN.
     *
     * @return array{has_active_guard: bool, shift: null}
     */
    public function endDuty(string $dutyPin, int $kioskUserId, ?string $ipAddress): array
    {
        $preview = $this->activeShift($kioskUserId);

        if (! $preview) {
            throw GuardDutyUnavailableException::missingShift();
        }

        $rateLimitIdentity = $preview->guard_personnel_id
            ? 'personnel:'.(int) $preview->guard_personnel_id
            : 'shift:'.(int) $preview->shift_id;

        $this->assertNotRateLimited($rateLimitIdentity, $ipAddress);

        $closed = DB::transaction(function () use ($dutyPin, $kioskUserId) {
            $current = $this->lockActiveShiftForKiosk($kioskUserId);

            if (! $current) {
                throw GuardDutyUnavailableException::missingShift();
            }

            $personnel = $current->guardPersonnel
                ?: ($current->guard_personnel_id
                    ? GuardPersonnel::query()->find((int) $current->guard_personnel_id)
                    : null);

            if (! $personnel || ! $personnel->verifyDutyPin($dutyPin)) {
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
            $current->setRelation('guardPersonnel', $personnel);

            return [
                'shift' => $current,
                'personnel' => $personnel,
            ];
        });

        if ($closed === null) {
            $this->hitRateLimiter($rateLimitIdentity, $ipAddress);
            $this->logFailedPinVerification(
                $preview->guardPersonnel,
                (int) ($preview->guard_personnel_id ?? 0)
            );

            throw ValidationException::withMessages([
                'duty_pin' => self::INVALID_CREDENTIALS_MESSAGE,
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
     * @return array{shift_id: int, clock_in_at: string|null, guard: array{id: int|null, user_id: int|null, name: string, badge_number: mixed, station: mixed}}
     */
    public function serializeShift(GuardDutyShift $shift): array
    {
        $identity = $this->resolveGuardIdentity($shift);
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
                'id' => $identity['personnel_id'],
                'user_id' => $identity['user_id'],
                'name' => $identity['name'],
                'badge_number' => $identity['badge_number'],
                'station' => $identity['station'],
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function currentDutyShifts(): Collection
    {
        return GuardDutyShift::query()
            ->with(['guardUser', 'guardProfile', 'guardPersonnel'])
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
            ->with(['guardUser', 'guardProfile', 'guardPersonnel'])
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
            ->with(['guardUser', 'guardProfile', 'guardPersonnel'])
            ->withCount('visits');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

            $query->where(function (Builder $builder) use ($like) {
                $builder->whereHas('guardPersonnel', function (Builder $personnelQuery) use ($like) {
                    $personnelQuery->where('first_name', 'ilike', $like)
                        ->orWhere('middle_name', 'ilike', $like)
                        ->orWhere('last_name', 'ilike', $like)
                        ->orWhere('badge_number', 'ilike', $like)
                        ->orWhereRaw(
                            "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) ilike ?",
                            [$like]
                        );
                })->orWhereHas('guardUser', function (Builder $userQuery) use ($like) {
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
                })->orWhere('station', 'ilike', $like);
            });
        }

        $station = trim((string) ($filters['station'] ?? ''));
        if ($station !== '') {
            $query->where(function (Builder $builder) use ($station) {
                $builder->where('station', $station)
                    ->orWhereHas('guardProfile', function (Builder $guardQuery) use ($station) {
                        $guardQuery->where('station', $station);
                    });
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
        $fromShifts = GuardDutyShift::query()
            ->whereNotNull('station')
            ->whereRaw("TRIM(COALESCE(station, '')) <> ''")
            ->distinct()
            ->pluck('station');

        $fromProfiles = Guard::query()
            ->whereNotNull('station')
            ->whereRaw("TRIM(COALESCE(station, '')) <> ''")
            ->distinct()
            ->pluck('station');

        return $fromShifts
            ->merge($fromProfiles)
            ->map(fn ($station) => trim((string) $station))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeAdminShift(GuardDutyShift $shift): array
    {
        $identity = $this->resolveGuardIdentity($shift);
        $isActive = $shift->isActive();
        $clockIn = $this->asManila($shift->clock_in_at);
        $clockOut = $this->asManila($shift->clock_out_at);
        $end = $isActive ? $this->now() : $clockOut;
        $visitorsCount = (int) ($shift->visits_count ?? $shift->visits()->count());

        return [
            'shift_id' => (int) $shift->shift_id,
            'guard' => [
                'id' => $identity['personnel_id'],
                'user_id' => $identity['user_id'],
                'name' => $identity['name'],
                'badge_number' => $identity['badge_number'],
                'station' => $identity['station'],
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

    protected function createShift(
        int $guardPersonnelId,
        int $kioskUserId,
        ?string $ipAddress,
        string $station,
        ?Carbon $now = null
    ): GuardDutyShift {
        $now ??= $this->now();

        return GuardDutyShift::query()->create([
            'guard_user_id' => null,
            'guard_personnel_id' => $guardPersonnelId,
            'kiosk_user_id' => $kioskUserId,
            'station' => $station,
            'clock_in_at' => $now,
            'clock_out_at' => null,
            'clock_in_ip' => $ipAddress,
            'created_at' => $now,
        ]);
    }

    protected function findActivePersonnel(int $guardPersonnelId): ?GuardPersonnel
    {
        $personnel = GuardPersonnel::query()->find($guardPersonnelId);

        if (! $personnel || ! $personnel->isActive()) {
            return null;
        }

        return $personnel;
    }

    /**
     * @return array{personnel_id: int|null, user_id: int|null, name: string, badge_number: mixed, station: mixed}
     */
    protected function resolveGuardIdentity(GuardDutyShift $shift): array
    {
        $personnel = $shift->guardPersonnel;
        $user = $shift->guardUser;
        $profile = $shift->guardProfile;
        $station = trim((string) ($shift->station ?? ''));

        if ($personnel) {
            return [
                'personnel_id' => (int) $personnel->guard_personnel_id,
                'user_id' => $shift->guard_user_id ? (int) $shift->guard_user_id : null,
                'name' => $personnel->displayName(),
                'badge_number' => $personnel->badge_number,
                'station' => $station !== '' ? $station : self::DEFAULT_STATION,
            ];
        }

        if ($station === '') {
            $station = trim((string) ($profile->station ?? ''));
        }

        return [
            'personnel_id' => $shift->guard_personnel_id ? (int) $shift->guard_personnel_id : null,
            'user_id' => $shift->guard_user_id ? (int) $shift->guard_user_id : null,
            'name' => $this->displayName($user),
            'badge_number' => $profile->badge_number ?? null,
            'station' => $station !== '' ? $station : self::DEFAULT_STATION,
        ];
    }

    protected function shiftGuardName(GuardDutyShift $shift): string
    {
        return $this->resolveGuardIdentity($shift)['name'];
    }

    protected function displayName(?User $user): string
    {
        return ActivityLogService::userDisplayName($user);
    }

    protected function normalizeStation(?string $station): string
    {
        $trimmed = trim((string) $station);

        return $trimmed !== '' ? $trimmed : self::DEFAULT_STATION;
    }

    protected function logDutyStarted(GuardPersonnel $personnel, GuardDutyShift $shift, int $kioskUserId): void
    {
        $name = $personnel->displayName();
        $station = $this->normalizeStation($shift->station);

        ActivityLogService::log(
            action: 'Guard Duty Started',
            module: 'Guard Duty',
            description: 'Guard duty started: '.$name.' (Badge '.$personnel->badge_number.') at '.$station.'.',
            entityType: 'GuardDutyShift',
            entityId: (int) $shift->shift_id,
            newValues: [
                'shift_id' => (int) $shift->shift_id,
                'guard_personnel_id' => (int) $personnel->guard_personnel_id,
                'guard_name' => $name,
                'badge_number' => $personnel->badge_number,
                'station' => $station,
                'kiosk_user_id' => $kioskUserId,
                'clock_in_at' => optional($shift->clock_in_at)?->toDateTimeString(),
            ]
        );
    }

    protected function logDutyEnded(GuardDutyShift $shift, int $kioskUserId, mixed $clockOutAt = null): void
    {
        $identity = $this->resolveGuardIdentity($shift);
        $name = $identity['name'];
        $station = $identity['station'] ?: self::DEFAULT_STATION;
        $clockOut = $clockOutAt instanceof Carbon
            ? $clockOutAt
            : ($clockOutAt ? Carbon::parse($clockOutAt, 'Asia/Manila') : $this->now());

        ActivityLogService::log(
            action: 'Guard Duty Ended',
            module: 'Guard Duty',
            description: 'Guard duty ended: '.$name
                .($identity['badge_number'] ? ' (Badge '.$identity['badge_number'].')' : '')
                .' at '.$station.'.',
            entityType: 'GuardDutyShift',
            entityId: (int) $shift->shift_id,
            oldValues: [
                'shift_id' => (int) $shift->shift_id,
                'guard_personnel_id' => $identity['personnel_id'],
                'guard_user_id' => $identity['user_id'],
                'guard_name' => $name,
                'badge_number' => $identity['badge_number'],
                'station' => $station,
                'kiosk_user_id' => $kioskUserId,
                'clock_in_at' => optional($shift->clock_in_at)?->toDateTimeString(),
            ],
            newValues: [
                'clock_out_at' => $clockOut->toDateTimeString(),
            ]
        );
    }

    protected function logDutyChanged(
        string $previousName,
        string $newName,
        GuardDutyShift $shift,
        int $kioskUserId
    ): void {
        $identity = $this->resolveGuardIdentity($shift);
        $station = $identity['station'] ?: self::DEFAULT_STATION;

        ActivityLogService::log(
            action: 'Guard Changed',
            module: 'Guard Duty',
            description: 'Guard duty changed from '.$previousName.' to '.$newName.' at '.$station.'.',
            entityType: 'GuardDutyShift',
            entityId: (int) $shift->shift_id,
            newValues: [
                'shift_id' => (int) $shift->shift_id,
                'previous_guard_name' => $previousName,
                'new_guard_name' => $newName,
                'guard_personnel_id' => $identity['personnel_id'],
                'station' => $station,
                'kiosk_user_id' => $kioskUserId,
                'clock_in_at' => optional($shift->clock_in_at)?->toDateTimeString(),
            ]
        );
    }

    protected function logFailedPinVerification(?GuardPersonnel $personnel, int $guardPersonnelId): void
    {
        $label = $personnel
            ? $personnel->displayName()
            : ($guardPersonnelId > 0 ? 'Guard #'.$guardPersonnelId : 'an unknown guard');

        ActivityLogService::log(
            action: 'Failed Duty PIN Verification',
            module: 'Guard Duty',
            description: 'Failed duty PIN verification for '.$label.'.',
            entityType: 'GuardPersonnel',
            entityId: $personnel?->guard_personnel_id ?: ($guardPersonnelId > 0 ? $guardPersonnelId : null),
            status: ActivityLogService::STATUS_FAILED,
            userId: null
        );
    }

    protected function rateLimitKey(string $identity, ?string $ipAddress): string
    {
        return 'guard-duty-pin:'.strtolower(trim($identity)).'|'.($ipAddress ?: 'unknown');
    }

    protected function assertNotRateLimited(string $identity, ?string $ipAddress): void
    {
        $key = $this->rateLimitKey($identity, $ipAddress);
        $ipKey = 'guard-duty-pin-ip:'.($ipAddress ?: 'unknown');

        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($ipKey, 10)) {
            throw ValidationException::withMessages([
                'duty_pin' => self::RATE_LIMIT_MESSAGE,
            ]);
        }
    }

    protected function hitRateLimiter(string $identity, ?string $ipAddress): void
    {
        RateLimiter::hit($this->rateLimitKey($identity, $ipAddress), 5 * 60);
        RateLimiter::hit('guard-duty-pin-ip:'.($ipAddress ?: 'unknown'), 5 * 60);
    }

    protected function clearRateLimiter(string $identity, ?string $ipAddress): void
    {
        RateLimiter::clear($this->rateLimitKey($identity, $ipAddress));
    }

    protected function now(): Carbon
    {
        return Carbon::now('Asia/Manila');
    }
}
