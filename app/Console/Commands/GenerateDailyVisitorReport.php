<?php

namespace App\Console\Commands;

use App\Services\DailyVisitorReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GenerateDailyVisitorReport extends Command
{
    protected $signature = 'generate:daily-visitor-report
                            {date? : Report date in YYYY-MM-DD format (defaults to today in Asia/Manila)}
                            {--force : Regenerate even if a completed report already exists}';

    protected $description = 'Generate the NU-Secure daily visitor Excel report and store it securely';

    public function handle(DailyVisitorReportService $service): int
    {
        $dateInput = $this->argument('date') ?: now('Asia/Manila')->toDateString();
        $force = (bool) $this->option('force');
        $lockKey = 'daily-visitor-report:'.$dateInput;

        $lock = Cache::lock($lockKey, 300);

        if (! $lock->get()) {
            $this->error('Another generation process is already running for '.$dateInput.'.');

            return self::FAILURE;
        }

        try {
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
}
