<?php

namespace App\Console\Commands;

use App\Services\DailyVisitorReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateOfficeDailyVisitorReports extends Command
{
    protected $signature = 'generate:office-daily-visitor-reports
                            {--catch-up=1 : Generate missing reports for the last N complete Asia/Manila days (1–31)}';

    protected $description = 'Generate separate daily visitor Excel reports for each active office';

    public function handle(DailyVisitorReportService $service): int
    {
        $days = max(1, min(31, (int) $this->option('catch-up')));
        // Share one lock across the midnight and catch-up commands.
        $lock = Cache::lock('office-daily-visitor-reports:generation', 3600);

        if (! $lock->get()) {
            $this->warn('Another office daily report generation process is already running.');

            return self::SUCCESS;
        }

        try {
            $offices = DB::table('office')
                ->select('office_id', 'office_name')
                ->where('is_active', true)
                ->orderBy('office_id')
                ->get();

            $totals = ['generated' => 0, 'skipped' => 0, 'failed' => 0];

            foreach ($offices as $office) {
                try {
                    // Reuse office-scoped generation, duplicate checks, and missing-file repair.
                    $stats = $service->ensureMissingOfficeDailyReports(
                        (int) $office->office_id,
                        (string) $office->office_name,
                        $days,
                        null
                    );

                    foreach (array_keys($totals) as $key) {
                        $totals[$key] += $stats[$key];
                    }
                } catch (Throwable $e) {
                    $totals['failed']++;
                    Log::error('Scheduled office daily report generation failed.', [
                        'office_id' => $office->office_id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error('Office '.$office->office_id.': '.$e->getMessage());
                }
            }

            $this->info('Office daily report generation finished.');
            $this->line('Generated: '.$totals['generated']);
            $this->line('Skipped (already complete): '.$totals['skipped']);
            $this->line('Failed: '.$totals['failed']);

            return $totals['failed'] > 0 ? self::FAILURE : self::SUCCESS;
        } catch (Throwable $e) {
            report($e);
            $this->error('Unable to generate office daily reports: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            $lock->release();
        }
    }
}
