<?php

namespace Tests\Feature;

use App\Services\DailyVisitorReportService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class OfficeDailyReportSchedulerTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function fakeOffices(): void
    {
        $query = Mockery::mock();
        DB::shouldReceive('table')->once()->with('office')->andReturn($query);
        $query->shouldReceive('select')->once()->with('office_id', 'office_name')->andReturnSelf();
        $query->shouldReceive('where')->once()->with('is_active', true)->andReturnSelf();
        $query->shouldReceive('orderBy')->once()->with('office_id')->andReturnSelf();
        $query->shouldReceive('get')->once()->andReturn(collect([
            (object) ['office_id' => 2, 'office_name' => 'Registrar'],
            (object) ['office_id' => 7, 'office_name' => 'Admissions'],
        ]));
    }

    public function test_midnight_generation_processes_active_offices_separately_as_system(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-10 00:00:00', 'Asia/Manila'));
        $this->fakeOffices();
        $service = Mockery::mock(DailyVisitorReportService::class);
        foreach ([[2, 'Registrar'], [7, 'Admissions']] as [$id, $name]) {
            $service->shouldReceive('ensureMissingOfficeDailyReports')->once()->with($id, $name, 1, null)
                ->andReturn(['generated' => 1, 'skipped' => 0, 'failed' => 0]);
        }
        $service->shouldNotReceive('generate');
        $this->app->instance(DailyVisitorReportService::class, $service);

        $this->artisan('generate:office-daily-visitor-reports')
            ->expectsOutput('Generated: 2')->assertSuccessful();
    }

    public function test_catchup_reuses_duplicate_checks_and_continues_after_an_office_failure(): void
    {
        $this->fakeOffices();
        $service = Mockery::mock(DailyVisitorReportService::class);
        $service->shouldReceive('ensureMissingOfficeDailyReports')->once()->with(2, 'Registrar', 7, null)
            ->andThrow(new \RuntimeException('Report storage unavailable'));
        $service->shouldReceive('ensureMissingOfficeDailyReports')->once()->with(7, 'Admissions', 7, null)
            ->andReturn(['generated' => 0, 'skipped' => 7, 'failed' => 0]);
        $this->app->instance(DailyVisitorReportService::class, $service);

        $this->artisan('generate:office-daily-visitor-reports --catch-up=7')
            ->expectsOutput('Skipped (already complete): 7')
            ->expectsOutput('Failed: 1')->assertFailed();
        $releasedLock = Cache::lock('office-daily-visitor-reports:generation', 60);
        $this->assertTrue($releasedLock->get());
        $releasedLock->release();
    }

    public function test_midnight_and_catchup_do_not_run_concurrently(): void
    {
        $lock = Cache::lock('office-daily-visitor-reports:generation', 3600);
        $this->assertTrue($lock->get());
        $service = Mockery::mock(DailyVisitorReportService::class);
        $service->shouldNotReceive('ensureMissingOfficeDailyReports');
        $this->app->instance(DailyVisitorReportService::class, $service);

        try {
            $this->artisan('generate:office-daily-visitor-reports --catch-up=7')
                ->expectsOutput('Another office daily report generation process is already running.')
                ->assertSuccessful();
        } finally {
            $lock->release();
        }
    }

    public function test_office_generation_is_scheduled_at_manila_midnight_with_hourly_catchup(): void
    {
        $this->artisan('list')->assertSuccessful();
        $events = collect($this->app->make(Schedule::class)->events());
        $daily = $events->first(fn ($event) => str_contains($event->command ?? '', 'generate:office-daily-visitor-reports') && ! str_contains($event->command, '--catch-up'));
        $catchUp = $events->first(fn ($event) => str_contains($event->command ?? '', 'generate:office-daily-visitor-reports --catch-up=7'));

        $this->assertNotNull($daily);
        $this->assertSame('0 0 * * *', $daily->expression);
        $this->assertSame('Asia/Manila', $daily->timezone);
        $this->assertTrue($daily->withoutOverlapping);
        $this->assertNotNull($catchUp);
        $this->assertSame('15 * * * *', $catchUp->expression);
    }
}
