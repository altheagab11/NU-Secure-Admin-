<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AlertsController extends Controller
{
    /**
     * Display a listing of alerts fetched from Supabase (PostgREST).
     *
     * Note: This implementation assumes Supabase REST relationships are named
     * `visitor`, `visit`, `office_scan`, and `resolved_by` and that `office`
     * is nested under `visit` and `office_scan` as shown in the mapping image.
     * If your relationship names differ, adjust the $select string accordingly.
     */
    public function index(Request $request)
    {
        $supabaseUrl = env('SUPABASE_URL');
        // prefer SERVICE_ROLE key if present , fall back to SUPABASE_KEY
        $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_KEY');

        $search = trim((string) $request->query('search', ''));
        $alertTypeFilter = trim((string) $request->query('alert_type', ''));
        $severityFilter = trim((string) $request->query('severity', ''));
        $statusFilter = trim((string) $request->query('status', ''));
        $officeFilter = trim((string) $request->query('office', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        $hasActiveFilters = $search !== ''
            || $alertTypeFilter !== ''
            || $severityFilter !== ''
            || $statusFilter !== ''
            || $officeFilter !== ''
            || $dateFrom !== ''
            || $dateTo !== '';

        // Safe defaults for the view
        $alertsRaw = [];
        $alerts = [];
        $total = 0;
        $resolvedCount = 0;
        $unresolvedCount = 0;
        $criticalCount = 0;
        $alertTypeOptions = [];
        $severityOptions = [];
        $statusOptions = [];
        $officeOptions = [];

        if ($supabaseUrl && $supabaseKey) {
            // Try to fetch alerts with related data. Adjust this select if your
            // PostgREST relationships have different names.
            $select = 'alert_id,created_at,alert_type,severity,status,resolved_at,resolution_notes,' .
                // visitor fields
                'visitor(first_name,last_name,pass_number,control_number,contact_no),' .
                // visit fields + visit_type + primary office mapping
                'visit(visit_id,purpose_reason,entry_time,exit_time,duration_minutes,primary_office_id,visit_type(visit_type_name),office(office_name)),' .
                // office_scan fields + office + scanned_by user + validation status
                'office_scan(scan_id,scan_time,remarks,office(office_name),users(first_name,last_name),validation_status(status_name)),' .
                // resolved_by user
                'resolved_by(first_name,last_name)';

            try {
                $response = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get(rtrim($supabaseUrl, '/') . '/rest/v1/alerts', [
                    'select' => $select,
                    'order' => 'alert_id.desc,created_at.desc',
                ]);

                if ($response->ok()) {
                    $alertsRaw = is_array($response->json()) ? $response->json() : [];

                    $total = count($alertsRaw);
                    $resolvedCount = count(array_filter($alertsRaw, function ($a) {
                        return (isset($a['status']) && strtolower($a['status']) === 'resolved');
                    }));
                    $unresolvedCount = $total - $resolvedCount;
                    $criticalCount = count(array_filter($alertsRaw, function ($a) {
                        return (isset($a['severity']) && strtolower($a['severity']) === 'critical');
                    }));

                    $alertTypeOptions = collect($alertsRaw)
                        ->pluck('alert_type')
                        ->filter(fn ($x) => filled($x))
                        ->map(fn ($x) => (string) $x)
                        ->unique()
                        ->sort()
                        ->values()
                        ->all();

                    $severityOptions = collect($alertsRaw)
                        ->pluck('severity')
                        ->filter(fn ($x) => filled($x))
                        ->map(fn ($x) => (string) $x)
                        ->unique()
                        ->sort()
                        ->values()
                        ->all();

                    $statusOptions = collect($alertsRaw)
                        ->pluck('status')
                        ->filter(fn ($x) => filled($x))
                        ->map(fn ($x) => (string) $x)
                        ->unique()
                        ->sort()
                        ->values()
                        ->all();

                    $officeOptions = collect($alertsRaw)
                        ->flatMap(function ($alert) {
                            $expectedOffice = $this->extractOfficeName($alert['visit']['office'] ?? null);
                            $scannedOffice = $this->extractOfficeName($alert['office_scan']['office'] ?? null);
                            return array_values(array_filter([$expectedOffice, $scannedOffice], fn ($x) => filled($x)));
                        })
                        ->unique()
                        ->sort()
                        ->values()
                        ->all();

                    $alerts = collect($alertsRaw)->filter(function ($alert) use (
                        $search,
                        $alertTypeFilter,
                        $severityFilter,
                        $statusFilter,
                        $officeFilter,
                        $dateFrom,
                        $dateTo
                    ) {
                        $visitor = $this->firstRelation($alert['visitor'] ?? null);
                        $visit = $this->firstRelation($alert['visit'] ?? null);
                        $visitOffice = $this->extractOfficeName($visit['office'] ?? null);
                        $scan = $this->firstRelation($alert['office_scan'] ?? null);
                        $scanOffice = $this->extractOfficeName($scan['office'] ?? null);

                        $visitorName = trim(((string) ($visitor['first_name'] ?? '')) . ' ' . ((string) ($visitor['last_name'] ?? '')));
                        $passNumber = (string) ($visitor['pass_number'] ?? '');
                        $controlNumber = (string) ($visitor['control_number'] ?? '');

                        $matchesSearch = true;
                        if ($search !== '') {
                            $haystack = Str::lower(implode(' ', [
                                $visitorName,
                                $passNumber,
                                $controlNumber,
                            ]));
                            $matchesSearch = Str::contains($haystack, Str::lower($search));
                        }

                        $matchesAlertType = $alertTypeFilter === '' || strcasecmp((string) ($alert['alert_type'] ?? ''), $alertTypeFilter) === 0;
                        $matchesSeverity = $severityFilter === '' || strcasecmp((string) ($alert['severity'] ?? ''), $severityFilter) === 0;
                        $matchesStatus = $statusFilter === '' || strcasecmp((string) ($alert['status'] ?? ''), $statusFilter) === 0;

                        $matchesOffice = true;
                        if ($officeFilter !== '') {
                            $matchesOffice = strcasecmp((string) $visitOffice, $officeFilter) === 0
                                || strcasecmp((string) $scanOffice, $officeFilter) === 0;
                        }

                        $createdAt = $alert['created_at'] ?? null;
                        $createdDate = null;
                        try {
                            if ($createdAt) {
                                $createdDate = Carbon::parse($createdAt)->toDateString();
                            }
                        } catch (\Throwable $e) {
                            $createdDate = null;
                        }

                        $matchesDateFrom = true;
                        if ($dateFrom !== '') {
                            $matchesDateFrom = $createdDate !== null && $createdDate >= $dateFrom;
                        }

                        $matchesDateTo = true;
                        if ($dateTo !== '') {
                            $matchesDateTo = $createdDate !== null && $createdDate <= $dateTo;
                        }

                        return $matchesSearch
                            && $matchesAlertType
                            && $matchesSeverity
                            && $matchesStatus
                            && $matchesOffice
                            && $matchesDateFrom
                            && $matchesDateTo;
                    })->sortByDesc(fn ($alert) => (int) ($alert['alert_id'] ?? 0))
                        ->values()
                        ->all();
                } else {
                    // Log non-200 response to help debugging (will appear in storage/logs)
                    logger()->warning('Supabase alerts fetch returned non-OK status', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                // Network or other HTTP client error — keep defaults and log
                logger()->error('Supabase fetch failed: ' . $e->getMessage());
            }
        }

        return view('admin.alert', [
            'alerts' => $alerts,
            'total' => $total,
            'resolvedCount' => $resolvedCount,
            'unresolvedCount' => $unresolvedCount,
            'criticalCount' => $criticalCount,
            'alertTypeOptions' => $alertTypeOptions,
            'severityOptions' => $severityOptions,
            'statusOptions' => $statusOptions,
            'officeOptions' => $officeOptions,
            'filters' => [
                'search' => $search,
                'alert_type' => $alertTypeFilter,
                'severity' => $severityFilter,
                'status' => $statusFilter,
                'office' => $officeFilter,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'hasActiveFilters' => $hasActiveFilters,
        ]);
    }

    private function firstRelation($value): array
    {
        if (is_array($value) && isset($value[0]) && is_array($value[0])) {
            return $value[0];
        }

        return is_array($value) ? $value : [];
    }

    private function extractOfficeName($officeRelation): string
    {
        $office = $this->firstRelation($officeRelation);
        return trim((string) ($office['office_name'] ?? ''));
    }

    /**
     * Resolve an alert and persist resolution details to Supabase.
     */
    public function resolve(Request $request, $alertId)
    {
        $validated = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:2000'],
        ]);

        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_KEY');

        if (!$supabaseUrl || !$supabaseKey) {
            return response()->json([
                'message' => 'Supabase configuration is missing.',
            ], 500);
        }

        $authUser = Auth::user();
        $resolvedById = $authUser->user_id ?? $authUser->id ?? session('user_id') ?? null;
    // if (!$resolvedById) {
    //         return response()->json([
    //             'message' => 'Unable to resolve alert: no logged-in user found.',
    //         ], 422);
    //     }
        $resolvedAt = now()->toIso8601String();

        try {
            $response = Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'Prefer' => 'return=representation',
            ])->withQueryParameters([
                'alert_id' => 'eq.' . $alertId,
                'select' => 'alert_id,status,resolved_at,resolution_notes,resolved_by(first_name,last_name)',
            ])->patch(rtrim($supabaseUrl, '/') . '/rest/v1/alerts', [
                'status' => 'Resolved',
                'resolved_at' => $resolvedAt,
                'resolved_by' => $resolvedById,
                'resolution_notes' => $validated['resolution_notes'],
            ]);

            if (!$response->ok()) {
                logger()->warning('Supabase resolve update failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'alert_id' => $alertId,
                ]);

                return response()->json([
                    'message' => 'Failed to save resolution to Supabase.',
                ], 500);
            }

            $updated = $response->json();
            $updatedAlert = is_array($updated) && array_key_exists(0, $updated) ? $updated[0] : $updated;

            return response()->json([
                'message' => 'Alert resolved successfully.',
                'alert' => $updatedAlert,
            ]);
        } catch (\Exception $e) {
            logger()->error('Supabase resolve request failed: ' . $e->getMessage(), [
                'alert_id' => $alertId,
            ]);

            return response()->json([
                'message' => 'Unable to resolve alert at the moment.',
            ], 500);
        }
    }
}
