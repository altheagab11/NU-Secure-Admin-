<?php

namespace App\Console\Commands;

use App\Services\DailyVisitorReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GenerateDailyVisitorReport extends Command
{
    protected $signature = 'generate:daily-visitor-report
                            {date? : Report date in YYYY-MM-DD format (defaults to yesterday in Asia/Manila)}
                            {--force : Regenerate even if a completed report already exists}
                            {--catch-up=0 : Also generate missing reports for the last N complete Asia/Manila days}';

    protected $description = 'Generate the NU-Secure daily visitor Excel report and store it securely';

    public function handle(DailyVisitorReportService $service): int
    {
        $catchUpDays = max(0, (int) $this->option('catch-up'));

        if ($catchUpDays > 0) {
            return $this->runCatchUp($service, $catchUpDays);
        }

        // Scheduler runs at 00:01 Asia/Manila; default to yesterday's complete calendar day.
        $dateInput = $this->argument('date')
            ?: now('Asia/Manila')->subDay()->toDateString();
        $force = (bool) $this->option('force');
        $lockKey = 'daily-visitor-report:'.$dateInput;

        $lock = Cache::lock($lockKey, 300);

        if (! $lock->get()) {
            $this->error('Another generation process is already running for '.$dateInput.'.');

            return self::FAILURE;
        }

        try {
            $this->info('Starting automatic daily visitor report generation for '.$dateInput.'.');

            $report = $service->generate($dateInput, null, $force);

            $this->info('Daily visitor report ready.');
            $this->line('Date: '.$report->report_date?->toDateString());
            $this->line('File: '.$report->file_name);
            $this->line('Records: '.$report->record_count);
            $this->line('Status: '.$report->generation_status);

            return $report->generation_status === 'completed'
                ? self::SUCCESS
                : self::FAILURE;
        } catch (Throwable $e) {
            $this->error('Failed to generate daily visitor report: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            optional($lock)->release();
        }
    }

    private function runCatchUp(DailyVisitorReportService $service, int $days): int
    {
        $lock = Cache::lock('daily-visitor-report:catch-up', 600);

        if (! $lock->get()) {
            $this->warn('Another catch-up process is already running.');

            return self::SUCCESS;
        }

        try {
            $stats = $service->ensureMissingDailyReports($days, null);

            $this->info('Daily visitor report catch-up finished.');
            $this->line('Generated: '.$stats['generated']);
            $this->line('Skipped (already complete): '.$stats['skipped']);
            $this->line('Failed: '.$stats['failed']);

            return $stats['failed'] > 0 && $stats['generated'] === 0
                ? self::FAILURE
                : self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Catch-up failed: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            optional($lock)->release();
        }
    }
}
