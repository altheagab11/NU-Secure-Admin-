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
                            {date? : Explicit manual report date (YYYY-MM-DD); otherwise yesterday at Manila midnight}
                            {--force : Explicitly regenerate existing reports; requires a date}';

    protected $description = 'Generate separate previous-day reports for active offices at Manila midnight';

    public function handle(DailyVisitorReportService $service): int
    {
        $now = now('Asia/Manila');
        $manualDate = $this->argument('date');

        if (! $manualDate && $this->option('force')) {
            $this->error('Explicit regeneration requires a report date.');

            return self::FAILURE;
        }

        if (! $manualDate && $now->format('H:i') !== '00:00') {
            $this->info('Automatic office daily reports only run at 00:00 Asia/Manila. No reports generated.');

            return self::SUCCESS;
        }

        // Capture yesterday once; a long run must not change dates between offices.
        $date = $manualDate ?: $now->copy()->subDay()->toDateString();
        $lock = Cache::lock('office-daily-visitor-reports:generation', 3600);

        if (! $lock->get()) {
            $this->warn('Another office daily report generation process is already running.');

            return self::SUCCESS;
        }

        try {
            $service->normalizeReportDate($date);
            $offices = DB::table('office')
                ->select('office_id', 'office_name')
                ->where('is_active', true)
                ->orderBy('office_id')
                ->get();
            $ready = 0;
            $failed = 0;

            foreach ($offices as $office) {
                try {
                    $report = $service->generateForOffice(
                        $date,
                        (int) $office->office_id,
                        (string) $office->office_name,
                        null,
                        (bool) $this->option('force')
                    );
                    if ($report->generation_status === 'completed') {
                        $ready++;
                    } else {
                        $failed++;
                    }
                } catch (Throwable $e) {
                    $failed++;
                    Log::error('Office daily report generation failed.', [
                        'report_date' => $date,
                        'office_id' => $office->office_id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error('Office '.$office->office_id.': '.$e->getMessage());
                }
            }

            $this->info('Office daily report generation finished.');
            $this->line('Date: '.$date);
            $this->line('Ready: '.$ready);
            $this->line('Failed: '.$failed);

            return $failed > 0 ? self::FAILURE : self::SUCCESS;
        } catch (Throwable $e) {
            report($e);
            $this->error('Unable to generate office daily reports: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            $lock->release();
        }
    }
}
