<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DailyReportFeatureTest extends TestCase
{
    #[Test]
    public function daily_report_routes_are_registered_under_admin_role_middleware(): void
    {
        $route = Route::getRoutes()->getByName('admin.daily-reports');
        $this->assertNotNull($route);
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertTrue(
            collect($route->gatherMiddleware())->contains(fn ($middleware) => str_contains((string) $middleware, 'role:1'))
        );
    }

    #[Test]
    public function guests_are_redirected_from_daily_reports(): void
    {
        $this->get(route('admin.daily-reports'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function unauthorized_role_cannot_view_daily_reports(): void
    {
        $user = new User([
            'user_id' => 99,
            'role_id' => 2,
            'email' => 'guard@example.com',
            'first_name' => 'Guard',
            'last_name' => 'User',
        ]);
        $user->exists = true;

        $this->actingAs($user)
            ->get(route('admin.daily-reports'))
            ->assertForbidden();
    }

    #[Test]
    public function unauthorized_role_cannot_download_daily_reports(): void
    {
        $user = new User([
            'user_id' => 99,
            'role_id' => 2,
            'email' => 'guard@example.com',
            'first_name' => 'Guard',
            'last_name' => 'User',
        ]);
        $user->exists = true;

        $this->actingAs($user)
            ->get(route('admin.daily-reports.download', ['id' => 1]))
            ->assertForbidden();
    }

    #[Test]
    public function authorized_admin_can_view_daily_reports_page(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('daily_reports')) {
                $this->markTestSkipped('daily_reports table is not available in the test database.');
            }
        } catch (\Throwable $e) {
            $this->markTestSkipped('Test database is not available for this assertion.');
        }

        $admin = new User([
            'user_id' => 1,
            'role_id' => 1,
            'email' => 'admin@example.com',
            'first_name' => 'System',
            'last_name' => 'Admin',
        ]);
        $admin->exists = true;

        $this->actingAs($admin)
            ->get(route('admin.daily-reports'))
            ->assertOk()
            ->assertSee('Daily Reports')
            ->assertSee('Generate Daily Report');
    }

    #[Test]
    public function missing_report_file_returns_controlled_error_for_admin(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('daily_reports')) {
                $this->markTestSkipped('daily_reports table is not available in the test database.');
            }
        } catch (\Throwable $e) {
            $this->markTestSkipped('Test database is not available for this assertion.');
        }

        Storage::fake('local');

        $admin = new User([
            'user_id' => 1,
            'role_id' => 1,
            'email' => 'admin@example.com',
            'first_name' => 'System',
            'last_name' => 'Admin',
        ]);
        $admin->exists = true;

        $uniqueDate = '1999-01-01';

        DailyReport::query()
            ->where('report_date', $uniqueDate)
            ->where('report_type', DailyReport::TYPE_DAILY_VISITOR)
            ->delete();

        $report = DailyReport::query()->create([
            'report_date' => $uniqueDate,
            'report_type' => DailyReport::TYPE_DAILY_VISITOR,
            'file_name' => 'NU-Secure_Visitor_Report_'.$uniqueDate.'.xlsx',
            'file_path' => 'reports/daily/1999/01/missing.xlsx',
            'record_count' => 0,
            'generation_status' => DailyReport::STATUS_COMPLETED,
            'generated_at' => now('Asia/Manila'),
            'generated_by' => 1,
        ]);

        try {
            $this->actingAs($admin)
                ->get(route('admin.daily-reports.download', $report->id))
                ->assertRedirect(route('admin.daily-reports'))
                ->assertSessionHas('error');
        } finally {
            $report->delete();
        }
    }

    #[Test]
    public function scheduled_command_is_registered(): void
    {
        $this->artisan('list')
            ->expectsOutputToContain('generate:daily-visitor-report')
            ->assertSuccessful();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
