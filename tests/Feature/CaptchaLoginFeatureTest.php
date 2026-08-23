<?php

namespace Tests\Feature;

use App\Models\LoginAttempt;
use App\Services\CaptchaService;
use App\Services\LoginAttemptService;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CaptchaLoginFeatureTest extends TestCase
{
    #[Test]
    public function login_page_renders_turnstile_without_exposing_the_secret(): void
    {
        $siteKey = app(CaptchaService::class)->siteKey();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('cf-turnstile', false)
            ->assertSee($siteKey)
            ->assertDontSee((string) config('services.turnstile.secret_key'))
            ->assertDontSee('1x0000000000000000000000000000000AA');
    }

    #[Test]
    public function missing_captcha_rejects_web_login_before_authentication(): void
    {
        $this->from(route('login'))
            ->post(route('login.submit'), [
                'email' => 'admin@example.com',
                'password' => 'correct-password-would-not-be-checked',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertGuest();
        $this->assertCaptchaFailureRecorded('admin@example.com', LoginAttemptService::REASON_CAPTCHA_MISSING);
    }

    #[Test]
    public function invalid_captcha_rejects_web_login_before_authentication(): void
    {
        $this->fakeFailedTurnstile();

        $this->from(route('login'))
            ->post(route('login.submit'), $this->withCaptchaToken([
                'email' => 'guard@example.com',
                'password' => 'correct-password-would-not-be-checked',
            ], 'invalid-token'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertGuest();
        $this->assertCaptchaFailureRecorded('guard@example.com', LoginAttemptService::REASON_CAPTCHA_FAILED);
    }

    #[Test]
    public function valid_captcha_and_wrong_password_keeps_generic_error(): void
    {
        if (! $this->usersTableAvailable()) {
            $this->markTestSkipped('users table is not available in the test database.');
        }

        $this->fakeSuccessfulTurnstile();

        $this->from(route('login'))
            ->post(route('login.submit'), $this->withCaptchaToken([
                'email' => 'office@example.com',
                'password' => 'WrongPassword123!',
            ]))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('errors.cf-turnstile-response');

        $this->assertGuest();
    }

    #[Test]
    public function api_login_does_not_require_captcha(): void
    {
        if (! $this->usersTableAvailable()) {
            $this->markTestSkipped('users table is not available in the test database.');
        }

        $this->postJson('/api/login', [
            'email' => 'mobile-user@example.com',
            'password' => 'WrongPassword123!',
            'device_name' => 'NU-Secure Mobile',
        ])->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid email or password.',
            ]);
    }

    #[Test]
    public function captcha_failure_records_do_not_store_passwords_or_tokens(): void
    {
        if (! $this->loginAttemptsTableAvailable()) {
            $this->markTestSkipped('login_attempts table is not available in the test database.');
        }

        $email = 'captcha-audit-'.uniqid().'@example.com';

        $this->from(route('login'))
            ->post(route('login.submit'), [
                'email' => $email,
                'password' => 'SecretPassword123!',
            ]);

        $attempt = LoginAttempt::query()->where('email', $email)->latest('id')->first();
        $this->assertNotNull($attempt);

        $encoded = json_encode($attempt->getAttributes()) ?: '';
        $this->assertStringNotContainsString('SecretPassword123!', $encoded);
        $this->assertStringNotContainsString('cf-turnstile-response', $encoded);
        $this->assertArrayNotHasKey('password', $attempt->getAttributes());
    }

    protected function assertCaptchaFailureRecorded(string $email, string $reason): void
    {
        if (! $this->loginAttemptsTableAvailable()) {
            return;
        }

        $attempt = LoginAttempt::query()->where('email', strtolower($email))->latest('id')->first();
        $this->assertNotNull($attempt);
        $this->assertSame(LoginAttempt::STATUS_FAILED, $attempt->status);
        $this->assertSame($reason, $attempt->failure_reason);
        $this->assertSame(LoginAttempt::SOURCE_WEB, $attempt->login_source);
    }

    protected function usersTableAvailable(): bool
    {
        try {
            return Schema::hasTable('users');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function loginAttemptsTableAvailable(): bool
    {
        try {
            return Schema::hasTable('login_attempts');
        } catch (\Throwable $e) {
            return false;
        }
    }
}