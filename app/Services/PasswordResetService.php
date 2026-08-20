<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PasswordResetService
{
    public const GENERIC_REQUEST_MESSAGE = 'If an account exists for this email, password reset instructions have been sent.';

    public const TOKEN_INVALID = 'invalid';

    public const TOKEN_EXPIRED = 'expired';

    public const TOKEN_VALID = 'valid';

    /**
     * Process a forgot-password request without revealing whether the email exists.
     *
     * @return array{success: bool, message: string}
     */
    public function requestReset(string $email): array
    {
        $email = Str::lower(trim($email));
        $message = self::GENERIC_REQUEST_MESSAGE;

        try {
            $user = User::findByEmail($email);

            if ($this->isEligibleForReset($user)) {
                try {
                    $this->sendResetLink($user);
                } catch (\Throwable $e) {
                    Log::warning('Password reset email could not be sent.', [
                        'user_id' => $user->user_id ?? null,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Password reset request could not be processed.');

            return [
                'success' => false,
                'message' => 'Unable to process your request right now. Please try again.',
            ];
        }

        return [
            'success' => true,
            'message' => $message,
        ];
    }

    /**
     * @return array{success: bool, message: string, code?: string}
     */
    public function resetPassword(string $email, string $token, string $password): array
    {
        $email = Str::lower(trim($email));
        $token = (string) $token;

        try {
            $tokenStatus = $this->inspectToken($email, $token);
        } catch (\Throwable $e) {
            Log::warning('Password reset could not be completed.');

            return [
                'success' => false,
                'message' => 'Unable to process your request right now. Please try again.',
                'code' => 'server_error',
            ];
        }

        if ($tokenStatus === self::TOKEN_EXPIRED) {
            ActivityLogService::log(
                action: 'Password Reset Failed',
                module: 'Authentication',
                description: 'Password reset failed because the reset link had expired.',
                entityType: 'User',
                status: ActivityLogService::STATUS_FAILED,
                userId: null
            );

            return [
                'success' => false,
                'message' => 'This password reset link has expired. Please request a new one.',
                'code' => self::TOKEN_EXPIRED,
            ];
        }

        if ($tokenStatus !== self::TOKEN_VALID) {
            ActivityLogService::log(
                action: 'Password Reset Failed',
                module: 'Authentication',
                description: 'Password reset failed because the reset link was invalid or already used.',
                entityType: 'User',
                status: ActivityLogService::STATUS_FAILED,
                userId: null
            );

            return [
                'success' => false,
                'message' => 'This password reset link is invalid or has already been used.',
                'code' => self::TOKEN_INVALID,
            ];
        }

        $user = User::findByEmail($email);

        if (! $user || ! $this->isEligibleForReset($user)) {
            ActivityLogService::log(
                action: 'Password Reset Failed',
                module: 'Authentication',
                description: 'Password reset failed because the account could not be updated.',
                entityType: 'User',
                status: ActivityLogService::STATUS_FAILED,
                userId: null
            );

            return [
                'success' => false,
                'message' => 'This password reset link is invalid or has already been used.',
                'code' => self::TOKEN_INVALID,
            ];
        }

        try {
            $newPasswordHash = Hash::make($password);
            $payload = ['password_hash' => $newPasswordHash];

            if (Schema::hasColumn('users', 'password')) {
                $payload['password'] = $newPasswordHash;
            }

            if (Schema::hasColumn('users', 'remember_token')) {
                $payload['remember_token'] = Str::random(60);
            }

            $updated = DB::table('users')
                ->where('user_id', (int) $user->user_id)
                ->update($payload);

            if ($updated < 1) {
                throw new \RuntimeException('Password update did not persist.');
            }
        } catch (\Throwable $e) {
            Log::warning('Password reset could not be completed.', [
                'user_id' => $user->user_id ?? null,
            ]);

            return [
                'success' => false,
                'message' => 'Unable to process your request right now. Please try again.',
                'code' => 'server_error',
            ];
        }

        try {
            DB::table('password_reset_tokens')
                ->whereRaw('LOWER(TRIM(COALESCE(email, \'\'))) = ?', [$email])
                ->delete();

            $this->invalidateUserSessions((int) $user->user_id);
        } catch (\Throwable $e) {
            Log::warning('Password reset cleanup could not be completed.', [
                'user_id' => $user->user_id ?? null,
            ]);
        }

        ActivityLogService::log(
            action: 'Password Reset Successful',
            module: 'Authentication',
            description: 'User successfully reset account password.',
            entityType: 'User',
            entityId: $user->user_id ?? null,
            userId: null
        );

        return [
            'success' => true,
            'message' => 'Your password has been reset successfully. You can now sign in using your new password.',
        ];
    }

    public function sendResetLink(User $user): void
    {
        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now('Asia/Manila'),
            ]
        );

        $expiresInMinutes = (int) config('auth.passwords.users.expire', 60);

        $webResetUrl = route('password.reset', [
            'token' => $plainToken,
            'email' => $user->email,
        ]);

        $mobileResetUrl = 'nusecure://reset-password?'.http_build_query([
            'token' => $plainToken,
            'email' => $user->email,
        ]);

        $fullName = trim(trim((string) ($user->first_name ?? '')).' '.trim((string) ($user->last_name ?? '')));

        Mail::to($user->email)->send(new PasswordResetMail(
            $fullName !== '' ? $fullName : 'User',
            $webResetUrl,
            $expiresInMinutes,
            $mobileResetUrl,
        ));

        ActivityLogService::log(
            action: 'Password Reset Requested',
            module: 'Authentication',
            description: 'A password reset link was requested for a NU-Secure account.',
            entityType: 'User',
            entityId: $user->user_id ?? null,
            userId: null
        );
    }

    public function findValidTokenRow(string $email, string $token): ?object
    {
        return $this->inspectToken($email, $token) === self::TOKEN_VALID
            ? $this->lookupTokenRow($email)
            : null;
    }

    /**
     * @return self::TOKEN_VALID|self::TOKEN_INVALID|self::TOKEN_EXPIRED
     */
    public function inspectToken(string $email, string $token): string
    {
        if ($email === '' || $token === '') {
            return self::TOKEN_INVALID;
        }

        $tokenRow = $this->lookupTokenRow($email);

        if (! $tokenRow || ! Hash::check($token, (string) $tokenRow->token)) {
            return self::TOKEN_INVALID;
        }

        $expiresInMinutes = (int) config('auth.passwords.users.expire', 60);
        $createdAt = isset($tokenRow->created_at) ? strtotime((string) $tokenRow->created_at) : null;

        if (! $createdAt || (time() - $createdAt) > ($expiresInMinutes * 60)) {
            return self::TOKEN_EXPIRED;
        }

        return self::TOKEN_VALID;
    }

    public function isEligibleForReset(?User $user): bool
    {
        if (! $user || ! filled($user->email)) {
            return false;
        }

        $status = Str::lower(trim((string) ($user->status ?? 'active')));

        return ! in_array($status, ['inactive', 'disabled', 'suspended', 'recycle_bin', 'deleted'], true);
    }

    public function invalidateUserSessions(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        if (config('session.driver') === 'database' && Schema::hasTable('sessions')) {
            try {
                DB::table('sessions')->where('user_id', $userId)->delete();
            } catch (\Throwable $e) {
                Log::warning('Unable to invalidate sessions after password reset.', [
                    'user_id' => $userId,
                ]);
            }
        }

        if (Schema::hasTable('personal_access_tokens')) {
            try {
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', User::class)
                    ->where('tokenable_id', $userId)
                    ->delete();
            } catch (\Throwable $e) {
                Log::warning('Unable to revoke API tokens after password reset.', [
                    'user_id' => $userId,
                ]);
            }
        }
    }

    protected function lookupTokenRow(string $email): ?object
    {
        return DB::table('password_reset_tokens')
            ->whereRaw('LOWER(TRIM(COALESCE(email, \'\'))) = ?', [$email])
            ->first();
    }
}
