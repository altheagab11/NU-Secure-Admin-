<?php

namespace Tests\Unit;

use App\Services\CaptchaService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CaptchaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use non-dummy keys so tests exercise the live siteverify HTTP path.
        config([
            'services.turnstile.site_key' => 'live-test-site-key',
            'services.turnstile.secret_key' => 'live-test-secret-key',
        ]);
    }

    #[Test]
    public function it_accepts_a_successful_turnstile_response(): void
    {
        Http::fake([
            CaptchaService::VERIFY_URL => Http::response(['success' => true], 200),
        ]);

        $this->assertTrue(app(CaptchaService::class)->verify('valid-token', '127.0.0.1'));

        Http::assertSent(function ($request) {
            return $request->url() === CaptchaService::VERIFY_URL
                && $request['response'] === 'valid-token'
                && $request['remoteip'] === '127.0.0.1'
                && ! str_contains(json_encode($request->data()) ?: '', 'password');
        });
    }

    #[Test]
    public function it_rejects_an_empty_or_failed_token(): void
    {
        Http::fake([
            CaptchaService::VERIFY_URL => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $captcha = app(CaptchaService::class);

        $this->assertFalse($captcha->verify(''));
        $this->assertFalse($captcha->verify('bad-token'));
        Http::assertSentCount(1);
    }

    #[Test]
    public function it_rejects_replayed_tokens(): void
    {
        Http::fake([
            CaptchaService::VERIFY_URL => Http::response(['success' => true], 200),
        ]);

        $captcha = app(CaptchaService::class);

        $this->assertTrue($captcha->verify('once-only-token'));
        $this->assertFalse($captcha->verify('once-only-token'));
        Http::assertSentCount(1);
    }

    #[Test]
    public function it_fails_closed_when_the_provider_cannot_be_reached(): void
    {
        Http::fake([
            CaptchaService::VERIFY_URL => function () {
                throw new \Illuminate\Http\Client\ConnectionException('timeout');
            },
        ]);

        $this->assertFalse(app(CaptchaService::class)->verify('any-token'));
    }

    #[Test]
    public function it_accepts_cloudflare_dummy_always_pass_secret_without_network(): void
    {
        config([
            'services.turnstile.site_key' => CaptchaService::DUMMY_SITE_KEY,
            'services.turnstile.secret_key' => CaptchaService::DUMMY_SECRET_KEY,
        ]);

        Http::fake();

        $this->assertTrue(app(CaptchaService::class)->verify('dummy-widget-token'));
        Http::assertNothingSent();
    }

    #[Test]
    public function it_never_exposes_the_secret_key_as_the_site_key(): void
    {
        $captcha = app(CaptchaService::class);

        $this->assertNotSame($captcha->siteKey(), config('services.turnstile.secret_key'));
        $this->assertSame(config('services.turnstile.site_key'), $captcha->siteKey());
    }
}
