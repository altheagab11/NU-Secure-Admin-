<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateDateRangeReportRequest;
use App\Models\DailyReport;
use App\Services\DailyVisitorReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DateRangeReportController extends Controller
{
    public function __construct(
        protected DailyVisitorReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $query = DailyReport::query()
            ->with(['generator:user_id,first_name,last_name,email'])
            ->where('report_type', DailyReport::TYPE_DATE_RANGE)
            ->orderByDesc('generated_at')
            ->orderByDesc('id');

        if (! empty($validated['date_from'])) {
            $query->whereDate('report_date', '>=', $validated['date_from']);
        }

        if (! empty($validated['date_to'])) {
            $query->where(function ($builder) use ($validated) {
                $builder->whereDate('date_range_end', '<=', $validated['date_to'])
                    ->orWhere(function ($inner) use ($validated) {
                        $inner->whereNull('date_range_end')
                            ->whereDate('report_date', '<=', $validated['date_to']);
                    });
            });
        }

        $reports = $query->paginate($this->resolvePerPage($request))->withQueryString();
        $maxDays = (int) config('reports.max_range_days', 31);

        return view('admin.date-range-reports', [
            'reports' => $reports,
            'filters' => [
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
            ],
            'maxDate' => now('Asia/Manila')->toDateString(),
            'maxRangeDays' => $maxDays,
        ]);
    }

    public function generate(GenerateDateRangeReportRequest $request): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $startDate = $request->validated('start_date');
        $endDate = $request->validated('end_date');

        try {
            $report = $this->reportService->generateDateRangeReport(
                $startDate,
                $endDate,
                $request->user()
            );

            return $this->streamReportDownload($request, $report, true);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('admin.date-range-reports')
                ->with('error', $e->getMessage())
                ->withInput();
        } catch (Throwable $e) {
            Log::error('Manual date-range report generation failed.', [
                'action' => 'date_range_report_generation_failed',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'user_id' => $request->user()?->getAuthIdentifier(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.date-range-reports')
                ->with('error', 'Unable to generate the date-range report. Please try again or contact support.')
                ->withInput();
        }
    }

    public function download(Request $request, int $id): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $report = DailyReport::query()
            ->where('report_type', DailyReport::TYPE_DATE_RANGE)
            ->findOrFail($id);

        return $this->streamReportDownload($request, $report, false);
    }

    protected function streamReportDownload(
        Request $request,
        DailyReport $report,
        bool $fromGenerate
    ): StreamedResponse|\Illuminate\Http\RedirectResponse {
        if (! $report->isDownloadable()) {
            return redirect()
                ->route('admin.date-range-reports')
                ->with('error', 'This report is not available for download yet. It may still be generating or may have failed.');
        }

        $disk = Storage::disk(DailyVisitorReportService::DISK);
        $path = (string) $report->file_path;
        $normalized = str_replace('\\', '/', $path);

        if (
            $normalized === ''
            || str_contains($normalized, '..')
            || ! str_starts_with($normalized, 'reports/date-range/')
            || ! $disk->exists($normalized)
        ) {
            Log::warning('Date-range report download failed because the file is missing.', [
                'action' => 'date_range_report_download_missing',
                'report_id' => $report->id,
                'start_date' => $report->report_date?->toDateString(),
                'end_date' => $report->date_range_end?->toDateString(),
                'user_id' => $request->user()?->getAuthIdentifier(),
            ]);

            return redirect()
                ->route('admin.date-range-reports')
                ->with('error', 'The report file could not be found in secure storage. Please generate the report again.');
        }

        $safeFileName = basename((string) $report->file_name);
        if ($safeFileName === '' || str_contains($safeFileName, '..')) {
            return redirect()
                ->route('admin.date-range-reports')
                ->with('error', 'The report file name is invalid.');
        }

        Log::info('Date-range visitor report downloaded.', [
            'action' => $fromGenerate ? 'date_range_report_generated_and_downloaded' : 'date_range_report_downloaded',
            'report_id' => $report->id,
            'start_date' => $report->report_date?->toDateString(),
            'end_date' => $report->date_range_end?->toDateString(),
            'record_count' => $report->record_count,
            'file_name' => $safeFileName,
            'user_id' => $request->user()?->getAuthIdentifier(),
            'generation_status' => $report->generation_status,
        ]);

        if ($fromGenerate) {
            session()->flash(
                'success',
                $report->record_count > 0
                    ? 'Date-range report generated successfully ('.$report->record_count.' visitor record'.($report->record_count === 1 ? '' : 's').').'
                    : 'No visitor records were found for the selected date range. An empty report was generated.'
            );
        }

        return $disk->download(
            $normalized,
            $safeFileName,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
}
