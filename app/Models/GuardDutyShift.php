<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GuardDutyShift extends Model
{
    protected $table = 'guard_duty_shifts';

    protected $primaryKey = 'shift_id';

    public const UPDATED_AT = null;

    protected $fillable = [
        'guard_user_id',
        'kiosk_user_id',
        'clock_in_at',
        'clock_out_at',
        'clock_in_ip',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'guard_user_id' => 'integer',
            'kiosk_user_id' => 'integer',
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function guardUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guard_user_id', 'user_id');
    }

    public function kioskUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kiosk_user_id', 'user_id');
    }

    public function guardProfile(): HasOne
    {
        return $this->hasOne(Guard::class, 'user_id', 'guard_user_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'duty_shift_id', 'shift_id');
    }

    public function isActive(): bool
    {
        return $this->clock_out_at === null;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('clock_out_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('clock_out_at');
    }
}
