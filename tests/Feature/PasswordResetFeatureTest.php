<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetFeatureTest extends TestCase
{
    #[Test]
    public function password_reset_routes_are_registered_as_public(): void
    {
        foreach ([
            'password.request',
            'password.email',
            'password.reset',
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

}
