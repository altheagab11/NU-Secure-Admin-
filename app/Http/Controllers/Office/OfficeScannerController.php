<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\OfficeScanService;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
            'recentScans' => $this->queries->recentActivity((int) $office->office_id, 8),
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

        return $payload;
    }
}
