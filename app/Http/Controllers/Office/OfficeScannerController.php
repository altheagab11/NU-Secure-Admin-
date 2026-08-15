<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\OfficeScanService;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class OfficeScannerController extends Controller
{
    public function __construct(
        protected OfficeScanService $scanService,
        protected OfficeVisitorQueryService $queries
    ) {
    }

    public function index(Request $request): View
    {
        $office = $request->attributes->get('office_context');
        $staffName = trim(trim((string) ($office->first_name ?? '')).' '.trim((string) ($office->last_name ?? '')));

        return view('office.scanner', [
            'pageTitle' => 'QR Scanner',
            'office' => $office,
            'staffName' => $staffName !== '' ? $staffName : 'Office Staff',
            'staffRole' => trim((string) ($office->position ?? 'Office Staff')) ?: 'Office Staff',
            'currentDate' => Carbon::now('Asia/Manila')->format('l, F j, Y'),
            'recentScans' => $this->paginateRecentScans($request, $office),
            'notifications' => $this->queries->unreadNotifications((int) $office->user_id, 10),
        ]);
    }

    public function verify(Request $request)
    {
        $office = $request->attributes->get('office_context');
        if (! $office) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHORIZED',
                'message' => 'You are not authorized to record visits for this office.',
            ], 403);
        }

        $validated = $request->validate([
            'qr_payload' => ['required', 'string', 'max:4000'],
            'scan_method' => ['nullable', 'string', 'in:camera,manual,hardware'],
            'scans_per_page' => ['nullable', 'integer', 'in:5,10,25,50,75,100'],
        ]);

        $rateKey = 'office-scan-verify:'.(int) $office->user_id;
        if (RateLimiter::tooManyAttempts($rateKey, 30)) {
            return response()->json([
                'success' => false,
                'code' => 'NETWORK_ERROR',
                'message' => 'Too many scan attempts. Please wait a moment and try again.',
            ], 429);
        }
        RateLimiter::hit($rateKey, 60);

        $result = $this->scanService->verify(
            (string) $validated['qr_payload'],
            $office,
            (string) ($validated['scan_method'] ?? 'camera')
        );
        $result['recent_scans'] = $this->recentScansPayload($request, $office);

        return response()->json($this->formatJson($result), (int) ($result['http'] ?? 400));
    }

    public function checkIn(Request $request)
    {
        $office = $request->attributes->get('office_context');
        if (! $office) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHORIZED',
                'message' => 'You are not authorized to record visits for this office.',
            ], 403);
        }

        $validated = $request->validate([
            'qr_payload' => ['required', 'string', 'max:4000'],
            'scan_method' => ['nullable', 'string', 'in:camera,manual,hardware'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'scans_per_page' => ['nullable', 'integer', 'in:5,10,25,50,75,100'],
        ]);

        $rateKey = 'office-scan-checkin:'.(int) $office->user_id;
        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            return response()->json([
                'success' => false,
                'code' => 'NETWORK_ERROR',
                'message' => 'Too many check-in attempts. Please wait a moment and try again.',
            ], 429);
        }
        RateLimiter::hit($rateKey, 60);

        // Reject client-supplied office_id if present — office always comes from auth context.
        if ($request->filled('office_id') && (int) $request->input('office_id') !== (int) $office->office_id) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHORIZED',
                'message' => 'You are not authorized to record visits for this office.',
            ], 403);
        }

        $result = $this->scanService->checkIn(
            (string) $validated['qr_payload'],
            $office,
            (string) ($validated['scan_method'] ?? 'camera'),
            $validated['remarks'] ?? null
        );
        $result['recent_scans'] = $this->recentScansPayload($request, $office);

        return response()->json($this->formatJson($result), (int) ($result['http'] ?? 400));
    }

    protected function formatJson(array $result): array
    {
        $payload = [
            'success' => (bool) ($result['success'] ?? false),
            'message' => (string) ($result['message'] ?? ''),
        ];

        if (! empty($result['code'])) {
            $payload['code'] = $result['code'];
        }
        if (array_key_exists('expected_office', $result)) {
            $payload['expected_office'] = $result['expected_office'];
        }
        if (! empty($result['data'])) {
            $payload['data'] = $result['data'];
        }
        if (array_key_exists('recent_scans', $result)) {
            $payload['recent_scans'] = $result['recent_scans'];
        }

        return $payload;
    }

    protected function recentScansPayload(Request $request, object $office): array
    {
        $paginator = $this->paginateRecentScans($request, $office, 1);

        return [
            'data' => $paginator->getCollection()->map(function ($row) {
                $status = trim((string) ($row->validation_status ?? ''));

                return [
                    'visitor_name' => (string) ($row->visitor_name ?? 'Visitor'),
                    'control_number' => (string) (($row->control_number ?? '') !== '' ? $row->control_number : '—'),
                    'time_label' => (string) ($row->scan_time_label ?? '—'),
                    'validation_status' => $status !== '' ? $status : '—',
                ];
            })->values()->all(),
            'meta' => [
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page' => max(1, $paginator->lastPage()),
                'per_page' => $paginator->perPage(),
                'first_url' => $paginator->url(1),
                'prev_url' => $paginator->previousPageUrl(),
                'next_url' => $paginator->nextPageUrl(),
                'last_url' => $paginator->url(max(1, $paginator->lastPage())),
            ],
        ];
    }

    protected function paginateRecentScans(Request $request, object $office, ?int $forcePage = null): LengthAwarePaginator
    {
        $perPage = $this->resolveScannerPerPage($request);
        $items = $this->queries->recentActivity((int) $office->office_id, 500, true);
        $total = $items->count();
        $lastPage = max(1, (int) ceil(($total > 0 ? $total : 1) / $perPage));
        $page = $forcePage ?? max(1, (int) $request->query('scans_page', 1));
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $query = $request->query();
        $query['scans_per_page'] = $perPage;

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            [
                'path' => route('office.scanner'),
                'query' => $query,
                'pageName' => 'scans_page',
            ]
        );
    }

    protected function resolveScannerPerPage(Request $request): int
    {
        $value = (int) $request->input('scans_per_page', $request->query('scans_per_page', 5));

        return in_array($value, [5, 10, 25, 50, 75, 100], true) ? $value : 5;
    }
}
