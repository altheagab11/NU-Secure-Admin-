<?php

namespace Tests\Feature;

use App\Mail\PasswordChangedMail;
use App\Mail\PasswordResetVerificationCodeMail;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Cache::flush();
    }

    #[Test]
    public function password_reset_routes_are_registered_as_public(): void
    {
        foreach ([
            'password.request',
            'password.email',
            'password.verify',
            'password.resend',
            'password.update',
            'password.reset.success',
        ] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, 'Missing route: '.$name);
            $middleware = $route->gatherMiddleware();
            $this->assertFalse(
                collect($middleware)->contains(fn ($item) => str_contains((string) $item, 'auth')),
                'Route '.$name.' should be reachable without authentication.'
            );
        }
    }

    #[Test]
    public function forgot_password_page_is_available(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Forgot Password')
            ->assertSee('Send Verification Code')
            ->assertSee('Back to Sign In');
    }

    #[Test]
    public function forgot_password_requires_a_valid_email(): void
    {
        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => ''])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'hello123'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function forgot_password_returns_generic_message_without_account_enumeration(): void
    {
        if (! $this->passwordResetTablesAvailable()) {
            $this->markTestSkipped('Password reset tables are not available in the test database.');
        }

        $email = 'missing-reset-'.uniqid().'@example.com';

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', PasswordResetService::GENERIC_REQUEST_MESSAGE);

        Mail::assertNothingSent();
    }

    #[Test]
    public function valid_registered_email_receives_verification_code_email_without_reset_link(): void
    {
        if (! $this->passwordResetTablesAvailable()) {
            $this->markTestSkipped('Password reset tables are not available in the test database.');
        }

        $user = $this->createResettableUser();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status');

        Mail::assertSent(PasswordResetVerificationCodeMail::class, function (PasswordResetVerificationCodeMail $mail) use ($user) {
            return $mail->hasTo($user->email)
                && preg_match('/^\d{6}$/', $mail->verificationCode) === 1;
        });

        $tokenRow = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($tokenRow);
        $this->assertFalse(Hash::check('000000', (string) $tokenRow->token));
    }

    #[Test]
    public function correct_verification_code_advances_to_password_step(): void
    {
        if (! $this->passwordResetTablesAvailable()) {
            $this->markTestSkipped('Password reset tables are not available in the test database.');
        }

        $user = $this->createResettableUser();
        $code = $this->requestVerificationCode($user->email);

        $this->post(route('password.verify'), ['code' => $code])
            ->assertRedirect(route('password.request'));

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Create New Password');
    }

    #[Test]
    public function wrong_verification_code_is_rejected(): void
    {
        if (! $this->passwordResetTablesAvailable()) {
            $this->markTestSkipped('Password reset tables are not available in the test database.');
        }

        $user = $this->createResettableUser();
        $this->requestVerificationCode($user->email);

        $this->post(route('password.verify'), ['code' => '000000'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('error');
    }

    #[Test]
    public function five_wrong_codes_invalidate_the_current_verification_code(): void
    {
        if (! $this->passwordResetTablesAvailable()) {
            $this->markTestSkipped('Password reset tables are not available in the test database.');
        }

        $user = $this->createResettableUser();
        $code = $this->requestVerificationCode($user->email);

        for ($i = 0; $i < PasswordResetService::MAX_VERIFY_ATTEMPTS; $i++) {
            $this->post(route('password.verify'), ['code' => '000000']);
        }

        $this->post(route('password.verify'), ['code' => $code])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('error');

        $this->assertNull(DB::table('password_reset_tokens')->where('email', $user->email)->first());
    }

    #[Test]
    public function expired_verification_code_is_rejected(): void
    {
        if (! $this->passwordResetTablesAvailable()) {
            $this->markTestSkipped('Password reset tables are not available in the test database.');
        }

        $user = $this->createResettableUser();
        $code = $this->requestVerificationCode($user->email);

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now('Asia/Manila')->subMinutes(11)]);

        $this->post(route('password.verify'), ['code' => $code])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('error');
    }

    #[Test]
    public function resend_before_cooldown_is_rejected(): void
    {
        if (! $this->passwordResetTablesAvailable()) {
            $this->markTestSkipped('Password reset tables are not available in the test database.');
        }

        $user = $this->createResettableUser();
        $this->requestVerificationCode($user->email);

        Mail::fake();

        $this->post(route('password.resend'))
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('error');

        Mail::assertNothingSent();
    }

    #[Test]
    public function direct_password_reset_without_verified_code_is_rejected(): void
    {
        if (! $this->passwordResetTablesAvailable()) {
            $this->markTestSkipped('Password reset tables are not available in the test database.');
        }

        $user = $this->createResettableUser();
        $this->requestVerificationCode($user->email);

        $this->post(route('password.update'), [
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertRedirect(route('password.request'))
            ->assertSessionHas('error');
    }

    #[Test]
    public function successful_reset_changes_password_and_sends_confirmation_email(): void
    {
        if (! $this->passwordResetTablesAvailable()) {
            $this->markTestSkipped('Password reset tables are not available in the test database.');
        }

        $user = $this->createResettableUser('OldPassword1');
        $code = $this->requestVerificationCode($user->email);

        $this->post(route('password.verify'), ['code' => $code])
            ->assertRedirect(route('password.request'));

        Mail::fake();

        $this->post(route('password.update'), [
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertRedirect(route('password.reset.success'));

        Mail::assertSent(PasswordChangedMail::class, fn (PasswordChangedMail $mail) => $mail->hasTo($user->email));

        $fresh = User::findByEmail($user->email);
        $this->assertTrue(Hash::check('NewPassword1', (string) $fresh?->password_hash));
        $this->assertFalse(Hash::check('OldPassword1', (string) $fresh?->password_hash));
        $this->assertNull(DB::table('password_reset_tokens')->where('email', $user->email)->first());
    }

    #[Test]
    public function api_forgot_password_does_not_return_verification_code(): void
    {
        if (! $this->passwordResetTablesAvailable()) {
            $this->markTestSkipped('Password reset tables are not available in the test database.');
        }

        $user = $this->createResettableUser();

        $response = $this->postJson('/api/forgot-password', ['email' => $user->email])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertArrayNotHasKey('code', $response->json());
        Mail::assertSent(PasswordResetVerificationCodeMail::class);
    }

    #[Test]
    public function api_verify_and_reset_flow_works_with_reset_token(): void
    {
        if (! $this->passwordResetTablesAvailable()) {
            $this->markTestSkipped('Password reset tables are not available in the test database.');
        }

        $user = $this->createResettableUser('OldPassword1');
        $verificationCode = $this->requestVerificationCode($user->email);

        $verifyResponse = $this->postJson('/api/forgot-password/verify-code', [
            'email' => $user->email,
            'code' => $verificationCode,
        ])->assertOk()
            ->assertJsonPath('success', true);

        $resetToken = $verifyResponse->json('reset_token');
        $this->assertNotEmpty($resetToken);

        $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'reset_token' => $resetToken,
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertOk()
            ->assertJsonPath('success', true);

        $fresh = User::findByEmail($user->email);
        $this->assertTrue(Hash::check('NewPassword1', (string) $fresh?->password_hash));
    }

    #[Test]
    public function reset_password_route_redirects_to_forgot_password_page(): void
    {
        $this->get(route('password.reset'))
            ->assertRedirect(route('password.request'));
    }

    protected function passwordResetTablesAvailable(): bool
    {
        try {
            return Schema::hasTable('users') && Schema::hasTable('password_reset_tokens');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function createResettableUser(string $password = 'OldPassword1'): User
    {
        $email = 'reset-user-'.uniqid().'@example.com';
        $hash = Hash::make($password);

        $payload = [
            'email' => $email,
            'password_hash' => $hash,
            'first_name' => 'Reset',
            'last_name' => 'Tester',
            'role_id' => 2,
            'status' => 'active',
        ];

        if (Schema::hasColumn('users', 'password')) {
            $payload['password'] = $hash;
        }

        if (Schema::hasColumn('users', 'name')) {
            $payload['name'] = 'Reset Tester';
        }

        $userId = DB::table('users')->insertGetId($payload, 'user_id');

        return User::query()->where('user_id', $userId)->firstOrFail();
    }

    protected function requestVerificationCode(string $email): string
    {
        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $email])
            ->assertRedirect(route('password.request'));

        $code = null;

        Mail::assertSent(PasswordResetVerificationCodeMail::class, function (PasswordResetVerificationCodeMail $mail) use (&$code) {
            $code = $mail->verificationCode;

            return true;
        });

        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $code);

        return (string) $code;
    }
}
