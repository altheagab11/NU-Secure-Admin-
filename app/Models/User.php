<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['first_name','last_name','name', 'email', 'password','password_hash'])]
#[Hidden(['password', 'password_hash', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Primary key name used by the users table on Supabase
     */
    protected $primaryKey = 'user_id';

    /**
     * Allow mass assignment for common user columns.
     */
    protected $fillable = ['first_name', 'last_name', 'name', 'email', 'password', 'password_hash', 'role_id'];

    /**
     * Supabase users table has created_at but no updated_at.
     */
    const UPDATED_AT = null;

    /**
     * Use users.password_hash as the auth password field.
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * Use Supabase password_hash as the auth password field.
     */
    public function getAuthPassword(): string
    {
        return (string) ($this->getAttributes()['password_hash'] ?? $this->password_hash ?? '');
    }

    public function guardProfile(): HasOne
    {
        return $this->hasOne(Guard::class, 'user_id', 'user_id');
    }

    public function dutyShifts(): HasMany
    {
        return $this->hasMany(GuardDutyShift::class, 'guard_user_id', 'user_id');
    }

    public static function findByEmail(string $email): ?self
    {
        $email = strtolower(trim($email));

        if ($email === '') {
            return null;
        }

        return static::query()
            ->whereRaw("LOWER(TRIM(COALESCE(email, ''))) = ?", [$email])
            ->first();
    }

    /**
     * Safe user payload for mobile API responses.
     *
     * @return array{user_id: int, first_name: mixed, last_name: mixed, email: mixed, role_id: int, status: string}
     */
    public function toApiUser(): array
    {
        $status = trim((string) ($this->status ?? 'active'));
        $normalized = strtolower($status);

        $displayStatus = match (true) {
            $normalized === '' || $normalized === 'active' => 'Active',
            $normalized === 'recycle_bin' => 'Inactive',
            default => ucfirst($status),
        };

        return [
            'user_id' => (int) $this->user_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'role_id' => (int) $this->role_id,
            'status' => $displayStatus,
        ];
    }
}
