<?php

namespace App\Services;

use App\Models\GuardDutyShift;
use App\Models\GuardPersonnel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class GuardPersonnelService
{
    public const DEFAULT_STATION = 'Lobby';

    /**
     * @param  array{
     *     search?: string,
     *     status?: string,
     *     per_page?: int,
     *     page?: int
     * }  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = $this->filteredQuery($filters)->orderBy('last_name')->orderBy('first_name')->orderBy('guard_personnel_id');

        return $query->paginate((int) ($filters['per_page'] ?? 10))->withQueryString();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function availableForDuty(): array
    {
        return GuardPersonnel::query()
            ->active()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('badge_number')
            ->get()
            ->map(fn (GuardPersonnel $guard) => $this->serializeSafe($guard))
            ->values()
            ->all();
    }

    /**
     * @param  array{
     *     first_name: string,
     *     middle_name?: ?string,
     *     last_name: string,
     *     badge_number: string,
     *     duty_pin: string,
     *     status?: string
     * }  $data
     */
    public function create(array $data): GuardPersonnel
    {
        $guard = GuardPersonnel::query()->create([
            'first_name' => trim($data['first_name']),
            'middle_name' => $this->nullableTrim($data['middle_name'] ?? null),
            'last_name' => trim($data['last_name']),
            'badge_number' => trim($data['badge_number']),
            'duty_pin' => (string) $data['duty_pin'],
            'status' => $this->normalizeStatus($data['status'] ?? GuardPersonnel::STATUS_ACTIVE),
        ]);

        ActivityLogService::log(
            action: 'Guard Personnel Created',
            module: 'Guard Personnel',
            description: 'Guard personnel created: '.$guard->displayName().' (Badge '.$guard->badge_number.').',
            entityType: 'GuardPersonnel',
            entityId: (int) $guard->guard_personnel_id,
            newValues: $this->auditPayload($guard)
        );

        return $guard->fresh();
    }

    /**
     * @param  array{
     *     first_name?: string,
     *     middle_name?: ?string,
     *     last_name?: string,
     *     badge_number?: string,
     *     status?: string
     * }  $data
     */
    public function update(GuardPersonnel $guard, array $data): GuardPersonnel
    {
        $old = $this->auditPayload($guard);

        if (array_key_exists('first_name', $data)) {
            $guard->first_name = trim((string) $data['first_name']);
        }

        if (array_key_exists('middle_name', $data)) {
            $guard->middle_name = $this->nullableTrim($data['middle_name']);
        }

        if (array_key_exists('last_name', $data)) {
            $guard->last_name = trim((string) $data['last_name']);
        }

        if (array_key_exists('badge_number', $data)) {
            $guard->badge_number = trim((string) $data['badge_number']);
        }

        if (array_key_exists('status', $data)) {
            $nextStatus = $this->normalizeStatus($data['status']);
            $this->assertCanDeactivate($guard, $nextStatus);
            $guard->status = $nextStatus;
        }

        $guard->save();

        ActivityLogService::log(
            action: 'Guard Personnel Updated',
            module: 'Guard Personnel',
            description: 'Guard personnel updated: '.$guard->displayName().' (Badge '.$guard->badge_number.').',
            entityType: 'GuardPersonnel',
            entityId: (int) $guard->guard_personnel_id,
            oldValues: $old,
            newValues: $this->auditPayload($guard)
        );

        return $guard->fresh();
    }

    public function updateStatus(GuardPersonnel $guard, string $status): GuardPersonnel
    {
        $normalized = $this->normalizeStatus($status);
        $oldStatus = $this->normalizeStatus($guard->status);
        $this->assertCanDeactivate($guard, $normalized);

        if ($oldStatus === $normalized) {
            return $guard;
        }

        $guard->status = $normalized;
        $guard->save();

        $action = $normalized === GuardPersonnel::STATUS_ACTIVE
            ? 'Guard Personnel Activated'
            : 'Guard Personnel Deactivated';

        ActivityLogService::log(
            action: $action,
            module: 'Guard Personnel',
            description: $action.': '.$guard->displayName().' (Badge '.$guard->badge_number.').',
            entityType: 'GuardPersonnel',
            entityId: (int) $guard->guard_personnel_id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $normalized]
        );

        return $guard->fresh();
    }

    public function updatePin(GuardPersonnel $guard, string $pin): GuardPersonnel
    {
        $guard->duty_pin = $pin;
        $guard->save();

        ActivityLogService::log(
            action: 'Guard Duty PIN Changed',
            module: 'Guard Personnel',
            description: 'Duty PIN reset for '.$guard->displayName().' (Badge '.$guard->badge_number.').',
            entityType: 'GuardPersonnel',
            entityId: (int) $guard->guard_personnel_id,
            newValues: [
                'guard_personnel_id' => (int) $guard->guard_personnel_id,
                'badge_number' => $guard->badge_number,
                'pin_changed' => true,
            ]
        );

        return $guard->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(GuardPersonnel $guard): array
    {
        return [
            'id' => (int) $guard->guard_personnel_id,
            'guard_personnel_id' => (int) $guard->guard_personnel_id,
            'first_name' => (string) $guard->first_name,
            'middle_name' => $guard->middle_name,
            'last_name' => (string) $guard->last_name,
            'full_name' => $guard->displayName(),
            'badge_number' => (string) $guard->badge_number,
            'status' => $this->normalizeStatus($guard->status),
            'status_label' => ucfirst($this->normalizeStatus($guard->status)),
            'is_active' => $guard->isActive(),
            'created_at' => optional($guard->created_at)?->toDateTimeString(),
            'updated_at' => optional($guard->updated_at)?->toDateTimeString(),
        ];
    }

    /**
     * @return array{id: int, full_name: string, badge_number: string, status: string}
     */
    public function serializeSafe(GuardPersonnel $guard): array
    {
        return [
            'id' => (int) $guard->guard_personnel_id,
            'full_name' => $guard->displayName(),
            'badge_number' => (string) $guard->badge_number,
            'status' => $this->normalizeStatus($guard->status),
        ];
    }

    protected function assertCanDeactivate(GuardPersonnel $guard, string $nextStatus): void
    {
        if ($nextStatus !== GuardPersonnel::STATUS_INACTIVE) {
            return;
        }

        $onDuty = GuardDutyShift::query()
            ->active()
            ->where('guard_personnel_id', (int) $guard->guard_personnel_id)
            ->exists();

        if ($onDuty) {
            throw ValidationException::withMessages([
                'status' => 'This guard is currently on duty. End their duty before deactivating.',
            ]);
        }
    }

    /**
     * @param  array{search?: string, status?: string}  $filters
     */
    protected function filteredQuery(array $filters): Builder
    {
        $query = GuardPersonnel::query();

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

            $query->where(function (Builder $builder) use ($like) {
                $builder->where('first_name', 'ilike', $like)
                    ->orWhere('middle_name', 'ilike', $like)
                    ->orWhere('last_name', 'ilike', $like)
                    ->orWhere('badge_number', 'ilike', $like)
                    ->orWhereRaw(
                        "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, '')) ilike ?",
                        [$like]
                    )
                    ->orWhereRaw(
                        "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) ilike ?",
                        [$like]
                    );
            });
        }

        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        if (in_array($status, [GuardPersonnel::STATUS_ACTIVE, GuardPersonnel::STATUS_INACTIVE], true)) {
            $query->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", [$status]);
        }

        return $query;
    }

    protected function normalizeStatus(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));

        return $normalized === GuardPersonnel::STATUS_INACTIVE
            ? GuardPersonnel::STATUS_INACTIVE
            : GuardPersonnel::STATUS_ACTIVE;
    }

    protected function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @return array<string, mixed>
     */
    protected function auditPayload(GuardPersonnel $guard): array
    {
        return [
            'guard_personnel_id' => (int) $guard->guard_personnel_id,
            'first_name' => $guard->first_name,
            'middle_name' => $guard->middle_name,
            'last_name' => $guard->last_name,
            'badge_number' => $guard->badge_number,
            'status' => $this->normalizeStatus($guard->status),
        ];
    }
}
