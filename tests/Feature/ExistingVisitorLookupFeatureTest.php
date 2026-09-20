<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExistingVisitorLookupFeatureTest extends TestCase
{
    #[Test]
    public function lookup_existing_visitor_route_is_registered_and_protected(): void
    {
        $route = collect(Route::getRoutes())->first(
            fn ($item) => in_array('POST', $item->methods(), true)
                && $item->uri() === 'guard/lookup-existing-visitor'
        );

        $this->assertNotNull($route);
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertTrue(
            collect($route->gatherMiddleware())->contains(
                fn ($middleware) => str_contains((string) $middleware, 'role:2,4')
            )
        );
    }

    #[Test]
    public function guests_cannot_lookup_existing_visitors(): void
    {
        $this->postJson('/guard/lookup-existing-visitor', [
            'first_name' => 'Jocelyn',
            'last_name' => 'Hernandez',
            'birthday' => '1963-10-23',
        ])->assertUnauthorized();
    }
}
