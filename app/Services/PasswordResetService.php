<?php

namespace App\Services;

use App\Mail\PasswordChangedMail;
use App\Mail\PasswordResetVerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PasswordResetService
{
    public const GENERIC_REQUEST_MESSAGE = 'If an account exists for this email, a verification code has been sent.';

    public const GENERIC_RESEND_MESSAGE = 'If an account exists for this email, a verification code has been sent.';

    public const CODE_EXPIRE_MINUTES = 10;

    public const MAX_VERIFY_ATTEMPTS = 5;

    public const RESEND_COOLDOWN_SECONDS = 60;

    public const VERIFIED_STATE_EXPIRE_MINUTES = 10;

    public const TOKEN_INVALID = 'invalid';

    public const TOKEN_EXPIRED = 'expired';

    public const TOKEN_VALID = 'valid';

    public const CODE_TOO_MANY_ATTEMPTS = 'too_many_attempts';

    public const RESEND_COOLDOWN = 'resend_cooldown';

    /**
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
                    $this->sendVerificationCode($user);
                } catch (\Throwable $e) {
                    Log::warning('Password reset verification code could not be sent.', [
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
     * @return array{success: bool, message: string, code?: string, retry_after?: int}
     */
    public function resendVerificationCode(string $email): array
    {
        $email = Str::lower(trim($email));
        $cooldown = $this->resendCooldownStatus($email);

        if (! $cooldown['allowed']) {
            ActivityLogService::log(
                action: 'Password Reset Request Rate-Limited',
                module: 'Authentication',
                description: 'Password reset resend was rate-limited due to cooldown.',
                entityType: 'User',
                status: ActivityLogService::STATUS_FAILED,
                userId: null
            );

            return [
                'success' => false,
                'message' => 'Please wait before requesting another verification code.',
                'code' => self::RESEND_COOLDOWN,
                'retry_after' => $cooldown['retry_after'],
            ];
        }

        return $this->requestReset($email);
    }

    /**
     * @return array{success: bool, message: string, code?: string, reset_token?: string}
     */
    public function verifyCode(string $email, string $code): array
    {
        $email = Str::lower(trim($email));
        $code = preg_replace('/\D/', '', (string) $code) ?? '';

        if ($code === '' || strlen($code) !== 6) {
            return [
                'success' => false,
                'message' => 'The verification code is invalid. Please try again.',
                'code' => self::TOKEN_INVALID,
            ];
        }

        $attemptKey = $this->verifyAttemptsCacheKey($email);
        $attempts = (int) Cache::get($attemptKey, 0);

        if ($attempts >= self::MAX_VERIFY_ATTEMPTS) {
            $this->invalidateVerificationCode($email);

            return [
                'success' => false,
                'message' => 'Too many invalid verification attempts. Please request a new verification code.',
                'code' => self::CODE_TOO_MANY_ATTEMPTS,
            ];
        }

        try {
            $codeStatus = $this->inspectVerificationCode($email, $code);
        } catch (\Throwable $e) {
            Log::warning('Password reset verification could not be processed.');

            return [
                'success' => false,
                'message' => 'Unable to process your request right now. Please try again.',
                'code' => 'server_error',
            ];
        }

        if ($codeStatus === self::TOKEN_EXPIRED) {
            return [
                'success' => false,
                'message' => 'This verification code has expired. Please request a new code.',
                'code' => self::TOKEN_EXPIRED,
            ];
        }

        if ($codeStatus !== self::TOKEN_VALID) {
            Cache::put($attemptKey, $attempts + 1, now()->addMinutes(self::CODE_EXPIRE_MINUTES));

            if (($attempts + 1) >= self::MAX_VERIFY_ATTEMPTS) {
                $this->invalidateVerificationCode($email);

                ActivityLogService::log(
                    action: 'Password Reset Failed',
                    module: 'Authentication',
                    description: 'Password reset verification failed after too many invalid code attempts.',
                    entityType: 'User',
                    status: ActivityLogService::STATUS_FAILED,
                    userId: null
                );

                return [
                    'success' => false,
                    'message' => 'Too many invalid verification attempts. Please request a new verification code.',
                    'code' => self::CODE_TOO_MANY_ATTEMPTS,
                ];
            }

            return [
                'success' => false,
                'message' => 'The verification code is invalid. Please try again.',
                'code' => self::TOKEN_INVALID,
            ];
        }

        $user = User::findByEmail($email);

        if (! $user || ! $this->isEligibleForReset($user)) {
            return [
                'success' => false,
                'message' => 'The verification code is invalid. Please try again.',
                'code' => self::TOKEN_INVALID,
            ];
        }

        Cache::forget($attemptKey);

        $resetToken = $this->createVerifiedState($email);

        ActivityLogService::log(
            action: 'Password Reset Code Verified',
            module: 'Authentication',
            description: 'Password reset verification code was successfully verified.',
            entityType: 'User',
            entityId: $user->user_id ?? null,
            userId: null
        );

        return [
            'success' => true,
            'message' => 'Verification successful. You may now create a new password.',
            'reset_token' => $resetToken,
        ];
    }

    /**
     * @return array{success: bool, message: string, code?: string}
     */
    public function resetPassword(string $email, string $password, ?string $resetToken = null): array
    {
        $email = Str::lower(trim($email));

        if (! $this->hasValidVerifiedState($email, $resetToken)) {
            ActivityLogService::log(
                action: 'Password Reset Failed',
                module: 'Authentication',
                description: 'Password reset failed because verification was missing or expired.',
                entityType: 'User',
                status: ActivityLogService::STATUS_FAILED,
                userId: null
            );

            return [
                'success' => false,
                'message' => 'Your verification has expired. Please request a new verification code.',
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
                'message' => 'Unable to reset your password. Please request a new verification code.',
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
            $this->invalidateVerificationCode($email);
            $this->clearVerifiedState($email, $resetToken);
            $this->invalidateUserSessions((int) $user->user_id);
            $this->sendPasswordChangedNotification($user);
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

    public function sendVerificationCode(User $user): void
    {
        $plainCode = $this->generateVerificationCode();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($plainCode),
                'created_at' => now('Asia/Manila'),
            ]
        );

        Cache::forget($this->verifyAttemptsCacheKey(Str::lower(trim($user->email))));
        Cache::put($this->resendCooldownCacheKey(Str::lower(trim($user->email))), now()->timestamp, now()->addMinutes(15));

        Mail::to($user->email)->send(new PasswordResetVerificationCodeMail(
            verificationCode: $plainCode,
            expiresInMinutes: self::CODE_EXPIRE_MINUTES,
        ));

        ActivityLogService::log(
            action: 'Password Reset Requested',
            module: 'Authentication',
            description: 'A password reset verification code was requested for a NU-Secure account.',
            entityType: 'User',
            entityId: $user->user_id ?? null,
            userId: null
        );
    }

    public function maskEmail(string $email): string
    {
        $email = Str::lower(trim($email));

        if ($email === '' || ! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $localLength = strlen($local);

        if ($localLength <= 2) {
            $maskedLocal = str_repeat('*', max(1, $localLength));
        } else {
            $maskedLocal = $local[0].str_repeat('*', max(1, $localLength - 2)).$local[$localLength - 1];
        }

        return $maskedLocal.'@'.$domain;
    }

    /**
     * @return array{allowed: bool, retry_after: int}
     */
    public function resendCooldownStatus(string $email): array
    {
        $email = Str::lower(trim($email));
        $lastSent = Cache::get($this->resendCooldownCacheKey($email));

        if (! $lastSent) {
            return ['allowed' => true, 'retry_after' => 0];
        }

        $elapsed = time() - (int) $lastSent;

        if ($elapsed >= self::RESEND_COOLDOWN_SECONDS) {
            return ['allowed' => true, 'retry_after' => 0];
        }

        return [
            'allowed' => false,
            'retry_after' => self::RESEND_COOLDOWN_SECONDS - $elapsed,
        ];
    }

    public function storeWebVerifiedState(string $email, string $resetToken): void
    {
        session([
            'password_reset.email' => Str::lower(trim($email)),
            'password_reset.verified_at' => now()->timestamp,
            'password_reset.reset_token' => $resetToken,
        ]);
    }

    public function clearWebVerifiedState(): void
    {
        session()->forget([
            'password_reset.email',
            'password_reset.verified_at',
            'password_reset.reset_token',
            'password_reset.pending_email',
            'password_reset.step',
            'password_reset.masked_email',
        ]);
    }

    public function hasWebVerifiedState(string $email): bool
    {
        $resetToken = (string) session('password_reset.reset_token', '');

        return $this->hasValidVerifiedState(Str::lower(trim($email)), $resetToken !== '' ? $resetToken : null);
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

    public function findValidTokenRow(string $email, string $token): ?object
    {
        return $this->inspectToken($email, $token) === self::TOKEN_VALID
            ? $this->lookupTokenRow($email)
            : null;
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

    protected function generateVerificationCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * @return self::TOKEN_VALID|self::TOKEN_INVALID|self::TOKEN_EXPIRED
     */
    protected function inspectVerificationCode(string $email, string $code): string
    {
        if ($email === '' || $code === '') {
            return self::TOKEN_INVALID;
        }

        $tokenRow = $this->lookupTokenRow($email);

        if (! $tokenRow || ! Hash::check($code, (string) $tokenRow->token)) {
            return self::TOKEN_INVALID;
        }

        $createdAt = isset($tokenRow->created_at) ? strtotime((string) $tokenRow->created_at) : null;

        if (! $createdAt || (time() - $createdAt) > (self::CODE_EXPIRE_MINUTES * 60)) {
            return self::TOKEN_EXPIRED;
        }

        return self::TOKEN_VALID;
    }

    protected function createVerifiedState(string $email): string
    {
        $resetToken = Str::random(64);

        Cache::put(
            $this->verifiedStateCacheKey($resetToken),
            [
                'email' => Str::lower(trim($email)),
                'created_at' => now()->timestamp,
            ],
            now()->addMinutes(self::VERIFIED_STATE_EXPIRE_MINUTES)
        );

        return $resetToken;
    }

    protected function hasValidVerifiedState(string $email, ?string $resetToken): bool
    {
        if ($resetToken === null || $resetToken === '') {
            return false;
        }

        $state = Cache::get($this->verifiedStateCacheKey($resetToken));

        if (! is_array($state)) {
            return false;
        }

        if (Str::lower(trim((string) ($state['email'] ?? ''))) !== Str::lower(trim($email))) {
            return false;
        }

        $createdAt = (int) ($state['created_at'] ?? 0);

        if ($createdAt <= 0 || (time() - $createdAt) > (self::VERIFIED_STATE_EXPIRE_MINUTES * 60)) {
            return false;
        }

        return true;
    }

    protected function clearVerifiedState(string $email, ?string $resetToken): void
    {
        if ($resetToken !== null && $resetToken !== '') {
            Cache::forget($this->verifiedStateCacheKey($resetToken));
        }

        $this->clearWebVerifiedState();
    }

    protected function invalidateVerificationCode(string $email): void
    {
        DB::table('password_reset_tokens')
            ->whereRaw('LOWER(TRIM(COALESCE(email, \'\'))) = ?', [Str::lower(trim($email))])
            ->delete();

        Cache::forget($this->verifyAttemptsCacheKey(Str::lower(trim($email))));
    }

    protected function sendPasswordChangedNotification(User $user): void
    {
        $fullName = trim(trim((string) ($user->first_name ?? '')).' '.trim((string) ($user->last_name ?? '')));

        Mail::to($user->email)->send(new PasswordChangedMail(
            fullName: $fullName !== '' ? $fullName : 'User',
        ));
    }

    protected function lookupTokenRow(string $email): ?object
    {
        return DB::table('password_reset_tokens')
            ->whereRaw('LOWER(TRIM(COALESCE(email, \'\'))) = ?', [Str::lower(trim($email))])
            ->first();
    }

    protected function verifyAttemptsCacheKey(string $email): string
    {
        return 'password-reset-verify-attempts:'.Str::lower(trim($email));
    }

    protected function resendCooldownCacheKey(string $email): string
    {
        return 'password-reset-resend:'.Str::lower(trim($email));
    }

    protected function verifiedStateCacheKey(string $resetToken): string
    {
        return 'password-reset-verified:'.hash('sha256', $resetToken);
    }
}
