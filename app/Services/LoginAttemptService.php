<?php

namespace App\Services;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class LoginAttemptService
{
    public const STATUS_SUCCESS = LoginAttempt::STATUS_SUCCESS;

    public const STATUS_FAILED = LoginAttempt::STATUS_FAILED;

    public const STATUS_BLOCKED = LoginAttempt::STATUS_BLOCKED;

    public const MAX_ATTEMPTS = 5;

    public const DECAY_SECONDS = 900;

    public const REASON_INCORRECT_PASSWORD = 'Incorrect password';

    public const REASON_ACCOUNT_NOT_FOUND = 'Account not found';

    public const REASON_INACTIVE = 'Inactive account';

    public const REASON_DISABLED = 'Account disabled';

    public const REASON_TOO_MANY_ATTEMPTS = 'Too many failed attempts';

    public const REASON_INVALID_CREDENTIALS = 'Invalid credentials';

    public const REASON_UNAUTHORIZED_ROLE = 'Unauthorized role';

    public const REASON_OFFICE_NOT_ASSIGNED = 'Account is not assigned to an office';

    public const REASON_CAPTCHA_MISSING = 'CAPTCHA not completed';

    public const REASON_CAPTCHA_FAILED = 'CAPTCHA verification failed';

    /**
     * @var list<string>
     */
    protected const SENSITIVE_FRAGMENTS = [
        'password',
        'token',
        'secret',
        'authorization',
        'cookie',
        'session',
        'bearer',
    ];

    public static function recordSuccess(?User $user, string $email, Request $request): ?LoginAttempt
    {
        return self::record(
            user: $user,
            email: $email,
            status: self::STATUS_SUCCESS,
            failureReason: null,
            request: $request
        );
    }

    public static function recordFailure(?User $user, string $email, string $failureReason, Request $request): ?LoginAttempt
    {
        return self::record(
            user: $user,
            email: $email,
            status: self::STATUS_FAILED,
            failureReason: $failureReason,
            request: $request
        );
    }

    public static function recordBlocked(?User $user, string $email, Request $request): ?LoginAttempt
    {
        return self::record(
            user: $user,
            email: $email,
            status: self::STATUS_BLOCKED,
            failureReason: self::REASON_TOO_MANY_ATTEMPTS,
            request: $request
        );
    }

    public static function record(
        ?User $user,
        string $email,
        string $status,
        ?string $failureReason,
        Request $request
    ): ?LoginAttempt {
        try {
            $normalizedEmail = self::normalizeEmail($email);
            $role = self::roleSlug($user);

            return LoginAttempt::query()->create([
                'user_id' => $user?->user_id !== null ? (int) $user->user_id : null,
                'email' => $normalizedEmail !== '' ? mb_substr($normalizedEmail, 0, 255) : null,
                'role' => $role,
                'status' => self::normalizeStatus($status),
                'failure_reason' => $failureReason !== null
                    ? mb_substr(self::sanitizeReason($failureReason), 0, 100)
                    : null,
                'ip_address' => $request->ip(),
                'user_agent' => self::safeUserAgent($request->userAgent()),
                'device_type' => self::detectDeviceType($request->userAgent()),
                'login_source' => self::detectLoginSource($request),
                'attempted_at' => now('Asia/Manila'),
            ]);
        } catch (Throwable $e) {
            Log::error('Login attempt write failed.', [
                'email' => self::normalizeEmail($email),
                'status' => $status,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public static function throttleKey(Request $request, string $email): string
    {
        $emailPart = self::normalizeEmail($email);
        $ipPart = (string) ($request->ip() ?: 'unknown');

        return Str::transliterate('login-attempt|'.$emailPart.'|'.$ipPart);
    }

    public static function tooManyAttempts(Request $request, string $email): bool
    {
        return RateLimiter::tooManyAttempts(self::throttleKey($request, $email), self::MAX_ATTEMPTS);
    }

    public static function hit(Request $request, string $email): void
    {
        RateLimiter::hit(self::throttleKey($request, $email), self::DECAY_SECONDS);
    }

    public static function clear(Request $request, string $email): void
    {
        RateLimiter::clear(self::throttleKey($request, $email));
    }

    public static function roleSlug(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return match ((int) ($user->role_id ?? 0)) {
            1 => LoginAttempt::ROLE_ADMIN,
            2, 4 => LoginAttempt::ROLE_GUARD,
            3 => LoginAttempt::ROLE_OFFICE,
            default => null,
        };
    }

    public static function roleLabel(?string $role): string
    {
        return match ($role) {
            LoginAttempt::ROLE_ADMIN, '1' => 'Administrator',
            LoginAttempt::ROLE_GUARD, '2', '4' => 'Guard',
            LoginAttempt::ROLE_OFFICE, '3' => 'Office Staff',
            default => '—',
        };
    }

    public static function statusLabel(string $status): string
    {
        return match (strtolower(trim($status))) {
            self::STATUS_SUCCESS => 'Successful',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_BLOCKED => 'Blocked',
            default => 'Failed',
        };
    }

    public static function inactiveReason(User $user): string
    {
        $status = strtolower(trim((string) ($user->status ?? 'active')));

        return $status === 'disabled'
            ? self::REASON_DISABLED
            : self::REASON_INACTIVE;
    }

    public static function detectDeviceType(?string $userAgent): string
    {
        $ua = strtolower(trim((string) $userAgent));

        if ($ua === '') {
            return 'Unknown';
        }

        if (preg_match('/ipad|tablet|playbook|silk|(android(?!.*mobile))/i', $ua) === 1) {
            return 'Tablet';
        }

        if (preg_match('/mobile|iphone|ipod|android|blackberry|opera mini|iemobile|webos|fennec/i', $ua) === 1) {
            return 'Mobile';
        }

        return 'Desktop';
    }

    public static function detectLoginSource(Request $request): string
    {
        $isApi = $request->is('api/*') || $request->expectsJson();

        if (! $isApi) {
            return LoginAttempt::SOURCE_WEB;
        }

        $ua = strtolower((string) $request->userAgent());
        $deviceName = strtolower(trim((string) ($request->input('device_name', $request->input('device_name', '')))));
        $haystack = $ua.' '.$deviceName;

        if (preg_match('/mobile|android|iphone|ipad|okhttp|dalvik|cfnetwork|darwin|flutter|okhttp/i', $haystack) === 1) {
            return LoginAttempt::SOURCE_MOBILE;
        }

        return LoginAttempt::SOURCE_API;
    }

    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    protected static function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            self::STATUS_SUCCESS, 'successful' => self::STATUS_SUCCESS,
            self::STATUS_BLOCKED => self::STATUS_BLOCKED,
            default => self::STATUS_FAILED,
        };
    }

    protected static function sanitizeReason(string $reason): string
    {
        $clean = trim($reason);

        foreach (self::SENSITIVE_FRAGMENTS as $fragment) {
            if (str_contains(strtolower($clean), $fragment) && ! in_array($clean, [
                self::REASON_INCORRECT_PASSWORD,
                self::REASON_INVALID_CREDENTIALS,
                self::REASON_CAPTCHA_MISSING,
                self::REASON_CAPTCHA_FAILED,
            ], true)) {
                return self::REASON_INVALID_CREDENTIALS;
            }
        }

        return $clean;
    }

    protected static function safeUserAgent(?string $userAgent): ?string
    {
        $value = trim((string) $userAgent);

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, 512);
    }
}
