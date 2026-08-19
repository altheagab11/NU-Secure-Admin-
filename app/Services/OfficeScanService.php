<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\ActivityLogService;

class OfficeScanService
{
    /**
     * Verify a QR payload for the logged-in office without recording check-in.
     *
     * @return array{success: bool, code?: string, message: string, http: int, data?: array, expected_office?: string}
     */
    public function verify(string $rawQr, object $officeContext, string $scanMethod = 'camera'): array
    {
        $officeId = (int) $officeContext->office_id;
        $userId = (int) ($officeContext->user_id ?? Auth::id());

        $this->audit('qr_verification_attempt', [
            'office_id' => $officeId,
            'user_id' => $userId,
            'scan_method' => $scanMethod,
            'payload_preview' => Str::limit(trim($rawQr), 120),
        ]);

        $parsed = $this->parseQrPayload($rawQr);

        if ($parsed['qr_token'] === null && $parsed['control_number'] === null && $parsed['pass_number'] === null) {
            $this->audit('invalid_qr', [
                'office_id' => $officeId,
                'user_id' => $userId,
                'scan_method' => $scanMethod,
            ]);

            return $this->error('INVALID_QR', 'The scanned QR code is invalid or not recognized.', 422);
        }

        $visit = $this->findVisitByPayload($parsed, includeExited: true);

        if (! $visit) {
            $this->audit('invalid_qr', [
                'office_id' => $officeId,
                'user_id' => $userId,
                'parsed' => $parsed,
            ]);

            return $this->error('INVALID_QR', 'The scanned QR code is invalid or not recognized.', 404);
        }

        if (! empty($visit->exit_time)) {
            $this->audit('expired_qr', [
                'office_id' => $officeId,
                'user_id' => $userId,
                'visit_id' => (int) $visit->visit_id,
            ]);

            return $this->error('EXPIRED_QR', 'This QR ticket is expired or no longer active.', 422);
        }

        $exitStatus = Str::lower(trim((string) ($visit->exit_status_name ?? '')));
        if ($exitStatus !== '' && (str_contains($exitStatus, 'cancel') || str_contains($exitStatus, 'void'))) {
            return $this->error('CANCELLED_VISIT', 'This visit has been cancelled.', 422);
        }

        if ($exitStatus !== '' && ! str_contains($exitStatus, 'active') && ! str_contains($exitStatus, 'ongoing')) {
            // Completed / overstay without exit_time is unusual; treat non-active as inactive.
            if (str_contains($exitStatus, 'complete') || str_contains($exitStatus, 'exit')) {
                return $this->error('INACTIVE_VISIT', 'This visit is no longer active.', 422);
            }
        }

        $route = $this->loadRoute((int) $visit->visit_id);
        if ($route->isEmpty()) {
            return $this->error('INACTIVE_VISIT', 'This visit has no office route configured.', 422);
        }

        if (! $this->isSequentialRoute($visit)) {
            return $this->verifyFlexibleRoute($visit, $route, $officeContext, $officeId, $userId, $scanMethod);
        }

        $current = $this->resolveCurrentExpectation($route);
        $staffOfficeExpectations = $route->where('office_id', $officeId)->values();

        // Already checked in at this office for the current matching step.
        if ($current && (int) $current->office_id === $officeId && $this->isExpectationDone($current)) {
            return $this->error('DUPLICATE_SCAN', 'This visitor has already checked in at this office.', 409);
        }

        // Staff office already completed for every matching route step, and is not the current step.
        if (
            $staffOfficeExpectations->isNotEmpty()
            && $staffOfficeExpectations->every(fn ($row) => $this->isExpectationDone($row))
            && (! $current || (int) $current->office_id !== $officeId)
        ) {
            $this->recordFailedScan($visit, $officeId, $userId, 'Unauthorized', 'Duplicate office scan attempt', $scanMethod);
            $this->audit('duplicate_scan_attempt', [
                'office_id' => $officeId,
                'user_id' => $userId,
                'visit_id' => (int) $visit->visit_id,
            ]);

            return $this->error('DUPLICATE_SCAN', 'This visitor has already checked in at this office.', 409);
        }

        if (! $current) {
            return $this->error('INACTIVE_VISIT', 'This visit is no longer active.', 422);
        }

        // Previous office incomplete: staff office matches a later step, but earlier steps pending.
        if ((int) $current->office_id !== $officeId) {
            $matchingPending = $route
                ->where('office_id', $officeId)
                ->filter(fn ($row) => ! $this->isExpectationDone($row))
                ->sortBy('expected_order')
                ->first();

            if ($matchingPending && (int) $matchingPending->expected_order > (int) $current->expected_order) {
                $previousOffice = $this->findPreviousOfficeName($route, (int) $matchingPending->expected_order);
                $this->recordFailedScan(
                    $visit,
                    $officeId,
                    $userId,
                    'Unauthorized',
                    'Previous office incomplete. Next expected: '.($current->office_name ?? 'Unknown'),
                    $scanMethod
                );
                $this->audit('previous_office_incomplete', [
                    'office_id' => $officeId,
                    'user_id' => $userId,
                    'visit_id' => (int) $visit->visit_id,
                    'expected_office_id' => (int) $current->office_id,
                ]);

                return $this->error(
                    'PREVIOUS_INCOMPLETE',
                    'The visitor must complete the previous office check-in before proceeding.'
                        .($previousOffice ? ' Previous office: '.$previousOffice.'.' : ''),
                    422,
                    [
                        'expected_office' => (string) ($current->office_name ?? ''),
                        'data' => $this->buildVisitorPayload($visit, $route, $current, $officeContext),
                    ]
                );
            }

            $scanId = $this->recordFailedScan(
                $visit,
                $officeId,
                $userId,
                'Unauthorized',
                'Wrong office scan. Next expected: '.($current->office_name ?? 'Unknown'),
                $scanMethod
            );
            $this->createWrongOfficeAlert($visit, $officeId, (string) ($current->office_name ?? 'Unknown'), $scanId);
            $this->audit('wrong_office_scan_attempt', [
                'office_id' => $officeId,
                'user_id' => $userId,
                'visit_id' => (int) $visit->visit_id,
                'expected_office_id' => (int) $current->office_id,
            ]);

            $visitorName = trim(trim((string) ($visit->first_name ?? '')).' '.trim((string) ($visit->last_name ?? ''))) ?: 'visitor';
            $scannedOffice = DB::table('office')->where('office_id', $officeId)->value('office_name') ?: 'this office';
            ActivityLogService::log(
                action: 'Wrong Office Detected',
                module: 'Office Scanning',
                description: ActivityLogService::actorLabel().' scanned '.$visitorName.' at '.$scannedOffice.' but the visitor was expected at '.trim((string) ($current->office_name ?? 'another office')).'.',
                entityType: 'Visit',
                entityId: (int) $visit->visit_id,
                status: ActivityLogService::STATUS_WARNING,
                newValues: [
                    'visit_id' => (int) $visit->visit_id,
                    'scanned_office_id' => $officeId,
                    'expected_office' => (string) ($current->office_name ?? ''),
                    'scan_id' => $scanId,
                ]
            );

            return $this->error(
                'WRONG_OFFICE',
                'This visitor is not currently expected at your office. The next expected destination is '
                    .trim((string) ($current->office_name ?? 'another office')).'.',
                422,
                [
                    'expected_office' => (string) ($current->office_name ?? ''),
                    'data' => $this->buildVisitorPayload($visit, $route, $current, $officeContext),
                ]
            );
        }

        // Ensure earlier route steps are complete.
        $previousIncomplete = $route
            ->filter(fn ($row) => (int) $row->expected_order < (int) $current->expected_order)
            ->first(fn ($row) => ! $this->isExpectationDone($row));

        if ($previousIncomplete) {
            return $this->error(
                'PREVIOUS_INCOMPLETE',
                'The visitor must complete the previous office check-in before proceeding.',
                422,
                ['expected_office' => (string) ($previousIncomplete->office_name ?? '')]
            );
        }

        $payload = $this->buildVisitorPayload($visit, $route, $current, $officeContext);

        return [
            'success' => true,
            'message' => 'Visitor verified successfully.',
            'http' => 200,
            'data' => $payload,
        ];
    }

