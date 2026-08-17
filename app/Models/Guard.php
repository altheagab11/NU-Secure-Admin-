<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guard extends Model
{
    use HasFactory;

    // The database table name (observed in your DB UI screenshot is `guard`)
    protected $table = 'guard';

    protected $primaryKey = 'guard_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'badge_number',
        'station',
    ];

    /**
     * The user account associated with this guard record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', (new User())->getKeyName());
    }

    public function dutyShifts(): HasMany
    {
        return $this->hasMany(GuardDutyShift::class, 'guard_user_id', 'user_id');
    }
}
