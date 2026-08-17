<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    protected $table = 'visit';

    protected $primaryKey = 'visit_id';

    public $timestamps = false;

    protected $fillable = [
        'visitor_id',
        'guard_user_id',
        'visit_type_id',
        'purpose_reason',
        'primary_office_id',
        'qr_token',
        'entry_time',
        'exit_time',
        'duration_minutes',
        'exit_status_id',
        'destination_text',
        'pass_number',
        'control_number',
        'on_duty_guard_id',
        'duty_shift_id',
    ];

    protected function casts(): array
    {
        return [
            'visitor_id' => 'integer',
            'guard_user_id' => 'integer',
            'visit_type_id' => 'integer',
            'primary_office_id' => 'integer',
            'exit_status_id' => 'integer',
            'on_duty_guard_id' => 'integer',
            'duty_shift_id' => 'integer',
            'entry_time' => 'datetime',
            'exit_time' => 'datetime',
        ];
    }

    public function onDutyGuard(): BelongsTo
    {
        return $this->belongsTo(User::class, 'on_duty_guard_id', 'user_id');
    }

    public function registeredByGuard(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guard_user_id', 'user_id');
    }

    public function dutyShift(): BelongsTo
    {
        return $this->belongsTo(GuardDutyShift::class, 'duty_shift_id', 'shift_id');
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class, 'visitor_id', 'visitor_id');
    }
}