    /**
     * Confirm and record office check-in after successful verification.
     *
     * @return array{success: bool, code?: string, message: string, http: int, data?: array, expected_office?: string}
     */
    public function checkIn(string $rawQr, object $officeContext, string $scanMethod = 'camera', ?string $remarks = null): array
    {
        $verification = $this->verify($rawQr, $officeContext, $scanMethod);
        if (! ($verification['success'] ?? false)) {
            return $verification;
        }

        $officeId = (int) $officeContext->office_id;
        $userId = (int) ($officeContext->user_id ?? Auth::id());
        $visitId = (int) ($verification['data']['visit']['visit_id'] ?? 0);
        $expectationId = (int) ($verification['data']['current_expectation_id'] ?? 0);

        if ($visitId <= 0 || $expectationId <= 0) {
            return $this->error('INVALID_QR', 'Unable to confirm check-in for this visitor.', 422);
        }

        $advanceProgress = null;

        try {
            $result = DB::transaction(function () use ($visitId, $expectationId, $officeId, $userId, $scanMethod, $remarks, $officeContext, &$advanceProgress) {
                $expectation = DB::table('office_expectation')
                    ->where('expectation_id', $expectationId)
                    ->where('visit_id', $visitId)
                    ->lockForUpdate()
                    ->first();

                if (! $expectation) {
                    return $this->error('INVALID_QR', 'Route step not found for this visit.', 404);
                }

                if ((int) $expectation->office_id !== $officeId) {
                    return $this->error('UNAUTHORIZED', 'You are not authorized to record visits for this office.', 403);
                }

                if ($this->isExpectationDone($expectation)) {
                    return $this->error('DUPLICATE_SCAN', 'This visitor has already checked in at this office.', 409);
                }

                $visit = $this->findVisitById($visitId);
                if ($visit && $this->isSequentialRoute($visit)) {
                    // Re-check previous steps under lock (enrollee / sequential routes only).
                    $previousIncomplete = DB::table('office_expectation as oe')
                        ->leftJoin('expectation_status as xs', 'xs.expectation_status_id', '=', 'oe.expectation_status_id')
                        ->where('oe.visit_id', $visitId)
                        ->where('oe.expected_order', '<', (int) $expectation->expected_order)
                        ->whereNull('oe.arrived_at')
                        ->where(function ($q) {
                            $q->whereNull('xs.status_name')
                                ->orWhereRaw("LOWER(TRIM(COALESCE(xs.status_name, ''))) NOT IN (?, ?, ?, ?)", [
                                    'arrived', 'completed', 'complete', 'skipped',
                                ]);
                        })
                        ->exists();

                    if ($previousIncomplete) {
                        return $this->error(
                            'PREVIOUS_INCOMPLETE',
                            'The visitor must complete the previous office check-in before proceeding.',
                            422
                        );
                    }
                }

                $now = $this->philippinesNow();
                $arrivedStatusId = $this->resolveExpectationStatusId(['arrived', 'completed', 'complete', 'done'])
                    ?? $this->resolveExpectationStatusId(['pending'])
                    ?? 1;
                $validValidationId = $this->resolveValidationStatusId(['valid']) ?? 1;

                DB::table('office_expectation')
                    ->where('expectation_id', $expectationId)
                    ->update([
                        'arrived_at' => $now,
                        'expectation_status_id' => $arrivedStatusId,
                    ]);

                $scanId = DB::table('office_scan')->insertGetId([
                    'visit_id' => $visitId,
                    'office_id' => $officeId,
                    'scanned_by_user_id' => $userId,
                    'scan_time' => $now,
                    'validation_status_id' => $validValidationId,
                    'remarks' => $remarks ?: ('Office check-in via '.$scanMethod),
                ], 'scan_id');

                $advanceProgress = [
                    'visit_id' => $visitId,
                    'office_id' => $officeId,
                    'expected_order' => (int) $expectation->expected_order,
                    'checked_at' => $now,
                ];

                $visit = $this->findVisitById($visitId);
                $route = $this->loadRoute($visitId);
                $checkedInStep = $route->firstWhere('expectation_id', $expectationId);
                $displayStep = $checkedInStep ?: $expectation;

                $this->audit('successful_office_check_in', [
                    'office_id' => $officeId,
                    'user_id' => $userId,
                    'visit_id' => $visitId,
                    'scan_id' => $scanId,
                    'expectation_id' => $expectationId,
                    'scan_method' => $scanMethod,
                ]);

                $payload = $this->buildVisitorPayload($visit, $route, $displayStep, $officeContext);
                $payload['authorized'] = true;
                $payload['checked_in'] = true;

                return [
                    'success' => true,
                    'message' => 'Office check-in recorded successfully.',
                    'http' => 200,
                    'data' => array_merge(
                        $payload,
                        [
                            'scan_id' => $scanId,
                            'checked_in_at' => $now->toDateTimeString(),
                        ]
                    ),
                ];
            });

            if (($result['success'] ?? false) && is_array($advanceProgress)) {
                $this->advanceEnrolleeProgress(
                    (int) $advanceProgress['visit_id'],
                    (int) $advanceProgress['office_id'],
                    (int) $advanceProgress['expected_order'],
                    $advanceProgress['checked_at']
                );

                $visitorName = (string) ($result['data']['visitor']['full_name'] ?? 'visitor');
                $officeName = (string) ($officeContext->office_name ?? $result['data']['staff_office']['office_name'] ?? 'the office');
                $scanId = $result['data']['scan_id'] ?? null;

                ActivityLogService::log(
                    action: 'QR Scan',
                    module: 'Office Scanning',
                    description: ActivityLogService::actorLabel().' scanned visitor '.$visitorName.' at '.$officeName.'.',
                    entityType: 'OfficeScan',
                    entityId: is_numeric($scanId) ? (int) $scanId : null,
                    newValues: [
                        'visit_id' => $visitId,
                        'office_id' => $officeId,
                        'office_name' => $officeName,
                        'visitor_name' => $visitorName,
                        'scan_method' => $scanMethod,
                        'validation' => 'Valid',
                    ]
                );
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Office check-in failed', [
                'message' => $e->getMessage(),
                'office_id' => $officeId,
                'visit_id' => $visitId,
            ]);

            return $this->error(
                'CHECKIN_FAILED',
                'Unable to record office check-in. Please try again.',
                500
            );
        }
    }

    public function parseQrPayload(string $rawQr): array
    {
        $payload = [
            'qr_token' => null,
            'control_number' => null,
            'pass_number' => null,
        ];

        $rawQr = trim($rawQr);
        if ($rawQr === '') {
            return $payload;
        }

        $decoded = json_decode($rawQr, true);
        if (is_array($decoded)) {
            $payload['qr_token'] = $this->normalizeNullableString($decoded['qr_token'] ?? null);
            $payload['control_number'] = $this->normalizeNullableString($decoded['control_number'] ?? null);
            $payload['pass_number'] = $this->normalizeNullableString($decoded['pass_number'] ?? null);

            return $payload;
        }

        if (preg_match('~/enrollee/progress/([^/?#]+)~i', $rawQr, $matches)) {
            $tokenFromUrl = $this->normalizeNullableString(urldecode($matches[1]));
            if ($tokenFromUrl !== null) {
                $payload['qr_token'] = $tokenFromUrl;

                return $payload;
            }
        }

        $rawValue = $this->normalizeNullableString($rawQr);
        if ($rawValue !== null) {
            if (stripos($rawValue, 'QR-') === 0) {
                $payload['qr_token'] = $rawValue;
            } else {
                $payload['control_number'] = $rawValue;
                $payload['pass_number'] = $rawValue;
            }
        }

        return $payload;
    }

    public function resolveVisitorPhotoUrl(?string $photoPath): ?string
    {
        $cleanPath = trim((string) $photoPath);
        if ($cleanPath === '') {
            return null;
        }

        if (Str::startsWith($cleanPath, ['http://', 'https://'])) {
            return $cleanPath;
        }

        if (Str::startsWith($cleanPath, ['/storage/', 'storage/'])) {
            return url('/'.ltrim($cleanPath, '/'));
        }

        [$bucket, $objectPath] = $this->parseStorageObjectPath($cleanPath);
        if ($bucket === null || $objectPath === null) {
            return null;
        }

        $supabaseUrl = rtrim((string) env('SUPABASE_URL', ''), '/');
        if ($supabaseUrl === '') {
            return null;
        }

        $supabaseKey = (string) (env('SUPABASE_STORAGE_KEY') ?: env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_KEY') ?: '');
        if ($supabaseKey !== '') {
            try {
                $encodedBucket = rawurlencode($bucket);
                $encodedObjectPath = collect(explode('/', $objectPath))
                    ->map(fn ($segment) => rawurlencode($segment))
                    ->implode('/');

                $signedResponse = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer '.$supabaseKey,
                    'Accept' => 'application/json',
                ])->timeout(20)->post($supabaseUrl.'/storage/v1/object/sign/'.$encodedBucket.'/'.$encodedObjectPath, [
                    'expiresIn' => 3600,
                ]);

                if ($signedResponse->ok()) {
                    $payload = $signedResponse->json();
                    $signed = is_array($payload) ? ($payload['signedURL'] ?? $payload['signedUrl'] ?? null) : null;
                    if (is_string($signed) && trim($signed) !== '') {
                        if (preg_match('/^https?:\/\//i', $signed) === 1) {
                            return $signed;
                        }

                        $signedPath = ltrim($signed, '/');
                        if (Str::startsWith($signedPath, 'storage/v1/')) {
                            return $supabaseUrl.'/'.$signedPath;
                        }
                        if (Str::startsWith($signedPath, 'object/')) {
                            return $supabaseUrl.'/storage/v1/'.$signedPath;
                        }

                        return $supabaseUrl.'/'.$signedPath;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Unable to sign office visitor preview URL: '.$e->getMessage());
            }
        }

        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $objectPath)));

        return $supabaseUrl.'/storage/v1/object/public/'.rawurlencode($bucket).'/'.$encodedPath;
    }

    protected function findVisitByPayload(array $parsed, bool $includeExited = false): ?object
    {
        $query = DB::table('visit as v')
            ->join('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('exit_status as es', 'es.exit_status_id', '=', 'v.exit_status_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'v.primary_office_id')
            ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
            ->select(
                'v.visit_id',
                'v.entry_time',
                'v.exit_time',
                'v.exit_status_id',
                'v.qr_token',
                'v.visitor_id',
                'v.purpose_reason',
                'v.primary_office_id',
                'v.destination_text',
                'v.control_number',
                'v.pass_number',
                'vr.first_name',
                'vr.last_name',
                'vr.visitor_photo_with_id_url',
                'vr.contact_no',
                'o.office_name as primary_office_name',
                'es.exit_status_name',
                'vt.visit_type_name'
            )
            ->where(function ($query) use ($parsed) {
                if (! empty($parsed['qr_token'])) {
                    $query->orWhereRaw('LOWER(TRIM(COALESCE(v.qr_token, \'\'))) = ?', [strtolower($parsed['qr_token'])]);
                }
                if (! empty($parsed['control_number'])) {
                    $query->orWhereRaw('LOWER(TRIM(COALESCE(v.control_number, \'\'))) = ?', [strtolower($parsed['control_number'])]);
                }
                if (! empty($parsed['pass_number'])) {
                    $query->orWhereRaw('LOWER(TRIM(COALESCE(v.pass_number, \'\'))) = ?', [strtolower($parsed['pass_number'])]);
                }
            });

        if (! $includeExited) {
            $query->whereNull('v.exit_time');
        }

        return $query->orderByDesc('v.entry_time')->orderByDesc('v.visit_id')->first();
    }

    protected function findVisitById(int $visitId): ?object
    {
        return DB::table('visit as v')
            ->join('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('exit_status as es', 'es.exit_status_id', '=', 'v.exit_status_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'v.primary_office_id')
            ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
            ->select(
                'v.visit_id',
                'v.entry_time',
                'v.exit_time',
                'v.exit_status_id',
                'v.qr_token',
                'v.visitor_id',
                'v.purpose_reason',
                'v.primary_office_id',
                'v.destination_text',
                'v.control_number',
                'v.pass_number',
                'vr.first_name',
                'vr.last_name',
                'vr.visitor_photo_with_id_url',
                'vr.contact_no',
                'o.office_name as primary_office_name',
                'es.exit_status_name',
                'vt.visit_type_name'
            )
            ->where('v.visit_id', $visitId)
            ->first();
    }

    public function loadRoute(int $visitId)
    {
        return DB::table('office_expectation as oe')
            ->leftJoin('office as o', 'o.office_id', '=', 'oe.office_id')
            ->leftJoin('expectation_status as xs', 'xs.expectation_status_id', '=', 'oe.expectation_status_id')
            ->where('oe.visit_id', $visitId)
            ->select([
                'oe.expectation_id',
                'oe.visit_id',
                'oe.office_id',
                'oe.expected_order',
                'oe.expectation_status_id',
                'oe.arrived_at',
                'oe.created_at',
                'o.office_name',
                'xs.status_name',
            ])
            ->orderBy('oe.expected_order')
            ->orderBy('oe.expectation_id')
            ->get();
    }

    public function resolveCurrentExpectation($route): ?object
    {
        foreach ($route as $row) {
            if (! $this->isExpectationDone($row)) {
                return $row;
            }
        }

        return null;
    }

    public function isSequentialRoute(object $visit): bool
    {
        $visitType = Str::lower(trim((string) ($visit->visit_type_name ?? '')));
        if ($visitType === 'enrollee') {
            return true;
        }

        if ($visitType === 'visitor') {
            return false;
        }

        $visitId = (int) ($visit->visit_id ?? 0);
        if ($visitId <= 0) {
            return false;
        }

        return DB::table('enrollee')->where('visit_id', $visitId)->exists();
    }

    public function resolveStaffOfficeExpectation($route, int $officeId): ?object
    {
        return $route
            ->where('office_id', $officeId)
            ->filter(fn ($row) => ! $this->isExpectationDone($row))
            ->sortBy('expected_order')
            ->first();
    }

    protected function verifyFlexibleRoute(
        object $visit,
        $route,
        object $officeContext,
        int $officeId,
        int $userId,
        string $scanMethod
    ): array {
        $staffOfficeExpectations = $route->where('office_id', $officeId)->values();
        $firstPending = $this->resolveCurrentExpectation($route);

        if (! $firstPending) {
            return $this->error('INACTIVE_VISIT', 'This visit is no longer active.', 422);
        }

        if ($staffOfficeExpectations->isEmpty()) {
            $pendingNames = $route
                ->filter(fn ($row) => ! $this->isExpectationDone($row))
                ->pluck('office_name')
                ->filter()
                ->unique()
                ->values();

            $expectedLabel = $pendingNames->isNotEmpty()
                ? $pendingNames->join(', ')
                : 'another office';

            $scanId = $this->recordFailedScan(
                $visit,
                $officeId,
                $userId,
                'Unauthorized',
                'Wrong office scan. Expected: '.$expectedLabel,
                $scanMethod
            );
            $this->createWrongOfficeAlert($visit, $officeId, $expectedLabel, $scanId);
            $this->audit('wrong_office_scan_attempt', [
                'office_id' => $officeId,
                'user_id' => $userId,
                'visit_id' => (int) $visit->visit_id,
            ]);

            return $this->error(
                'WRONG_OFFICE',
                'This visitor is not registered for your office. Expected destination'
                    .($pendingNames->count() > 1 ? 's are ' : ' is ')
                    .$expectedLabel.'.',
                422,
                [
                    'expected_office' => $expectedLabel,
                    'data' => $this->buildVisitorPayload($visit, $route, $firstPending, $officeContext),
                ]
            );
        }

        if ($staffOfficeExpectations->every(fn ($row) => $this->isExpectationDone($row))) {
            $this->recordFailedScan($visit, $officeId, $userId, 'Unauthorized', 'Duplicate office scan attempt', $scanMethod);
            $this->audit('duplicate_scan_attempt', [
                'office_id' => $officeId,
                'user_id' => $userId,
                'visit_id' => (int) $visit->visit_id,
            ]);

            return $this->error('DUPLICATE_SCAN', 'This visitor has already checked in at this office.', 409);
        }

        $target = $this->resolveStaffOfficeExpectation($route, $officeId);
        if (! $target) {
            return $this->error('DUPLICATE_SCAN', 'This visitor has already checked in at this office.', 409);
        }

        return [
            'success' => true,
            'message' => 'Visitor verified successfully.',
            'http' => 200,
            'data' => $this->buildVisitorPayload($visit, $route, $target, $officeContext),
        ];
    }

    public function isExpectationDone(object $row): bool
    {
        if (! empty($row->arrived_at)) {
            return true;
        }

        $status = Str::lower(trim((string) ($row->status_name ?? '')));
        if ($status === '') {
            return false;
        }

        foreach (['completed', 'complete', 'done', 'arrived', 'validated', 'finished', 'success', 'skip'] as $needle) {
            if (str_contains($status, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function buildVisitorPayload(object $visit, $route, ?object $current, object $officeContext): array
    {
        $fullName = trim(trim((string) ($visit->first_name ?? '')).' '.trim((string) ($visit->last_name ?? '')));
        $photoPath = trim((string) ($visit->visitor_photo_with_id_url ?? ''));
        $flexibleRoute = ! $this->isSequentialRoute($visit);
        $staffOfficeId = (int) $officeContext->office_id;

        $classified = [];
        $foundCurrent = false;
        foreach ($route as $step) {
            $done = $this->isExpectationDone($step);
            if ($done) {
                $state = 'done';
            } elseif ($flexibleRoute) {
                $state = 'current';
            } elseif (! $foundCurrent) {
                $state = 'current';
                $foundCurrent = true;
            } else {
                $state = 'pending';
            }

            $classified[] = [
                'expectation_id' => (int) $step->expectation_id,
                'office_id' => (int) $step->office_id,
                'order' => (int) $step->expected_order,
                'office_name' => (string) ($step->office_name ?? 'Office'),
                'arrived_at' => $step->arrived_at,
                'status_name' => (string) ($step->status_name ?? ''),
                'state' => $state,
            ];
        }

        $previous = null;
        if ($current) {
            $previous = collect($classified)
                ->where('order', '<', (int) $current->expected_order)
                ->sortByDesc('order')
                ->first();
        }

        $remaining = collect($classified)->whereIn('state', ['current', 'pending'])->values()->all();

        return [
            'visit' => [
                'visit_id' => (int) $visit->visit_id,
                'control_number' => trim((string) ($visit->control_number ?? '')),
                'pass_number' => trim((string) ($visit->pass_number ?? '')),
                'purpose_reason' => trim((string) ($visit->purpose_reason ?? '')),
                'destination_text' => trim((string) ($visit->destination_text ?? '')),
                'destination_display' => trim((string) ($visit->primary_office_name ?? '')) !== ''
                    ? trim((string) ($visit->primary_office_name ?? ''))
                    : (trim((string) ($visit->destination_text ?? '')) !== ''
                        ? trim((string) ($visit->destination_text ?? ''))
                        : ''),
                'entry_time' => $visit->entry_time,
                'exit_time' => $visit->exit_time,
                'exit_status' => trim((string) ($visit->exit_status_name ?? 'Active')),
                'visit_type' => trim((string) ($visit->visit_type_name ?? '')),
                'qr_token' => trim((string) ($visit->qr_token ?? '')),
            ],
            'visitor' => [
                'visitor_id' => (int) $visit->visitor_id,
                'full_name' => $fullName !== '' ? $fullName : 'Visitor',
                'first_name' => trim((string) ($visit->first_name ?? '')),
                'last_name' => trim((string) ($visit->last_name ?? '')),
                'contact_no' => trim((string) ($visit->contact_no ?? '')),
                'photo_url' => $this->resolveVisitorPhotoUrl($photoPath),
            ],
            'current_office' => [
                'office_id' => (int) ($current->office_id ?? $officeContext->office_id),
                'office_name' => (string) ($current->office_name ?? $officeContext->office_name ?? ''),
            ],
            'staff_office' => [
                'office_id' => (int) $officeContext->office_id,
                'office_name' => (string) ($officeContext->office_name ?? ''),
            ],
            'previous_office' => $previous ? [
                'office_id' => (int) $previous['office_id'],
                'office_name' => (string) $previous['office_name'],
                'arrived_at' => $previous['arrived_at'],
            ] : null,
            'remaining_route' => $remaining,
            'route' => $classified,
            'current_expectation_id' => $current ? (int) $current->expectation_id : null,
            'authorized' => $current
                && (int) $current->office_id === $staffOfficeId
                && ! $this->isExpectationDone($current),
            'flexible_route' => $flexibleRoute,
        ];
    }

    protected function findPreviousOfficeName($route, int $beforeOrder): ?string
    {
        $previous = collect($route)
            ->filter(fn ($row) => (int) $row->expected_order < $beforeOrder)
            ->sortByDesc('expected_order')
            ->first();

        return $previous ? trim((string) ($previous->office_name ?? '')) : null;
    }

    protected function recordFailedScan(object $visit, int $officeId, int $userId, string $validationName, string $remarks, string $scanMethod): ?int
    {
        try {
            $validationId = $this->resolveValidationStatusId([$validationName, 'unauthorized', 'invalid']) ?? 3;
            $scanId = DB::table('office_scan')->insertGetId([
                'visit_id' => (int) $visit->visit_id,
                'office_id' => $officeId,
                'scanned_by_user_id' => $userId,
                'scan_time' => $this->philippinesNow(),
                'validation_status_id' => $validationId,
                'remarks' => $remarks.' ['.$scanMethod.']',
            ], 'scan_id');

            $notifTypeId = $this->resolveNotifTypeId(['Wrong Office', 'Unauthorized']) ?? 1;
            DB::table('notification')->insert([
                'scan_id' => $scanId,
                'recipient_user_id' => $userId,
                'notif_type_id' => $notifTypeId,
                'message' => $remarks,
                'sent_at' => $this->philippinesNow(),
                'read_at' => null,
            ]);

            if (! str_contains(strtolower($remarks), 'wrong office')) {
                $visitorName = trim(trim((string) ($visit->first_name ?? '')).' '.trim((string) ($visit->last_name ?? ''))) ?: 'visitor';
                $officeName = DB::table('office')->where('office_id', $officeId)->value('office_name') ?: 'the office';
                ActivityLogService::log(
                    action: 'Scan Rejected',
                    module: 'Office Scanning',
                    description: ActivityLogService::actorLabel().' rejected a scan for '.$visitorName.' at '.$officeName.'.',
                    entityType: 'OfficeScan',
                    entityId: (int) $scanId,
                    status: ActivityLogService::STATUS_WARNING,
                    newValues: [
                        'visit_id' => (int) $visit->visit_id,
                        'office_id' => $officeId,
                        'remarks' => $remarks,
                    ]
                );
            }

            return (int) $scanId;
        } catch (\Throwable $e) {
            Log::warning('Failed to record office failed scan audit: '.$e->getMessage());

            return null;
        }
    }

    protected function createWrongOfficeAlert(object $visit, int $officeId, string $expectedOfficeName, ?int $scanId = null): void
    {
        try {
            $officeName = DB::table('office')->where('office_id', $officeId)->value('office_name') ?: 'Office';
            $visitorName = trim(trim((string) ($visit->first_name ?? '')).' '.trim((string) ($visit->last_name ?? '')));
            if ($visitorName === '') {
                $visitorName = 'Visitor';
            }

            DB::table('alerts')->insert([
                'visit_id' => (int) $visit->visit_id,
                'visitor_id' => (int) $visit->visitor_id,
                'scan_id' => $scanId,
                'alert_type' => 'Wrong Office',
                'severity' => 'Medium',
                'message' => $visitorName.' checked in at '.$officeName.' but was expected at '.$expectedOfficeName.'.',
                'status' => 'Unresolved',
                'created_at' => $this->philippinesNow(),
            ]);

            $alertId = DB::table('alerts')
                ->where('visit_id', (int) $visit->visit_id)
                ->where('scan_id', $scanId)
                ->orderByDesc('alert_id')
                ->value('alert_id');

            ActivityLogService::log(
                action: 'Alert Generated',
                module: 'Alerts',
                description: 'Unauthorized visitor alert generated for '.$visitorName.'.',
                entityType: 'Alert',
                entityId: $alertId ? (int) $alertId : null,
                newValues: [
                    'alert_type' => 'Wrong Office',
                    'severity' => 'Medium',
                    'visitor_name' => $visitorName,
                    'visit_id' => (int) $visit->visit_id,
                    'scanned_office' => $officeName,
                    'expected_office' => $expectedOfficeName,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to create wrong-office alert: '.$e->getMessage());
        }
    }

    protected function advanceEnrolleeProgress(int $visitId, int $officeId, int $expectedOrder, Carbon $now): void
    {
        try {
            $enrollee = DB::table('enrollee as e')
                ->join('visit as v', 'v.visitor_id', '=', 'e.visitor_id')
                ->where('v.visit_id', $visitId)
                ->select('e.enrollee_id')
                ->first();

            if (! $enrollee) {
                return;
            }

            $step = DB::table('enrollee_step')
                ->where('office_id', $officeId)
                ->where('step_order', $expectedOrder)
                ->where('is_active', true)
                ->first();

            if (! $step) {
                $step = DB::table('enrollee_step')
                    ->where('office_id', $officeId)
                    ->where('is_active', true)
                    ->orderBy('step_order')
                    ->first();
            }

            if (! $step) {
                return;
            }

            $completedStepStatusId = $this->resolveStepStatusId(['done', 'completed', 'complete']) ?? 2;
            $pendingStepStatusId = $this->resolveStepStatusId(['pending', 'ongoing', 'in progress', 'in_progress']) ?? 1;

            $existing = DB::table('enrollee_progress')
                ->where('enrollee_id', (int) $enrollee->enrollee_id)
                ->where('step_id', (int) $step->step_id)
                ->first();

            if ($existing) {
                DB::table('enrollee_progress')
                    ->where('progress_id', (int) $existing->progress_id)
                    ->update([
                        'step_status_id' => $completedStepStatusId,
                        'completed_at' => $now,
                    ]);
            } else {
                DB::table('enrollee_progress')->insert([
                    'enrollee_id' => (int) $enrollee->enrollee_id,
                    'step_id' => (int) $step->step_id,
                    'step_status_id' => $completedStepStatusId,
                    'completed_at' => $now,
                ]);
            }

            $totalSteps = (int) DB::table('enrollee_step')->where('is_active', true)->count();
            $doneSteps = (int) DB::table('enrollee_progress')
                ->where('enrollee_id', (int) $enrollee->enrollee_id)
                ->whereNotNull('completed_at')
                ->count();

            $statusName = $doneSteps >= $totalSteps && $totalSteps > 0 ? 'COMPLETED' : 'ONGOING';
            $statusId = DB::table('enrollee_status')
                ->whereRaw('LOWER(TRIM(COALESCE(status_name, \'\'))) = ?', [strtolower($statusName)])
                ->value('enrollee_status_id');

            if ($statusId) {
                DB::table('enrollee')
                    ->where('enrollee_id', (int) $enrollee->enrollee_id)
                    ->update([
                        'enrollee_status_id' => $statusId,
                        'updated_at' => $now,
                    ]);
            }

            $visitorName = DB::table('visit as v')
                ->join('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
                ->where('v.visit_id', $visitId)
                ->selectRaw("TRIM(CONCAT(COALESCE(vr.first_name, ''), ' ', COALESCE(vr.last_name, ''))) as visitor_name")
                ->value('visitor_name') ?: 'enrollee';
            $officeName = DB::table('office')->where('office_id', $officeId)->value('office_name') ?: 'office verification';
            $stepName = trim((string) ($step->step_name ?? $step->step_label ?? $officeName));

            ActivityLogService::log(
                action: 'Enrollee Progress Updated',
                module: 'Enrollee Processing',
                description: ActivityLogService::actorLabel().' marked '.$stepName.' as Completed for enrollee '.$visitorName.'.',
                entityType: 'Enrollee',
                entityId: (int) $enrollee->enrollee_id,
                oldValues: ['step_status' => 'Pending'],
                newValues: [
                    'step_status' => 'Completed',
                    'office_id' => $officeId,
                    'office_name' => $officeName,
                    'visit_id' => $visitId,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Unable to advance enrollee progress: '.$e->getMessage());
        }
    }

    protected function resolveStepStatusId(array $names): ?int
    {
        foreach ($names as $name) {
            $normalized = strtolower(trim($name));

            foreach (['step_status_name', 'status_name'] as $column) {
                if (! Schema::hasColumn('step_status', $column)) {
                    continue;
                }

                $id = DB::table('step_status')
                    ->whereRaw("LOWER(TRIM(COALESCE({$column}, ''))) = ?", [$normalized])
                    ->value('step_status_id');

                if ($id) {
                    return (int) $id;
                }
            }
        }

        return null;
    }

    protected function resolveExpectationStatusId(array $names): ?int
    {
        foreach ($names as $name) {
            $id = DB::table('expectation_status')
                ->whereRaw('LOWER(TRIM(COALESCE(status_name, \'\'))) = ?', [strtolower(trim($name))])
                ->value('expectation_status_id');
            if ($id) {
                return (int) $id;
            }
        }

        return null;
    }

    protected function resolveValidationStatusId(array $names): ?int
    {
        foreach ($names as $name) {
            $id = DB::table('validation_status')
                ->whereRaw('LOWER(TRIM(COALESCE(status_name, \'\'))) = ?', [strtolower(trim($name))])
                ->value('validation_status_id');
            if ($id) {
                return (int) $id;
            }
        }

        return null;
    }

    protected function resolveNotifTypeId(array $names): ?int
    {
        foreach ($names as $name) {
            $id = DB::table('notif_type')
                ->whereRaw('LOWER(TRIM(COALESCE(notif_type_name, \'\'))) = ?', [strtolower(trim($name))])
                ->value('notif_type_id');
            if ($id) {
                return (int) $id;
            }
        }

        return null;
    }

    protected function parseStorageObjectPath(string $rawPath): array
    {
        $path = trim($rawPath);
        if ($path === '') {
            return [null, null];
        }

        $path = preg_replace('#^https?://[^/]+/#i', '', $path) ?? $path;
        $path = ltrim($path, '/');

        foreach (['storage/v1/object/public/', 'storage/v1/object/sign/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
                $path = explode('?', $path, 2)[0] ?? $path;
            }
        }

        $segments = array_values(array_filter(explode('/', $path), fn ($s) => $s !== ''));
        if (count($segments) < 2) {
            $bucket = (string) env('SUPABASE_STORAGE_BUCKET', 'visitor-file');

            return [$bucket, $path];
        }

        $bucket = array_shift($segments);

        return [$bucket, implode('/', $segments)];
    }

    protected function normalizeNullableString($value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }

    protected function philippinesNow(): Carbon
    {
        return Carbon::now('Asia/Manila');
    }

    protected function error(string $code, string $message, int $http, array $extra = []): array
    {
        return array_merge([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'http' => $http,
        ], $extra);
    }

    protected function audit(string $action, array $context = []): void
    {
        Log::info('office_portal.'.$action, array_merge($context, [
            'ip' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 255),
            'timestamp' => $this->philippinesNow()->toDateTimeString(),
        ]));
    }
}
