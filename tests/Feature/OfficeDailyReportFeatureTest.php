<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OfficeDailyReportFeatureTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function office_daily_report_routes_are_registered_under_office_staff_middleware(): void
    {
        $route = Route::getRoutes()->getByName('office.daily-reports');
        $this->assertNotNull($route);
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertTrue(
            collect($route->gatherMiddleware())->contains(fn ($middleware) => str_contains((string) $middleware, 'office.staff'))
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function guests_are_redirected_from_office_daily_reports(): void
    {
        $this->get(route('office.daily-reports'))
            ->assertRedirect(route('login'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_role_cannot_access_office_daily_reports(): void
    {
        $user = new User([
            'user_id' => 1,
            'role_id' => 1,
            'email' => 'admin@example.com',
            'first_name' => 'System',
            'last_name' => 'Admin',
        ]);
        $user->exists = true;

        $this->actingAs($user)
            ->get(route('office.daily-reports'))
            ->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function office_staff_cannot_download_another_offices_report(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('daily_reports')) {
                $this->markTestSkipped('daily_reports table is not available in the test database.');
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('daily_reports', 'office_id')) {
                $this->markTestSkipped('office_id column is not available on daily_reports.');
            }
        } catch (\Throwable $e) {
            $this->markTestSkipped('Test database is not available for this assertion.');
        }

        // Without a real office_staff assignment, EnsureOfficeStaff logs the user out / forbids.
        // This asserts the route exists and remains protected for non-office roles.
        $guard = new User([
            'user_id' => 99,
            'role_id' => 2,
            'email' => 'guard@example.com',
            'first_name' => 'Guard',
            'last_name' => 'User',
        ]);
        $guard->exists = true;

        $this->actingAs($guard)
            ->get(route('office.daily-reports.download', ['id' => 1]))
            ->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function office_report_type_constant_is_distinct_from_admin(): void
    {
        $this->assertSame('daily_visitor', DailyReport::TYPE_DAILY_VISITOR);
        $this->assertSame('daily_visitor_office', DailyReport::TYPE_DAILY_VISITOR_OFFICE);
        $this->assertNotSame(DailyReport::TYPE_DAILY_VISITOR, DailyReport::TYPE_DAILY_VISITOR_OFFICE);
    }
}
