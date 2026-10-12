<?php

namespace App\Console\Commands;

use App\Services\DailyVisitorReportService;
use Illuminate\Console\Command;
use Throwable;

class GenerateDailyVisitorReport extends Command
{
    protected $signature = 'generate:daily-visitor-report
                            {date? : Explicit manual report date (YYYY-MM-DD); otherwise yesterday at Manila midnight}
                            {--force : Explicitly regenerate an existing report; requires a date}';

    protected $description = 'Generate the previous day at Manila midnight, or one explicitly requested manual date';

    public function handle(DailyVisitorReportService $service): int
    {
        $now = now('Asia/Manila');
        $manualDate = $this->argument('date');

        if (! $manualDate && $this->option('force')) {
            $this->error('Explicit regeneration requires a report date.');

            return self::FAILURE;
        }

        if (! $manualDate && $now->format('H:i') !== '00:00') {
            $this->info('Automatic daily reports only run at 00:00 Asia/Manila. No reports generated.');

            return self::SUCCESS;
        }

        $date = $manualDate ?: $now->copy()->subDay()->toDateString();

        try {
            $this->info('Starting '.($manualDate ? 'manual' : 'automatic').' daily visitor report generation for '.$date.'.');
            $report = $service->generate($date, null, (bool) $this->option('force'));
            $this->info('Daily visitor report ready.');
            $this->line('Date: '.$report->report_date?->toDateString());
            $this->line('File: '.$report->file_name);
            $this->line('Records: '.$report->record_count);
            $this->line('Status: '.$report->generation_status);

            return $report->generation_status === 'completed' ? self::SUCCESS : self::FAILURE;
        } catch (Throwable $e) {
            $this->error('Failed to generate daily visitor report: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
