<?php

namespace Tests\Unit;

use App\Services\ActivityLogService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityLogServiceTest extends TestCase
{
    #[Test]
    public function it_strips_sensitive_fields_from_payloads(): void
    {
        $sanitized = ActivityLogService::sanitize([
            'first_name' => 'Maria',
            'email' => 'maria@example.com',
            'password' => 'secret-password',
            'password_hash' => '$2y$10$abcdefghijklmnopqrstuv',
            'remember_token' => 'abc123',
            'qr_token' => 'QR-SECRET-TOKEN',
            'qr_payload' => '{"qr_token":"QR-SECRET-TOKEN"}',
            'authorization' => 'Bearer secret',
            'nested' => [
                'status' => 'Active',
                'api_token' => 'tok_123',
                'cvv' => '123',
            ],
        ]);

        $this->assertSame('Maria', $sanitized['first_name']);
        $this->assertSame('maria@example.com', $sanitized['email']);
        $this->assertSame('Active', $sanitized['nested']['status']);
        $this->assertArrayNotHasKey('password', $sanitized);
        $this->assertArrayNotHasKey('password_hash', $sanitized);
        $this->assertArrayNotHasKey('remember_token', $sanitized);
        $this->assertArrayNotHasKey('qr_token', $sanitized);
        $this->assertArrayNotHasKey('qr_payload', $sanitized);
        $this->assertArrayNotHasKey('authorization', $sanitized);
        $this->assertArrayNotHasKey('api_token', $sanitized['nested']);
        $this->assertArrayNotHasKey('cvv', $sanitized['nested']);
    }

    #[Test]
    public function it_maps_roles_and_system_actor_labels(): void
    {
        $this->assertSame('Administrator', ActivityLogService::roleLabel(1));
        $this->assertSame('Guard', ActivityLogService::roleLabel(2));
        $this->assertSame('Office Staff', ActivityLogService::roleLabel(3));
        $this->assertSame('Guard', ActivityLogService::roleLabel(4));
        $this->assertSame('System', ActivityLogService::roleLabel(null));
        $this->assertSame('System', ActivityLogService::actorLabel(null));
    }
}
