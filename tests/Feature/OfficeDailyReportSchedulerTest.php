<?php

namespace Tests\Feature;

use App\Models\DailyReport;
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

    private function fakeService(string $date): DailyVisitorReportService
    {
        $service = Mockery::mock(DailyVisitorReportService::class);
        $service->shouldReceive('normalizeReportDate')->once()->with($date)->andReturn(Carbon::parse($date, 'Asia/Manila'));
        $this->app->instance(DailyVisitorReportService::class, $service);

        return $service;
    }

    public function test_midnight_generation_processes_only_yesterday_for_each_active_office_as_system(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-13 00:00:00', 'Asia/Manila'));
        $this->fakeOffices();
        $service = $this->fakeService('2026-10-12');
        foreach ([[2, 'Registrar'], [7, 'Admissions']] as [$id, $name]) {
            $service->shouldReceive('generateForOffice')->once()->with('2026-10-12', $id, $name, null, false)
                ->andReturn(new DailyReport(['generation_status' => 'completed']));
        }
        $service->shouldNotReceive('generate');

        $this->artisan('generate:office-daily-visitor-reports')
            ->expectsOutput('Ready: 2')->assertSuccessful();
    }

    public function test_manual_generation_continues_after_an_office_failure_and_releases_lock(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-12 18:00:00', 'Asia/Manila'));
        $this->fakeOffices();
        $service = $this->fakeService('2026-10-05');
        $service->shouldReceive('generateForOffice')->once()->with('2026-10-05', 2, 'Registrar', null, true)
            ->andThrow(new \RuntimeException('Report storage unavailable'));
        $service->shouldReceive('generateForOffice')->once()->with('2026-10-05', 7, 'Admissions', null, true)
            ->andReturn(new DailyReport(['generation_status' => 'completed']));

        $this->artisan('generate:office-daily-visitor-reports 2026-10-05 --force')
            ->expectsOutput('Ready: 1')->expectsOutput('Failed: 1')->assertFailed();
        $releasedLock = Cache::lock('office-daily-visitor-reports:generation', 60);
        $this->assertTrue($releasedLock->get());
        $releasedLock->release();
    }

    public function test_office_commands_do_not_run_concurrently(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-13 00:00:00', 'Asia/Manila'));
        $lock = Cache::lock('office-daily-visitor-reports:generation', 3600);
        $this->assertTrue($lock->get());
        $service = Mockery::mock(DailyVisitorReportService::class);
        $service->shouldNotReceive('generateForOffice');
        $this->app->instance(DailyVisitorReportService::class, $service);

        try {
            $this->artisan('generate:office-daily-visitor-reports')
                ->expectsOutput('Another office daily report generation process is already running.')
                ->assertSuccessful();
        } finally {
            $lock->release();
        }
    }

    public function test_both_scopes_are_scheduled_only_at_manila_midnight(): void
    {
        $this->artisan('list')->assertSuccessful();
        $events = collect($this->app->make(Schedule::class)->events());
        $reports = $events->filter(fn ($event) => str_contains($event->command ?? '', 'generate:'));
        $this->assertCount(2, $reports);
        foreach ($reports as $event) {
            $this->assertSame('0 0 * * *', $event->expression);
            $this->assertSame('Asia/Manila', $event->timezone);
            $this->assertTrue($event->withoutOverlapping);
            $this->assertTrue($event->onOneServer);
            $this->assertTrue($event->runInBackground);
            $this->assertStringNotContainsString('--catch-up', $event->command);
            Carbon::setTestNow(Carbon::parse('2026-10-12 18:15:00', 'Asia/Manila'));
            $this->assertFalse($event->isDue($this->app));
            Carbon::setTestNow(Carbon::parse('2026-10-13 00:00:00', 'Asia/Manila'));
            $this->assertTrue($event->isDue($this->app));
        }
    }

    public function test_automatic_commands_do_nothing_outside_midnight(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow(Carbon::parse('2026-10-12 18:15:00', 'Asia/Manila'));
        $service = Mockery::mock(DailyVisitorReportService::class);
        $service->shouldNotReceive('generate');
        $service->shouldNotReceive('generateForOffice');
        $this->app->instance(DailyVisitorReportService::class, $service);
        $this->artisan('generate:daily-visitor-report')->assertSuccessful();
        $this->artisan('generate:office-daily-visitor-reports')->assertSuccessful();
        $this->artisan('generate:daily-visitor-report --force')->assertFailed();
        $this->artisan('generate:office-daily-visitor-reports --force')->assertFailed();
    }

    public function test_admin_midnight_command_generates_only_the_previous_manila_day(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow(Carbon::parse('2026-10-12 16:00:00', 'UTC'));
        $service = Mockery::mock(DailyVisitorReportService::class);
        $service->shouldReceive('generate')->once()->with('2026-10-12', null, false)
            ->andReturn(new DailyReport(['report_date' => '2026-10-12', 'generation_status' => 'completed']));
        $this->app->instance(DailyVisitorReportService::class, $service);
        $this->artisan('generate:daily-visitor-report')->assertSuccessful();
    }
}
