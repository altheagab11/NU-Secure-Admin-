<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityLogFeatureTest extends TestCase
{
    #[Test]
    public function activity_log_routes_are_registered_under_admin_role_middleware(): void
    {
        foreach ([
            'admin.activity-logs',
            'admin.activity-logs.summary',
            'admin.activity-logs.filters',
            'admin.activity-logs.show',
            'api.admin.activity-logs',
            'api.admin.activity-logs.summary',
            'api.admin.activity-logs.filters',
            'api.admin.activity-logs.show',
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
    public function guests_are_redirected_from_activity_logs(): void
    {
        $this->get(route('admin.activity-logs'))
            ->assertRedirect(route('login'));

        $this->get(route('api.admin.activity-logs'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function guard_cannot_access_activity_logs(): void
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
            ->get(route('admin.activity-logs'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('api.admin.activity-logs'))
            ->assertForbidden();
    }

    #[Test]
    public function office_staff_cannot_access_activity_logs(): void
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
            ->get(route('admin.activity-logs'))
            ->assertForbidden();
    }

    #[Test]
    public function authorized_admin_can_view_activity_logs_page(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                $this->markTestSkipped('activity_logs table is not available in the test database.');
            }
        } catch (\Throwable $e) {
            $this->markTestSkipped('Test database is not available for this assertion.');
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
            ->get(route('admin.activity-logs'))
            ->assertOk()
            ->assertSee('Activity Logs')
            ->assertSee('Monitor and review system activities performed by users.')
            ->assertDontSee('Edit Log')
            ->assertDontSee('Delete Log')
            ->assertDontSee('Clear Logs');
    }
}
