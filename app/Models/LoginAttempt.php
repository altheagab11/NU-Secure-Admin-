<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttempt extends Model
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_BLOCKED = 'blocked';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_GUARD = 'guard';

    public const ROLE_OFFICE = 'office';

    public const SOURCE_WEB = 'Web';

    public const SOURCE_API = 'API';

    public const SOURCE_MOBILE = 'Mobile';

    protected $table = 'login_attempts';

    protected $fillable = [
        'user_id',
        'email',
        'role',
        'status',
        'failure_reason',
        'ip_address',
        'user_agent',
        'device_type',
        'login_source',
        'attempted_at',
    ];

    protected $hidden = [
        'password',
        'password_hash',
        'token',
        'access_token',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'attempted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
