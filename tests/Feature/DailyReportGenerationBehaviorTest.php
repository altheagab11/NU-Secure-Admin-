<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use App\Services\DailyVisitorReportService;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class DailyReportGenerationBehaviorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Never migrate, clear, or write to the application's configured database.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        config(['cache.default' => 'array']);
        Carbon::setTestNow(Carbon::parse('2026-10-12 18:00:00', 'Asia/Manila'));
        Storage::fake('local');

        foreach ([
            '2026_08_01_000001_create_daily_reports_table.php',
            '2026_08_01_180000_add_date_range_end_to_daily_reports_table.php',
            '2026_10_01_220000_add_office_id_to_daily_reports_table.php',
            '2026_10_12_000000_enforce_daily_report_scope_uniqueness.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Schema::create('users', function (Blueprint $table) {
            $table->integer('user_id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->integer('role_id');
            $table->string('status')->nullable();
        });
        Schema::create('office', function (Blueprint $table) {
            $table->integer('office_id')->primary();
            $table->string('office_name');
            $table->string('floor')->nullable();
            $table->boolean('is_active')->default(true);
        });
        Schema::create('office_staff', function (Blueprint $table) {
            $table->integer('staff_id')->primary();
            $table->integer('user_id');
            $table->integer('office_id');
            $table->string('position')->nullable();
        });
        Schema::create('alerts', function (Blueprint $table) {
            $table->integer('alert_id')->primary();
            $table->boolean('is_resolved')->default(false);
            $table->string('status')->default('unresolved');
        });
        DB::table('users')->insert([
            ['user_id' => 1, 'first_name' => 'Test', 'last_name' => 'Admin', 'email' => 'admin@example.test', 'role_id' => 1],
            ['user_id' => 7, 'first_name' => 'Test', 'last_name' => 'Admissions', 'email' => 'office@example.test', 'role_id' => 3],
        ]);
        DB::table('office')->insert(['office_id' => 7, 'office_name' => 'Admissions']);
        DB::table('office_staff')->insert(['staff_id' => 7, 'user_id' => 7, 'office_id' => 7]);
        $queries = Mockery::mock(OfficeVisitorQueryService::class);
        $queries->shouldReceive('unreadNotifications')->andReturn(collect());
        $this->app->instance(OfficeVisitorQueryService::class, $queries);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function report(string $type, ?int $officeId = null, string $date = '2026-10-05'): DailyReport
    {
        return DailyReport::create([
            'report_type' => $type, 'office_id' => $officeId, 'report_date' => $date,
            'file_name' => 'existing.xlsx',
            'file_path' => $officeId ? 'reports/daily/office/'.$officeId.'/existing.xlsx' : 'reports/daily/existing.xlsx',
            'generation_status' => 'completed', 'generated_at' => now('Asia/Manila'),
        ]);
    }

    private function forbidGeneration(): void
    {
        $service = Mockery::mock(DailyVisitorReportService::class);
        $service->shouldNotReceive('generate');
        $service->shouldNotReceive('generateForOffice');
        $this->app->instance(DailyVisitorReportService::class, $service);
    }

    public function test_evening_page_load_refresh_filters_and_orphaned_files_do_not_generate_reports(): void
    {
        $this->forbidGeneration();
        Storage::disk('local')->put('reports/daily/orphaned.xlsx', 'old file');
        foreach ([['admin', 1], ['office', 7]] as [$scope, $userId]) {
            $this->actingAs(User::findOrFail($userId));
            foreach (['', '?report_date=2026-10-05', '?date_from=2026-10-05&date_to=2026-10-11', ''] as $query) {
                $this->get(route($scope.'.daily-reports').$query)->assertOk()->assertViewHas('reports', fn ($reports) => $reports->isEmpty());
            }
        }
        $this->assertDatabaseCount('daily_reports', 0);
        Storage::disk('local')->assertExists('reports/daily/orphaned.xlsx');
    }

    public function test_deleted_report_records_do_not_reappear_on_either_page(): void
    {
        $this->report(DailyReport::TYPE_DAILY_VISITOR);
        $this->report(DailyReport::TYPE_DAILY_VISITOR_OFFICE, 7);
        DailyReport::query()->delete(); // Isolated in-memory fixtures only.
        $this->forbidGeneration();
        $this->actingAs(User::findOrFail(1))->get(route('admin.daily-reports'))->assertOk();
        $this->actingAs(User::findOrFail(7))->get(route('office.daily-reports'))->assertOk();
        $this->assertDatabaseCount('daily_reports', 0);
    }

    public function test_lists_and_downloads_keep_scopes_separate(): void
    {
        $admin = $this->report(DailyReport::TYPE_DAILY_VISITOR);
        $admissions = $this->report(DailyReport::TYPE_DAILY_VISITOR_OFFICE, 7);
        $other = $this->report(DailyReport::TYPE_DAILY_VISITOR_OFFICE, 2);
        $this->forbidGeneration();
        $this->actingAs(User::findOrFail(1))->get(route('admin.daily-reports'))
            ->assertOk()->assertViewHas('reports', fn ($reports) => $reports->pluck('id')->all() === [$admin->id]);
        $this->get(route('admin.daily-reports.download', $admissions->id))->assertNotFound();
        $this->actingAs(User::findOrFail(7))->get(route('office.daily-reports'))
            ->assertOk()->assertViewHas('reports', fn ($reports) => $reports->pluck('id')->all() === [$admissions->id]);
        $this->get(route('office.daily-reports.download', $other->id))->assertNotFound();
        $this->get(route('office.daily-reports.download', $admin->id))->assertNotFound();
    }

    public function test_missing_download_files_require_explicit_regeneration(): void
    {
        $this->forbidGeneration();
        foreach ([['admin', 1, DailyReport::TYPE_DAILY_VISITOR, null], ['office', 7, DailyReport::TYPE_DAILY_VISITOR_OFFICE, 7]] as [$scope, $userId, $type, $office]) {
            $report = $this->report($type, $office);
            $this->actingAs(User::findOrFail($userId))->get(route($scope.'.daily-reports.download', $report->id))
                ->assertRedirect(route($scope.'.daily-reports'))->assertSessionHas('error');
            $this->assertTrue($report->fresh()->generated_at->equalTo($report->generated_at));
        }
        $this->assertDatabaseCount('daily_reports', 2);
    }

    public function test_manual_generate_regenerate_and_download_work_for_both_scopes(): void
    {
        $service = Mockery::mock(DailyVisitorReportService::class);
        $this->app->instance(DailyVisitorReportService::class, $service);
        foreach ([['admin', 1, DailyReport::TYPE_DAILY_VISITOR, null], ['office', 7, DailyReport::TYPE_DAILY_VISITOR_OFFICE, 7]] as [$scope, $userId, $type, $office]) {
            $report = $this->report($type, $office);
            foreach ([false, true] as $regenerate) {
                if ($office) {
                    $service->shouldReceive('generateForOffice')->once()->with('2026-10-05', 7, 'Admissions', $userId, $regenerate)->andReturn($report);
                } else {
                    $service->shouldReceive('generate')->once()->with('2026-10-05', $userId, $regenerate)->andReturn($report);
                }
            }
            $this->actingAs(User::findOrFail($userId))->post(route($scope.'.daily-reports.generate'), ['report_date' => '2026-10-05'])
                ->assertRedirect(route($scope.'.daily-reports'))->assertSessionHas('success');
            $this->post(route($scope.'.daily-reports.regenerate', $report->id), ['confirm' => '1'])
                ->assertRedirect(route($scope.'.daily-reports'))->assertSessionHas('success');
            Storage::disk('local')->put($report->file_path, 'existing report');
            $this->get(route($scope.'.daily-reports.download', $report->id))->assertDownload('existing.xlsx');
        }
    }

    public function test_completed_records_are_idempotent_even_when_the_excel_file_is_missing(): void
    {
        $admin = $this->report(DailyReport::TYPE_DAILY_VISITOR);
        $office = $this->report(DailyReport::TYPE_DAILY_VISITOR_OFFICE, 7);
        $service = new DailyVisitorReportService;
        $this->assertSame($admin->id, $service->generate('2026-10-05')->id);
        $this->assertSame($office->id, $service->generateForOffice('2026-10-05', 7, 'Admissions')->id);
        $this->assertDatabaseCount('daily_reports', 2);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_service_locks_block_concurrent_scheduled_and_manual_writers(): void
    {
        foreach ([[DailyReport::TYPE_DAILY_VISITOR, 0], [DailyReport::TYPE_DAILY_VISITOR_OFFICE, 7]] as [$type, $office]) {
            $lock = Cache::lock('daily-report:generation:'.$type.':'.$office.':2026-10-05', 3600);
            $this->assertTrue($lock->get());
            try {
                $service = new DailyVisitorReportService;
                try {
                    $office ? $service->generateForOffice('2026-10-05', 7, 'Admissions', 7, true) : $service->generate('2026-10-05', 1, true);
                    $this->fail('A competing generation must not start.');
                } catch (RuntimeException $e) {
                    $this->assertStringContainsString('already being generated', $e->getMessage());
                }
                $this->assertDatabaseCount('daily_reports', 0);
            } finally {
                $lock->release();
            }
        }
    }

    public function test_database_uniqueness_preserves_distinct_offices_and_repeat_date_range_reports(): void
    {
        $this->report(DailyReport::TYPE_DAILY_VISITOR);
        $this->report(DailyReport::TYPE_DAILY_VISITOR_OFFICE, 7);
        $this->report(DailyReport::TYPE_DAILY_VISITOR_OFFICE, 2);
        $this->report(DailyReport::TYPE_DATE_RANGE);
        $this->report(DailyReport::TYPE_DATE_RANGE);
        $this->assertDatabaseCount('daily_reports', 5);
        foreach ([[DailyReport::TYPE_DAILY_VISITOR, null], [DailyReport::TYPE_DAILY_VISITOR_OFFICE, 7]] as [$type, $office]) {
            try {
                $this->report($type, $office);
                $this->fail('Duplicate daily dates/scopes must be rejected by the database.');
            } catch (QueryException $e) {
                $this->assertStringContainsString('UNIQUE constraint', $e->getMessage());
            }
        }
    }

    public function test_real_midnight_generation_creates_only_yesterday_and_is_idempotent(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-13 00:00:00', 'Asia/Manila'));
        // Keep real storage, spreadsheet writing, report persistence and locking;
        // replace only visitor reads so no production visitor database is involved.
        $service = Mockery::mock(DailyVisitorReportService::class)->makePartial();
        $service->shouldReceive('fetchVisitorRowsForDate')->once()->withArgs(fn ($date) => $date->toDateString() === '2026-10-12')->andReturn(collect());
        $service->shouldReceive('fetchVisitorRowsForOfficeDate')->once()->withArgs(fn ($date, $office) => $date->toDateString() === '2026-10-12' && $office === 7)->andReturn(collect());
        $this->app->instance(DailyVisitorReportService::class, $service);
        for ($run = 0; $run < 2; $run++) {
            $this->artisan('generate:daily-visitor-report')->assertSuccessful();
            $this->artisan('generate:office-daily-visitor-reports')->assertSuccessful();
        }
        $this->assertDatabaseCount('daily_reports', 2);
        $this->assertSame(['2026-10-12'], DailyReport::all()->pluck('report_date')->map->toDateString()->unique()->values()->all());
        $this->assertCount(2, Storage::disk('local')->allFiles('reports/daily'));
        $this->assertSame('NU-Secure_Visitor_Report_2026-10-12.xlsx', DailyReport::where('report_type', DailyReport::TYPE_DAILY_VISITOR)->firstOrFail()->file_name);
    }

    public function test_uniqueness_migration_refuses_to_delete_existing_duplicates(): void
    {
        $migration = require database_path('migrations/2026_10_12_000000_enforce_daily_report_scope_uniqueness.php');
        $migration->down();
        $this->report(DailyReport::TYPE_DAILY_VISITOR);
        $this->report(DailyReport::TYPE_DAILY_VISITOR);
        try {
            $migration->up();
            $this->fail('Existing duplicates must require explicit review.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('no records have been deleted', $e->getMessage());
        }
        $this->assertDatabaseCount('daily_reports', 2);
    }

    public function test_generation_failure_releases_the_service_lock_for_explicit_retry(): void
    {
        $service = Mockery::mock(DailyVisitorReportService::class)->makePartial();
        $service->shouldReceive('fetchVisitorRowsForDate')->once()->andThrow(new RuntimeException('Visitor query failed'));
        try {
            $service->generate('2026-10-05', 1);
            $this->fail('A failed query must be reported.');
        } catch (RuntimeException $e) {
            $this->assertSame('Visitor query failed', $e->getMessage());
        }
        $this->assertDatabaseHas('daily_reports', ['generation_status' => 'failed']);
        $lock = Cache::lock('daily-report:generation:daily_visitor:0:2026-10-05', 3600);
        $this->assertTrue($lock->get());
        $lock->release();
    }
}
