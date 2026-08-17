<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuardDutyFeatureTest extends TestCase
{
    #[Test]
    public function self_registration_guard_duty_routes_are_protected(): void
    {
        $current = Route::getRoutes()->getByName('self-registration.guard-on-duty');
        $assign = Route::getRoutes()->getByName('self-registration.guard-on-duty.assign');
        $change = Route::getRoutes()->getByName('self-registration.guard-on-duty.change');
        $end = Route::getRoutes()->getByName('self-registration.guard-on-duty.end');

        $this->assertNotNull($current);
        $this->assertNotNull($assign);
        $this->assertNotNull($change);
        $this->assertNotNull($end);

        $this->assertContains('auth', $current->gatherMiddleware());
        $this->assertTrue(
            collect($current->gatherMiddleware())->contains(
                fn ($middleware) => str_contains((string) $middleware, 'role:1,4')
            )
        );

        foreach ([$assign, $change, $end] as $route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains('auth', $middleware);
            $this->assertTrue(
                collect($middleware)->contains(fn ($item) => str_contains((string) $item, 'role:4'))
            );
            $this->assertTrue(
                collect($middleware)->contains(fn ($item) => str_contains((string) $item, 'throttle:5,1')),
                'Guard authentication routes must be rate limited.'
            );
        }
    }

    #[Test]
    public function guests_cannot_access_guard_on_duty_endpoints(): void
    {
        $this->getJson(route('self-registration.guard-on-duty'))
            ->assertUnauthorized();

        $this->postJson(route('self-registration.guard-on-duty.assign'), [
            'email' => 'guard@example.com',
            'password' => 'secret',
        ])->assertUnauthorized();

        $this->postJson(route('self-registration.guard-on-duty.change'), [
            'email' => 'guard@example.com',
            'password' => 'secret',
        ])->assertUnauthorized();

        $this->postJson(route('self-registration.guard-on-duty.end'), [
            'password' => 'secret',
        ])->assertUnauthorized();
    }

    #[Test]
    public function admin_guard_duty_routes_are_protected(): void
    {
        foreach ([
            'admin.guard-duty',
            'api.admin.guard-duty',
            'api.admin.guard-duty.filters',
            'api.admin.guard-duty.show',
            'api.admin.guard-duty.visitors',
        ] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, 'Missing route: '.$name);
            $this->assertContains('auth', $route->gatherMiddleware());
            $this->assertTrue(
                collect($route->gatherMiddleware())->contains(
                    fn ($middleware) => str_contains((string) $middleware, 'role:1')
                ),
                'Route '.$name.' is missing admin role middleware.'
            );
        }
    }

    #[Test]
    public function guests_are_redirected_from_admin_guard_duty(): void
    {
        $this->get(route('admin.guard-duty'))
            ->assertRedirect(route('login'));

        $this->get(route('api.admin.guard-duty'))
            ->assertUnauthorized();
    }

    #[Test]
    public function guard_cannot_access_admin_guard_duty(): void
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
            ->get(route('admin.guard-duty'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('api.admin.guard-duty'))
            ->assertForbidden();
    }

    #[Test]
    public function office_staff_cannot_access_admin_guard_duty(): void
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
            ->get(route('admin.guard-duty'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('api.admin.guard-duty'))
            ->assertForbidden();
    }

    #[Test]
    public function authorized_admin_can_open_guard_duty_page(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('guard_duty_shifts')) {
                $this->markTestSkipped('guard_duty_shifts table is not available in the test database.');
            }
        } catch (\Throwable $e) {
            $this->markTestSkipped('Unable to inspect guard_duty_shifts table: '.$e->getMessage());
        }

        $user = new User([
            'user_id' => 1,
            'role_id' => 1,
            'email' => 'admin@example.com',
            'first_name' => 'Admin',
            'last_name' => 'User',
        ]);
        $user->exists = true;

        $this->actingAs($user)
            ->get(route('admin.guard-duty'))
            ->assertOk()
            ->assertSee('Guard Duty Monitoring')
            ->assertSee('Currently On Duty')
            ->assertSee('Duty History');
    }
}
