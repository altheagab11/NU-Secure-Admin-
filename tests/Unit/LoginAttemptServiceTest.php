<?php

namespace Tests\Unit;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\LoginAttemptService;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginAttemptServiceTest extends TestCase
{
    #[Test]
    public function it_maps_role_ids_to_project_role_slugs(): void
    {
        $admin = new User(['role_id' => 1]);
        $guard = new User(['role_id' => 2]);
        $office = new User(['role_id' => 3]);
        $selfGuard = new User(['role_id' => 4]);

        $this->assertSame(LoginAttempt::ROLE_ADMIN, LoginAttemptService::roleSlug($admin));
        $this->assertSame(LoginAttempt::ROLE_GUARD, LoginAttemptService::roleSlug($guard));
        $this->assertSame(LoginAttempt::ROLE_OFFICE, LoginAttemptService::roleSlug($office));
        $this->assertSame(LoginAttempt::ROLE_GUARD, LoginAttemptService::roleSlug($selfGuard));
        $this->assertNull(LoginAttemptService::roleSlug(null));
    }

    #[Test]
    public function it_uses_existing_role_labels(): void
    {
        $this->assertSame('Administrator', LoginAttemptService::roleLabel('admin'));
        $this->assertSame('Guard', LoginAttemptService::roleLabel('guard'));
        $this->assertSame('Office Staff', LoginAttemptService::roleLabel('office'));
    }

    #[Test]
    public function it_detects_device_types_without_third_party_packages(): void
    {
        $this->assertSame('Desktop', LoginAttemptService::detectDeviceType('Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0'));
        $this->assertSame('Mobile', LoginAttemptService::detectDeviceType('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)'));
        $this->assertSame('Tablet', LoginAttemptService::detectDeviceType('Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X)'));
        $this->assertSame('Unknown', LoginAttemptService::detectDeviceType(null));
    }

    #[Test]
    public function it_detects_web_api_and_mobile_login_sources(): void
    {
        $web = Request::create('/login', 'POST');
        $this->assertSame(LoginAttempt::SOURCE_WEB, LoginAttemptService::detectLoginSource($web));

        $api = Request::create('/api/login', 'POST', server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_USER_AGENT' => 'NU-Secure-Postman',
        ]);
        $this->assertSame(LoginAttempt::SOURCE_API, LoginAttemptService::detectLoginSource($api));

        $mobile = Request::create('/api/login', 'POST', [
            'device_name' => 'Pixel 8',
        ], server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_USER_AGENT' => 'okhttp/4.12.0',
        ]);
        $this->assertSame(LoginAttempt::SOURCE_MOBILE, LoginAttemptService::detectLoginSource($mobile));
    }

    #[Test]
    public function it_never_accepts_password_or_token_fields_on_the_model(): void
    {
        $fillable = (new LoginAttempt)->getFillable();

        foreach (['password', 'password_hash', 'token', 'access_token', 'remember_token'] as $field) {
            $this->assertNotContains($field, $fillable);
        }
    }
}
