<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class GuardPersonnel extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'guard_personnel';

    protected $primaryKey = 'guard_personnel_id';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'badge_number',
        'duty_pin',
        'status',
    ];

    protected $hidden = [
        'duty_pin',
    ];

    protected function casts(): array
    {
        return [
            'guard_personnel_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function dutyShifts(): HasMany
    {
        return $this->hasMany(GuardDutyShift::class, 'guard_personnel_id', 'guard_personnel_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", [self::STATUS_ACTIVE]);
    }

    public function isActive(): bool
    {
        return strtolower(trim((string) $this->status)) === self::STATUS_ACTIVE;
    }

    public function fullName(): string
    {
        $parts = array_filter([
            trim((string) $this->first_name),
            trim((string) ($this->middle_name ?? '')),
            trim((string) $this->last_name),
        ], static fn (string $part) => $part !== '');

        return trim(implode(' ', $parts));
    }

    public function displayName(): string
    {
        $name = $this->fullName();

        return $name !== '' ? $name : 'Security Guard';
    }

    public function setDutyPinAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['duty_pin'] = $value;

            return;
        }

        if (str_starts_with($value, '$')) {
            $this->attributes['duty_pin'] = $value;

            return;
        }

        $this->attributes['duty_pin'] = Hash::make($value);
    }

    public function verifyDutyPin(string $pin): bool
    {
        $stored = (string) ($this->attributes['duty_pin'] ?? $this->duty_pin ?? '');

        if ($stored === '' || $pin === '') {
            return false;
        }

        if (str_starts_with($stored, '$')) {
            return Hash::check($pin, $stored);
        }

        return hash_equals($stored, $pin);
    }
}
