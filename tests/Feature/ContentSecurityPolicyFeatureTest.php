<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContentSecurityPolicyFeatureTest extends TestCase
{
    #[Test]
    public function login_response_sets_nonce_based_script_csp_without_unsafe_inline(): void
    {
        $response = $this->get(route('login'))->assertOk();

        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertNotSame('', $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString('https://challenges.cloudflare.com', $csp);
        $this->assertStringContainsString('https://cdn.jsdelivr.net', $csp);
        $this->assertStringContainsString('https://unpkg.com', $csp);
        $this->assertStringContainsString("frame-src 'self' https://challenges.cloudflare.com", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString('upgrade-insecure-requests', $csp);
        $this->assertStringNotContainsString("'unsafe-inline'", $csp);
        $this->assertStringNotContainsString('script-src *', $csp);
        $this->assertMatchesRegularExpression("/script-src[^;]*'nonce-[A-Za-z0-9+\/=]+'/", $csp);

        preg_match("/'nonce-([^']+)'/", $csp, $matches);
        $this->assertNotEmpty($matches[1] ?? null);

        $response->assertSee('nonce="'.$matches[1].'"', false);
    }

    #[Test]
    public function forgot_password_page_includes_csp_nonce_on_inline_script(): void
    {
        $response = $this->get(route('password.request'))->assertOk();

        $csp = (string) $response->headers->get('Content-Security-Policy');
        preg_match("/'nonce-([^']+)'/", $csp, $matches);
        $this->assertNotEmpty($matches[1] ?? null);

        $response->assertSee('nonce="'.$matches[1].'"', false)
            ->assertSee('Send Verification Code');
    }
}
