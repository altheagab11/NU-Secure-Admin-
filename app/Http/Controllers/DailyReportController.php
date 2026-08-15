<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Services\ActivityLogService;
use App\Services\DailyVisitorReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DailyReportController extends Controller
{
    public function __construct(
        protected DailyVisitorReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'report_date' => ['nullable', 'date_format:Y-m-d'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        // Catch up missing automated reports when the OS scheduler is not running.
        $this->catchUpMissingReports();

        $query = DailyReport::query()
            ->with(['generator:user_id,first_name,last_name,email'])
            ->where('report_type', DailyReport::TYPE_DAILY_VISITOR)
            ->orderByDesc('report_date')
            ->orderByDesc('id');

        if (! empty($validated['report_date'])) {
            $query->whereDate('report_date', $validated['report_date']);
        }

        if (! empty($validated['date_from'])) {
            $query->whereDate('report_date', '>=', $validated['date_from']);
        }

        if (! empty($validated['date_to'])) {
            $query->whereDate('report_date', '<=', $validated['date_to']);
        }

        $reports = $query->paginate($this->resolvePerPage($request))->withQueryString();

        return view('admin.daily-reports', [
            'reports' => $reports,
            'filters' => [
                'report_date' => $validated['report_date'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
            ],
            'maxDate' => now('Asia/Manila')->toDateString(),
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'report_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ], [
            'report_date.required' => 'Please select a report date.',
            'report_date.date_format' => 'The report date must use the YYYY-MM-DD format.',
            'report_date.before_or_equal' => 'Future dates are not allowed.',
        ]);

        try {
            $report = $this->reportService->generate(
                $validated['report_date'],
                (int) $request->user()->getAuthIdentifier(),
                false
            );

            $message = 'Daily report generated successfully for '.$validated['report_date'].' ('.$report->record_count.' visitor record'.($report->record_count === 1 ? '' : 's').').';

            return redirect()
                ->route('admin.daily-reports')
                ->with('success', $message);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('admin.daily-reports')
                ->with('error', $e->getMessage())
                ->withInput();
        } catch (Throwable $e) {
            Log::error('Manual daily report generation failed.', [
                'action' => 'daily_report_generation_failed',
                'report_date' => $validated['report_date'],
                'user_id' => $request->user()?->getAuthIdentifier(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.daily-reports')
                ->with('error', 'Unable to generate the daily report. Please try again or contact support.')
                ->withInput();
        }
    }

    public function regenerate(Request $request, int $id)
    {
        $report = DailyReport::query()
            ->where('report_type', DailyReport::TYPE_DAILY_VISITOR)
            ->findOrFail($id);

        $request->validate([
            'confirm' => ['accepted'],
        ], [
            'confirm.accepted' => 'Please confirm that you want to replace the existing report.',
        ]);

        try {
            $updated = $this->reportService->generate(
                $report->report_date->toDateString(),
                (int) $request->user()->getAuthIdentifier(),
                true
            );

            return redirect()
                ->route('admin.daily-reports')
                ->with('success', 'Report regenerated successfully for '.$updated->report_date->toDateString().'.');
        } catch (RuntimeException $e) {
            return redirect()
                ->route('admin.daily-reports')
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Daily report regeneration failed.', [
                'action' => 'daily_report_generation_failed',
                'report_id' => $report->id,
                'report_date' => $report->report_date?->toDateString(),
                'user_id' => $request->user()?->getAuthIdentifier(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.daily-reports')
                ->with('error', 'Unable to regenerate the report. Please try again or contact support.');
        }
    }

    public function download(Request $request, int $id): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $report = DailyReport::query()
            ->where('report_type', DailyReport::TYPE_DAILY_VISITOR)
            ->findOrFail($id);

        if (! $report->isDownloadable()) {
            return redirect()
                ->route('admin.daily-reports')
                ->with('error', 'This report is not available for download yet. It may still be generating or may have failed.');
        }

        $disk = Storage::disk(DailyVisitorReportService::DISK);
        $path = (string) $report->file_path;

        // Prevent directory traversal / arbitrary file access.
        $normalized = str_replace('\\', '/', $path);
        if (
            $normalized === ''
            || str_contains($normalized, '..')
            || ! str_starts_with($normalized, 'reports/daily/')
            || ! $disk->exists($normalized)
        ) {
            Log::warning('Daily report download failed because the file is missing.', [
                'action' => 'daily_report_download_missing',
                'report_id' => $report->id,
                'report_date' => $report->report_date?->toDateString(),
                'user_id' => $request->user()?->getAuthIdentifier(),
            ]);

            return redirect()
                ->route('admin.daily-reports')
                ->with('error', 'The report file could not be found in secure storage. Please regenerate the report.');
        }

        Log::info('Daily visitor report downloaded.', [
            'action' => 'daily_report_downloaded',
            'report_id' => $report->id,
            'report_date' => $report->report_date?->toDateString(),
            'user_id' => $request->user()?->getAuthIdentifier(),
        ]);

        ActivityLogService::log(
            action: 'Report Downloaded',
            module: 'Reports',
            description: ActivityLogService::actorLabel().' downloaded '.$report->file_name.'.',
            entityType: 'DailyReport',
            entityId: $report->id,
            newValues: [
                'file_name' => $report->file_name,
                'report_date' => $report->report_date?->toDateString(),
            ]
        );

        return $disk->download(
            $normalized,
            $report->file_name,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    /**
     * Fill missing daily reports for recent days when the OS scheduler is idle.
     * Throttled so page loads stay responsive.
     */
    protected function catchUpMissingReports(): void
    {
        $cacheKey = 'daily-visitor-report:page-catch-up';

        if (cache()->has($cacheKey)) {
            return;
        }

        cache()->put($cacheKey, true, now()->addMinutes(10));

        try {
            $this->reportService->ensureMissingDailyReports(7, null);
        } catch (Throwable $e) {
            Log::warning('Daily report page catch-up failed.', [
                'action' => 'daily_report_page_catchup_failed',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
