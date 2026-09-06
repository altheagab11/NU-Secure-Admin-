<?php

namespace Tests;

use App\Services\CaptchaService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function fakeSuccessfulTurnstile(): void
    {
        // Avoid Cloudflare dummy secrets so verification uses the HTTP siteverify path.
        config([
            'services.turnstile.site_key' => 'live-test-site-key',
            'services.turnstile.secret_key' => 'live-test-secret-key',
        ]);

        Http::fake([
            CaptchaService::VERIFY_URL => Http::response(['success' => true], 200),
        ]);
    }

    protected function fakeFailedTurnstile(): void
    {
        config([
            'services.turnstile.site_key' => 'live-test-site-key',
            'services.turnstile.secret_key' => 'live-test-secret-key',
        ]);

        Http::fake([
            CaptchaService::VERIFY_URL => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function withCaptchaToken(array $payload, ?string $token = null): array
    {
        $payload['cf-turnstile-response'] = $token ?? 'test-turnstile-token-'.uniqid();

        return $payload;
    }
}