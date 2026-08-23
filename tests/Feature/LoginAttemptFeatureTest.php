<?php

namespace Tests\Feature;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\LoginAttemptService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginAttemptFeatureTest extends TestCase
{
    #[Test]
    public function login_attempt_routes_are_registered_under_admin_role_middleware(): void
    {
        foreach ([
            'admin.login-attempts',
            'admin.login-attempts.summary',
            'api.admin.login-attempts',
            'api.admin.login-attempts.summary',
        ] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, 'Missing route: '.$name);
            $this->assertContains('auth', $route->gatherMiddleware());
            $this->assertTrue(
                collect($route->gatherMiddleware())->contains(fn ($middleware) => str_contains((string) $middleware, 'role:1')),
                'Route '.$name.' is missing admin role middleware.'
            );
        }
    }

    #[Test]
    public function guests_are_redirected_from_login_attempts(): void
    {
        $this->get(route('admin.login-attempts'))
            ->assertRedirect(route('login'));

        $this->get(route('api.admin.login-attempts'))
            ->assertUnauthorized();
    }

    #[Test]
    public function guard_cannot_access_login_attempts(): void
    {
        $user = new User([
            'user_id' => 99,
            'role_id' => 2,
            'email' => 'guard@example.com',
            'first_name' => 'Guard',
            'last_name' => 'Juan',
        ]);
        $user->exists = true;

        $this->actingAs($user)
            ->get(route('admin.login-attempts'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('api.admin.login-attempts'))
            ->assertForbidden();
    }

    #[Test]
    public function office_staff_cannot_access_login_attempts(): void
    {
        $user = new User([
            'user_id' => 88,
            'role_id' => 3,
            'email' => 'office@example.com',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);
        $user->exists = true;

        $this->actingAs($user)
            ->get(route('admin.login-attempts'))
            ->assertForbidden();
    }

    #[Test]
    public function authorized_admin_can_view_login_attempts_page(): void
    {
        if (! $this->loginAttemptsTableAvailable()) {
            $this->markTestSkipped('login_attempts table is not available in the test database.');
        }

        $admin = new User([
            'user_id' => 1,
            'role_id' => 1,
            'email' => 'admin@example.com',
            'first_name' => 'Admin',
            'last_name' => 'Demo',
        ]);
        $admin->exists = true;

        $this->actingAs($admin)
            ->get(route('admin.login-attempts'))
            ->assertOk()
            ->assertSee('Login Attempts')
            ->assertSee('Administrator')
            ->assertSee('Guard')
            ->assertSee('Office Staff')
            ->assertDontSee('Edit Attempt')
            ->assertDontSee('Delete Attempt')
            ->assertDontSee('Clear Attempts');
    }

    #[Test]
    public function failed_web_login_is_recorded_without_storing_the_password(): void
    {
        if (! $this->loginAttemptsTableAvailable()) {
            $this->markTestSkipped('login_attempts table is not available in the test database.');
        }

        $this->fakeSuccessfulTurnstile();

        $email = 'missing-login-attempt-'.uniqid().'@example.com';

        $this->from(route('login'))
            ->post(route('login.submit'), $this->withCaptchaToken([
                'email' => $email,
                'password' => 'WrongPassword123!',
            ]))
            ->assertSessionHasErrors('email');

        $attempt = LoginAttempt::query()->where('email', $email)->latest('id')->first();
        $this->assertNotNull($attempt);
        $this->assertSame(LoginAttempt::STATUS_FAILED, $attempt->status);
        $this->assertSame(LoginAttemptService::REASON_ACCOUNT_NOT_FOUND, $attempt->failure_reason);
        $this->assertSame(LoginAttempt::SOURCE_WEB, $attempt->login_source);
        $this->assertNull($attempt->user_id);

        $attributes = $attempt->getAttributes();
        $this->assertArrayNotHasKey('password', $attributes);
        $this->assertArrayNotHasKey('password_hash', $attributes);
        $this->assertStringNotContainsString('WrongPassword123!', json_encode($attributes) ?: '');
    }

    #[Test]
    public function failed_api_login_is_recorded_as_api_or_mobile_source(): void
    {
        if (! $this->loginAttemptsTableAvailable()) {
            $this->markTestSkipped('login_attempts table is not available in the test database.');
        }

        $email = 'missing-api-login-'.uniqid().'@example.com';

        $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'WrongPassword123!',
            'device_name' => 'NU-Secure Mobile',
        ])->assertStatus(422);

        $attempt = LoginAttempt::query()->where('email', $email)->latest('id')->first();
        $this->assertNotNull($attempt);
        $this->assertSame(LoginAttempt::STATUS_FAILED, $attempt->status);
        $this->assertContains($attempt->login_source, [
            LoginAttempt::SOURCE_API,
            LoginAttempt::SOURCE_MOBILE,
        ]);
        $this->assertStringNotContainsString('WrongPassword123!', json_encode($attempt->getAttributes()) ?: '');
    }

    #[Test]
    public function rate_limiting_blocks_further_attempts_and_records_blocked_status(): void
    {
        if (! $this->loginAttemptsTableAvailable()) {
            $this->markTestSkipped('login_attempts table is not available in the test database.');
        }

        $this->fakeSuccessfulTurnstile();

        $email = 'rate-limit-'.uniqid().'@example.com';
        $request = $this->from(route('login'));

        for ($i = 0; $i < LoginAttemptService::MAX_ATTEMPTS; $i++) {
            $request->post(route('login.submit'), $this->withCaptchaToken([
                'email' => $email,
                'password' => 'WrongPassword123!',
            ]));
        }

        $this->from(route('login'))
            ->post(route('login.submit'), $this->withCaptchaToken([
                'email' => $email,
                'password' => 'WrongPassword123!',
            ]))
            ->assertSessionHasErrors('email');

        $blocked = LoginAttempt::query()
            ->where('email', $email)
            ->where('status', LoginAttempt::STATUS_BLOCKED)
            ->exists();

        $this->assertTrue($blocked);
        RateLimiter::clear(LoginAttemptService::throttleKey(request(), $email));
    }

    #[Test]
    public function admin_can_filter_and_paginate_login_attempts(): void
    {
        if (! $this->loginAttemptsTableAvailable()) {
            $this->markTestSkipped('login_attempts table is not available in the test database.');
        }

        $admin = new User([
            'user_id' => 1,
            'role_id' => 1,
            'email' => 'admin@example.com',
            'first_name' => 'Admin',
            'last_name' => 'Demo',
        ]);
        $admin->exists = true;

        $marker = 'filter-ip-'.uniqid();

        LoginAttempt::query()->create([
            'email' => 'guard-filter@example.com',
            'role' => LoginAttempt::ROLE_GUARD,
            'status' => LoginAttempt::STATUS_FAILED,
            'failure_reason' => LoginAttemptService::REASON_INCORRECT_PASSWORD,
            'ip_address' => $marker,
            'device_type' => 'Desktop',
            'login_source' => LoginAttempt::SOURCE_WEB,
            'attempted_at' => now('Asia/Manila'),
        ]);

        LoginAttempt::query()->create([
            'email' => 'office-filter@example.com',
            'role' => LoginAttempt::ROLE_OFFICE,
            'status' => LoginAttempt::STATUS_SUCCESS,
            'ip_address' => '10.0.0.9',
            'device_type' => 'Mobile',
            'login_source' => LoginAttempt::SOURCE_API,
            'attempted_at' => now('Asia/Manila'),
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('api.admin.login-attempts', [
                'role' => 'guard',
                'status' => 'failed',
                'search' => $marker,
                'per_page' => 10,
                'page' => 1,
            ]));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $rows = $response->json('data');
        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertSame('Guard', $row['role']);
            $this->assertSame('Failed', $row['status']);
            $this->assertSame($marker, $row['ip_address']);
        }

        $this->assertLessThanOrEqual(10, count($rows));
        $this->assertArrayHasKey('current_page', $response->json('meta'));
        $this->assertArrayHasKey('per_page', $response->json('meta'));
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
