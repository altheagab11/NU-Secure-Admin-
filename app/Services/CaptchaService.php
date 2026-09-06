<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CaptchaService
{
    public const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public const TOKEN_FIELD = 'cf-turnstile-response';

    /**
     * Cloudflare's official dummy keys for local/testing only.
     *
     * @see https://developers.cloudflare.com/turnstile/troubleshooting/testing/
     */
    public const DUMMY_SITE_KEY = '1x00000000000000000000AA';

    public const DUMMY_SECRET_KEY = '1x0000000000000000000000000000000AA';

    public const DUMMY_SECRET_KEY_ALWAYS_FAIL = '2x0000000000000000000000000000000AA';

    public function siteKey(): string
    {
        $configured = trim((string) config('services.turnstile.site_key'));

        if ($configured !== '') {
            return $configured;
        }

        return $this->allowsDummyKeys() ? self::DUMMY_SITE_KEY : '';
    }

    public function isConfigured(): bool
    {
        return $this->secretKey() !== '' && $this->siteKey() !== '';
    }

    public function tokenFrom(?string $value): string
    {
        return trim((string) $value);
    }

    public function verify(string $token, ?string $ipAddress = null): bool
    {
        $token = $this->tokenFrom($token);

        if ($token === '') {
            return false;
        }

        $secret = $this->secretKey();

        if ($secret === '') {
            Log::error('Turnstile secret key is not configured. Login CAPTCHA failed closed.');

            return false;
        }

        // Localhost / phpunit with Cloudflare test keys: accept any non-empty token.
        // Dummy widgets often reuse the same token, so replay locking must stay off here.
        if ($this->isLocalTestingMode($secret)) {
            if ($secret === self::DUMMY_SECRET_KEY_ALWAYS_FAIL) {
                return false;
            }

            return true;
        }

        $replayKey = $this->replayCacheKey($token);

        if (Cache::has($replayKey)) {
            return false;
        }

        $payload = [
            'secret' => $secret,
            'response' => $token,
        ];

        if (is_string($ipAddress) && $ipAddress !== '') {
            $payload['remoteip'] = $ipAddress;
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(8)
                ->post(self::VERIFY_URL, $payload);
        } catch (ConnectionException|Throwable $e) {
            Log::warning('Turnstile verification request failed.', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('Turnstile verification returned a non-success HTTP status.', [
                'status' => $response->status(),
            ]);

            return false;
        }

        $success = $response->json('success') === true;

        if (! $success) {
            Log::warning('Turnstile verification rejected the token.', [
                'error_codes' => $response->json('error-codes') ?? [],
            ]);

            return false;
        }

        Cache::put($replayKey, true, now()->addMinutes(5));

        return true;
    }

    protected function secretKey(): string
    {
        $configured = trim((string) config('services.turnstile.secret_key'));

        if ($configured !== '') {
            return $configured;
        }

        return $this->allowsDummyKeys() ? self::DUMMY_SECRET_KEY : '';
    }

    protected function allowsDummyKeys(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    /**
     * True when running locally/testing with Cloudflare's official dummy secrets.
     */
    protected function isLocalTestingMode(string $secret): bool
    {
        if (! $this->allowsDummyKeys()) {
            return false;
        }

        return in_array($secret, [
            self::DUMMY_SECRET_KEY,
            self::DUMMY_SECRET_KEY_ALWAYS_FAIL,
        ], true)
            || $this->siteKey() === self::DUMMY_SITE_KEY;
    }

    protected function replayCacheKey(string $token): string
    {
        return 'turnstile:used:'.hash('sha256', $token);
    }
}
