<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogService
{
    public const STATUS_SUCCESS = 'Success';

    public const STATUS_FAILED = 'Failed';

    public const STATUS_WARNING = 'Warning';

    /**
     * Keys that must never be persisted in old_values / new_values.
     *
     * @var list<string>
     */
    protected const SENSITIVE_KEYS = [
        'password',
        'password_hash',
        'password_confirmation',
        'current_password',
        'new_password',
        'plain_password',
        'passwordplain',
        'remember_token',
        'api_token',
        'access_token',
        'refresh_token',
        'auth_token',
        'authorization',
        'cookie',
        'cookies',
        'session',
        'session_payload',
        'cvv',
        'card_cvv',
        'card_number',
        'qr_token',
        'qr_payload',
        'secret',
        'private_key',
        'bearer',
        'setup_token',
        'reset_token',
        'token',
    ];

    /**
     * Record a human-readable audit event. Failures never interrupt the caller.
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?string $entityType = null,
        int|string|null $entityId = null,
        mixed $oldValues = null,
        mixed $newValues = null,
        string $status = self::STATUS_SUCCESS,
        ?int $userId = null
    ): ?ActivityLog {
        try {
            $request = request();
            $resolvedUserId = $userId;

            if ($resolvedUserId === null && Auth::check()) {
                $authId = Auth::id();
                $resolvedUserId = is_numeric($authId) ? (int) $authId : null;
            }

            $entityIdValue = null;
            if ($entityId !== null && $entityId !== '' && is_numeric($entityId)) {
                $entityIdValue = (int) $entityId;
            }

            return ActivityLog::query()->create([
                'user_id' => $resolvedUserId,
                'action' => mb_substr(trim($action), 0, 100),
                'module' => mb_substr(trim($module), 0, 100),
                'description' => trim($description),
                'entity_type' => $entityType !== null ? mb_substr(trim($entityType), 0, 100) : null,
                'entity_id' => $entityIdValue,
                'old_values' => self::sanitize($oldValues),
                'new_values' => self::sanitize($newValues),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'request_method' => $request?->method() ? mb_substr($request->method(), 0, 10) : null,
                'request_url' => $request?->fullUrl(),
                'status' => self::normalizeStatus($status),
                'created_at' => now('Asia/Manila'),
            ]);
        } catch (Throwable $e) {
            Log::error('Activity log write failed.', [
                'action' => $action,
                'module' => $module,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public static function actorLabel(?User $user = null): string
    {
        $user ??= Auth::user();

        if (! $user instanceof User) {
            return 'System';
        }

        $first = trim((string) ($user->first_name ?? ''));
        $last = trim((string) ($user->last_name ?? ''));
        $full = trim($first.' '.$last);

        if ($full === '') {
            $full = trim((string) ($user->name ?? $user->email ?? 'User'));
        }

        $short = $first !== '' ? $first : $full;

        return match ((int) ($user->role_id ?? 0)) {
            1 => $full,
            2, 4 => 'Guard '.$short,
            3 => 'Office Staff '.$short,
            default => $full,
        };
    }

    public static function roleLabel(?int $roleId): string
    {
        return match ((int) $roleId) {
            1 => 'Administrator',
            2, 4 => 'Guard',
            3 => 'Office Staff',
            default => 'System',
        };
    }

    public static function userDisplayName(?object $user): string
    {
        if (! $user) {
            return 'System';
        }

        $first = trim((string) ($user->first_name ?? ''));
        $last = trim((string) ($user->last_name ?? ''));
        $full = trim($first.' '.$last);

        if ($full !== '') {
            return $full;
        }

        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $email = trim((string) ($user->email ?? ''));

        return $email !== '' ? $email : 'User';
    }

    /**
     * Remove sensitive fields from payloads before they are stored.
     */
    public static function sanitize(mixed $values): ?array
    {
        if ($values === null) {
            return null;
        }

        if ($values instanceof \Illuminate\Support\Collection) {
            $values = $values->toArray();
        } elseif (is_object($values)) {
            $values = method_exists($values, 'toArray')
                ? $values->toArray()
                : get_object_vars($values);
        }

        if (! is_array($values)) {
            return null;
        }

        $clean = self::sanitizeArray($values);

        return $clean === [] ? null : $clean;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected static function sanitizeArray(array $values): array
    {
        $clean = [];

        foreach ($values as $key => $value) {
            if (is_string($key) && self::isSensitiveKey($key)) {
                continue;
            }

            if (is_array($value)) {
                $nested = self::sanitizeArray($value);
                if ($nested !== []) {
                    $clean[$key] = $nested;
                }

                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    protected static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower(trim($key));
        $normalized = str_replace(['-', ' '], '_', $normalized);

        if (in_array($normalized, self::SENSITIVE_KEYS, true)) {
            return true;
        }

        foreach (['password', 'token', 'secret', 'authorization', 'cookie', 'cvv', 'session'] as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return true;
            }
        }

        return false;
    }

    protected static function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'failed', 'fail', 'error' => self::STATUS_FAILED,
            'warning', 'warn' => self::STATUS_WARNING,
            default => self::STATUS_SUCCESS,
        };
    }
}
