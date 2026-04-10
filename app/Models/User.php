<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['first_name','last_name','name', 'email', 'password','password_hash'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
     * Use Supabase password_hash as the auth password field.
     */
    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }
}
