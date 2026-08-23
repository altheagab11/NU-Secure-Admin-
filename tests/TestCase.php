<?php

namespace Tests;

use App\Services\CaptchaService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function fakeSuccessfulTurnstile(): void
    {
        Http::fake([
            CaptchaService::VERIFY_URL => Http::response(['success' => true], 200),
        ]);
    }

    protected function fakeFailedTurnstile(): void
    {
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