<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use App\Services\DailyVisitorReportService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DateRangeReportFeatureTest extends TestCase
{
    #[Test]
    public function date_range_routes_are_registered_under_admin_role_middleware(): void
    {
        foreach ([
            'admin.date-range-reports',
            'admin.date-range-reports.generate',
            'admin.date-range-reports.download',
        ] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, 'Missing route: '.$name);
            $this->assertContains('auth', $route->gatherMiddleware());
            $this->assertTrue(
                collect($route->gatherMiddleware())->contains(fn ($middleware) => str_contains((string) $middleware, 'role:1'))
            );
        }
    }

    #[Test]
    public function guests_are_redirected_from_date_range_reports(): void
    {
        $this->get(route('admin.date-range-reports'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.date-range-reports.generate'), [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-03',
        ])->assertRedirect(route('login'));
    }

    #[Test]
    public function unauthorized_role_cannot_access_date_range_reports(): void
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
            ->get(route('admin.date-range-reports'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.date-range-reports.generate'), [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-03',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.date-range-reports.download', ['id' => 1]))
            ->assertForbidden();
    }

    #[Test]
    public function authorized_admin_can_view_date_range_report_form(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('daily_reports')) {
                $this->markTestSkipped('daily_reports table is not available in the test database.');
            }
        } catch (\Throwable $e) {
            $this->markTestSkipped('Test database is not available for this assertion.');
        }

        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.date-range-reports'))
            ->assertOk()
            ->assertSee('Generate Date-Range Report')
            ->assertSee('Start Date')
            ->assertSee('End Date')
            ->assertSee('Generate and Download');
    }

    #[Test]
    public function validation_rejects_invalid_date_range_requests(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->from(route('admin.date-range-reports'))
            ->post(route('admin.date-range-reports.generate'), [])
            ->assertRedirect(route('admin.date-range-reports'))
            ->assertSessionHasErrors(['start_date', 'end_date']);

        $this->actingAs($admin)
            ->from(route('admin.date-range-reports'))
            ->post(route('admin.date-range-reports.generate'), [
                'start_date' => '2026-07-03',
                'end_date' => '2026-07-01',
            ])
            ->assertRedirect(route('admin.date-range-reports'))
            ->assertSessionHasErrors(['end_date']);

        $this->actingAs($admin)
            ->from(route('admin.date-range-reports'))
            ->post(route('admin.date-range-reports.generate'), [
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-01',
            ])
            ->assertRedirect(route('admin.date-range-reports'))
            ->assertSessionHasErrors(['end_date']);

        $future = now('Asia/Manila')->addDay()->toDateString();
        $today = now('Asia/Manila')->toDateString();

        $this->actingAs($admin)
            ->from(route('admin.date-range-reports'))
            ->post(route('admin.date-range-reports.generate'), [
                'start_date' => $today,
                'end_date' => $future,
            ])
            ->assertRedirect(route('admin.date-range-reports'))
            ->assertSessionHasErrors(['end_date']);

        config(['reports.max_range_days' => 31]);

        $this->actingAs($admin)
            ->from(route('admin.date-range-reports'))
            ->post(route('admin.date-range-reports.generate'), [
                'start_date' => '2026-01-01',
                'end_date' => '2026-02-15',
            ])
            ->assertRedirect(route('admin.date-range-reports'))
            ->assertSessionHasErrors(['end_date']);
    }

    #[Test]
    public function admin_can_generate_and_download_two_day_report(): void
    {
        Storage::fake('local');
        $admin = $this->makeAdmin();

        $mockReport = new DailyReport([
            'report_date' => '2026-07-01',
            'date_range_end' => '2026-07-02',
            'report_type' => DailyReport::TYPE_DATE_RANGE,
            'file_name' => 'NU-Secure_Visitor_Report_2026-07-01_to_2026-07-02.xlsx',
            'file_path' => 'reports/date-range/2026/07/NU-Secure_Visitor_Report_2026-07-01_to_2026-07-02.xlsx',
            'record_count' => 0,
            'generation_status' => DailyReport::STATUS_COMPLETED,
            'generated_at' => now('Asia/Manila'),
            'generated_by' => 1,
        ]);
        $mockReport->id = 9001;
        $mockReport->exists = true;

        Storage::disk('local')->put($mockReport->file_path, 'fake-xlsx-content');

        $this->mock(DailyVisitorReportService::class, function ($mock) use ($mockReport) {
            $mock->shouldReceive('generateDateRangeReport')
                ->once()
                ->withArgs(function ($start, $end, $user) {
                    return $start === '2026-07-01'
                        && $end === '2026-07-02'
                        && (int) $user->role_id === 1;
                })
                ->andReturn($mockReport);
        });

        $response = $this->actingAs($admin)
            ->post(route('admin.date-range-reports.generate'), [
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-02',
            ]);

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString(
            'NU-Secure_Visitor_Report_2026-07-01_to_2026-07-02.xlsx',
            (string) $response->headers->get('content-disposition')
        );
    }

    #[Test]
    public function admin_can_generate_report_covering_more_than_two_days(): void
    {
        Storage::fake('local');
        $admin = $this->makeAdmin();

        $mockReport = new DailyReport([
            'report_date' => '2026-07-01',
            'date_range_end' => '2026-07-07',
            'report_type' => DailyReport::TYPE_DATE_RANGE,
            'file_name' => 'NU-Secure_Visitor_Report_2026-07-01_to_2026-07-07.xlsx',
            'file_path' => 'reports/date-range/2026/07/NU-Secure_Visitor_Report_2026-07-01_to_2026-07-07.xlsx',
            'record_count' => 12,
            'generation_status' => DailyReport::STATUS_COMPLETED,
            'generated_at' => now('Asia/Manila'),
            'generated_by' => 1,
        ]);
        $mockReport->id = 9002;
        $mockReport->exists = true;

        Storage::disk('local')->put($mockReport->file_path, 'fake-xlsx-content');

        $this->mock(DailyVisitorReportService::class, function ($mock) use ($mockReport) {
            $mock->shouldReceive('generateDateRangeReport')
                ->once()
                ->with('2026-07-01', '2026-07-07', Mockery::type(User::class))
                ->andReturn($mockReport);
        });

        $this->actingAs($admin)
            ->post(route('admin.date-range-reports.generate'), [
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-07',
            ])
            ->assertOk();
    }

    #[Test]
    public function missing_date_range_file_returns_controlled_error(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('daily_reports')) {
                $this->markTestSkipped('daily_reports table is not available in the test database.');
            }
        } catch (\Throwable $e) {
            $this->markTestSkipped('Test database is not available for this assertion.');
        }

        Storage::fake('local');
        $admin = $this->makeAdmin();

        $report = DailyReport::query()->create([
            'report_date' => '1998-01-01',
            'date_range_end' => '1998-01-03',
            'report_type' => DailyReport::TYPE_DATE_RANGE,
            'file_name' => 'NU-Secure_Visitor_Report_1998-01-01_to_1998-01-03.xlsx',
            'file_path' => 'reports/date-range/1998/01/missing.xlsx',
            'record_count' => 0,
            'generation_status' => DailyReport::STATUS_COMPLETED,
            'generated_at' => now('Asia/Manila'),
            'generated_by' => 1,
        ]);

        try {
            $this->actingAs($admin)
                ->get(route('admin.date-range-reports.download', $report->id))
                ->assertRedirect(route('admin.date-range-reports'))
                ->assertSessionHas('error');
        } finally {
            $report->delete();
        }
    }

    #[Test]
    public function daily_report_routes_remain_registered_and_unaffected(): void
    {
        $route = Route::getRoutes()->getByName('admin.daily-reports');
        $this->assertNotNull($route);
        $this->assertContains('auth', $route->gatherMiddleware());

        $this->artisan('list')
            ->expectsOutputToContain('generate:daily-visitor-report')
            ->assertSuccessful();
    }

    #[Test]
    public function successful_generation_writes_audit_log_action(): void
    {
        Log::spy();
        Storage::fake('local');

        $path = 'reports/date-range/2026/07/NU-Secure_Visitor_Report_2026-07-10_to_2026-07-11.xlsx';
        Storage::disk('local')->put($path, 'xlsx');

        $report = new DailyReport([
            'report_date' => '2026-07-10',
            'date_range_end' => '2026-07-11',
            'report_type' => DailyReport::TYPE_DATE_RANGE,
            'file_name' => 'NU-Secure_Visitor_Report_2026-07-10_to_2026-07-11.xlsx',
            'file_path' => $path,
            'record_count' => 3,
            'generation_status' => DailyReport::STATUS_COMPLETED,
            'generated_at' => now('Asia/Manila'),
            'generated_by' => 1,
        ]);
        $report->id = 9010;
        $report->exists = true;

        $this->mock(DailyVisitorReportService::class, function ($mock) use ($report) {
            $mock->shouldReceive('generateDateRangeReport')->once()->andReturn($report);
        });

        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.date-range-reports.generate'), [
                'start_date' => '2026-07-10',
                'end_date' => '2026-07-11',
            ])
            ->assertOk();

        Log::shouldHaveReceived('info')->withArgs(function ($message, $context = null) {
            if (! is_array($context)) {
                return false;
            }

            return ($context['action'] ?? null) === 'date_range_report_generated_and_downloaded';
        })->atLeast()->once();
    }

    protected function makeAdmin(): User
    {
        $admin = new User([
            'user_id' => 1,
            'role_id' => 1,
            'email' => 'admin@example.com',
            'first_name' => 'System',
            'last_name' => 'Admin',
        ]);
        $admin->exists = true;

        return $admin;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
