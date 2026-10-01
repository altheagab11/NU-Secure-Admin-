<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Services\ActivityLogService;
use App\Services\DailyVisitorReportService;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class OfficeDailyReportController extends Controller
{
    public function __construct(
        protected DailyVisitorReportService $reportService,
        protected OfficeVisitorQueryService $queries
    ) {}

    public function index(Request $request)
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;

        $validated = $request->validate([
            'report_date' => ['nullable', 'date_format:Y-m-d'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $this->catchUpMissingReports($officeId, (string) ($office->office_name ?? ''));

        $query = DailyReport::query()
            ->with(['generator:user_id,first_name,last_name,email'])
            ->where('report_type', DailyReport::TYPE_DAILY_VISITOR_OFFICE)
            ->where('office_id', $officeId)
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

        $staffName = trim(trim((string) ($office->first_name ?? '')).' '.trim((string) ($office->last_name ?? '')));

        return view('office.daily-reports', [
            'pageTitle' => 'Daily Reports',
            'office' => $office,
            'staffName' => $staffName !== '' ? $staffName : 'Office Staff',
            'staffRole' => trim((string) ($office->position ?? 'Office Staff')) ?: 'Office Staff',
            'currentDate' => Carbon::now('Asia/Manila')->format('l, F j, Y'),
            'reports' => $reports,
            'filters' => [
                'report_date' => $validated['report_date'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
            ],
            'maxDate' => now('Asia/Manila')->toDateString(),
            'notifications' => $this->queries->unreadNotifications((int) $office->user_id, 10),
        ]);
    }

    public function generate(Request $request)
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;

        $validated = $request->validate([
            'report_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ], [
            'report_date.required' => 'Please select a report date.',
            'report_date.date_format' => 'The report date must use the YYYY-MM-DD format.',
            'report_date.before_or_equal' => 'Future dates are not allowed.',
        ]);

        try {
            $report = $this->reportService->generateForOffice(
                $validated['report_date'],
                $officeId,
                (string) ($office->office_name ?? ''),
                (int) $request->user()->getAuthIdentifier(),
                false
            );

            $message = 'Daily report generated successfully for '.$validated['report_date'].' ('.$report->record_count.' visitor record'.($report->record_count === 1 ? '' : 's').').';

            return redirect()
                ->route('office.daily-reports')
                ->with('success', $message);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('office.daily-reports')
                ->with('error', $e->getMessage())
                ->withInput();
        } catch (Throwable $e) {
            Log::error('Office manual daily report generation failed.', [
                'action' => 'office_daily_report_generation_failed',
                'report_date' => $validated['report_date'],
                'office_id' => $officeId,
                'user_id' => $request->user()?->getAuthIdentifier(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('office.daily-reports')
                ->with('error', 'Unable to generate the daily report. Please try again or contact support.')
                ->withInput();
        }
    }

    public function regenerate(Request $request, int $id)
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;

        $report = DailyReport::query()
            ->where('report_type', DailyReport::TYPE_DAILY_VISITOR_OFFICE)
            ->where('office_id', $officeId)
            ->findOrFail($id);

        $request->validate([
            'confirm' => ['accepted'],
        ], [
            'confirm.accepted' => 'Please confirm that you want to replace the existing report.',
        ]);

        try {
            $updated = $this->reportService->generateForOffice(
                $report->report_date->toDateString(),
                $officeId,
                (string) ($office->office_name ?? ''),
                (int) $request->user()->getAuthIdentifier(),
                true
            );

            return redirect()
                ->route('office.daily-reports')
                ->with('success', 'Report regenerated successfully for '.$updated->report_date->toDateString().'.');
        } catch (RuntimeException $e) {
            return redirect()
                ->route('office.daily-reports')
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Office daily report regeneration failed.', [
                'action' => 'office_daily_report_generation_failed',
                'report_id' => $report->id,
                'report_date' => $report->report_date?->toDateString(),
                'office_id' => $officeId,
                'user_id' => $request->user()?->getAuthIdentifier(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('office.daily-reports')
                ->with('error', 'Unable to regenerate the report. Please try again or contact support.');
        }
    }

    public function download(Request $request, int $id): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;

        $report = DailyReport::query()
            ->where('report_type', DailyReport::TYPE_DAILY_VISITOR_OFFICE)
            ->where('office_id', $officeId)
            ->findOrFail($id);

        if (! $report->isDownloadable()) {
            return redirect()
                ->route('office.daily-reports')
                ->with('error', 'This report is not available for download yet. It may still be generating or may have failed.');
        }

        $disk = Storage::disk(DailyVisitorReportService::DISK);
        $path = (string) $report->file_path;

        $normalized = str_replace('\\', '/', $path);
        $expectedPrefix = 'reports/daily/office/'.$officeId.'/';
        if (
            $normalized === ''
            || str_contains($normalized, '..')
            || ! str_starts_with($normalized, $expectedPrefix)
        ) {
            Log::warning('Office daily report download failed because the stored path is invalid.', [
                'action' => 'office_daily_report_download_invalid_path',
                'report_id' => $report->id,
                'report_date' => $report->report_date?->toDateString(),
                'office_id' => $officeId,
                'user_id' => $request->user()?->getAuthIdentifier(),
            ]);

            return redirect()
                ->route('office.daily-reports')
                ->with('error', 'The report file could not be found in secure storage. Please regenerate the report.');
        }

        if (! $disk->exists($normalized)) {
            try {
                $report = $this->reportService->generateForOffice(
                    $report->report_date->toDateString(),
                    $officeId,
                    (string) ($office->office_name ?? ''),
                    (int) $request->user()->getAuthIdentifier(),
                    true
                );
                $normalized = str_replace('\\', '/', (string) $report->file_path);
            } catch (Throwable $e) {
                Log::warning('Office daily report download failed because the file is missing and regeneration failed.', [
                    'action' => 'office_daily_report_download_missing',
                    'report_id' => $report->id,
                    'report_date' => $report->report_date?->toDateString(),
                    'office_id' => $officeId,
                    'user_id' => $request->user()?->getAuthIdentifier(),
                    'error' => $e->getMessage(),
                ]);

                return redirect()
                    ->route('office.daily-reports')
                    ->with('error', 'The report file could not be found in secure storage. Please regenerate the report.');
            }

            if (
                $normalized === ''
                || str_contains($normalized, '..')
                || ! str_starts_with($normalized, $expectedPrefix)
                || ! $disk->exists($normalized)
            ) {
                return redirect()
                    ->route('office.daily-reports')
                    ->with('error', 'The report file could not be found in secure storage. Please regenerate the report.');
            }
        }

        Log::info('Office daily visitor report downloaded.', [
            'action' => 'office_daily_report_downloaded',
            'report_id' => $report->id,
            'report_date' => $report->report_date?->toDateString(),
            'office_id' => $officeId,
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
                'office_id' => $officeId,
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

    protected function catchUpMissingReports(int $officeId, string $officeName): void
    {
        $cacheKey = 'office-daily-visitor-report:page-catch-up:'.$officeId;

        if (cache()->has($cacheKey)) {
            return;
        }

        cache()->put($cacheKey, true, now()->addMinutes(10));

        try {
            $this->reportService->ensureMissingOfficeDailyReports($officeId, $officeName, 7, null);
        } catch (Throwable $e) {
            Log::warning('Office daily report page catch-up failed.', [
                'action' => 'office_daily_report_page_catchup_failed',
                'office_id' => $officeId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
