<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GuardDashboardController extends Controller
{
    public function index()
    {
        $activeVisitorsCount = DB::table('visit')
            ->whereNull('exit_time')
            ->count();

        $unresolvedAlertsCount = DB::table('alerts')
            ->whereRaw('LOWER(TRIM(COALESCE(status, \'\'))) = ?', ['unresolved'])
            ->count();

        $exitedTodayCount = DB::table('visit')
            ->whereDate('exit_time', today())
            ->count();

        $entriesTodayCount = DB::table('visit')
            ->whereDate('entry_time', today())
            ->count();

        $pendingExitScansCount = DB::table('visit as v')
            ->leftJoin('exit_status as es', 'es.exit_status_id', '=', 'v.exit_status_id')
            ->leftJoin('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->whereNull('v.exit_time')
            ->whereRaw('LOWER(TRIM(COALESCE(es.exit_status_name, \'\'))) = ?', ['ready to exit'])
            ->count('v.visit_id');

        $resolvedTodayCount = DB::table('alerts')
            ->whereRaw('LOWER(TRIM(COALESCE(status, \'\'))) = ?', ['resolved'])
            ->whereDate('resolved_at', today())
            ->count();

        $latestScanPerVisit = DB::table('office_scan as os')
            ->select('os.visit_id', DB::raw('MAX(os.scan_id) as latest_scan_id'))
            ->groupBy('os.visit_id');

        $latestUnresolvedAlertPerVisit = DB::table('alerts as al')
            ->select('al.visit_id', DB::raw('MAX(al.alert_id) as latest_unresolved_alert_id'))
            ->whereRaw('LOWER(TRIM(COALESCE(al.status, \'\'))) = ?', ['unresolved'])
            ->groupBy('al.visit_id');

        $activeVisitorsRows = DB::table('visit as v')
            ->leftJoin('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'v.primary_office_id')
            ->leftJoin('exit_status as es', 'es.exit_status_id', '=', 'v.exit_status_id')
            ->leftJoinSub($latestScanPerVisit, 'latest_scan', function ($join) {
                $join->on('latest_scan.visit_id', '=', 'v.visit_id');
            })
            ->leftJoin('office_scan as os', 'os.scan_id', '=', 'latest_scan.latest_scan_id')
            ->leftJoin('validation_status as vs', 'vs.validation_status_id', '=', 'os.validation_status_id')
            ->leftJoinSub($latestUnresolvedAlertPerVisit, 'latest_alert', function ($join) {
                $join->on('latest_alert.visit_id', '=', 'v.visit_id');
            })
            ->leftJoin('alerts as la', 'la.alert_id', '=', 'latest_alert.latest_unresolved_alert_id')
            ->whereNull('v.exit_time')
            ->select([
                'v.visit_id',
                'v.entry_time',
                'v.duration_minutes',
                'vr.first_name',
                'vr.last_name',
                'vr.pass_number',
                'vr.control_number',
                'o.office_name',
                'es.exit_status_name',
                'vs.status_name as validation_status_name',
                'la.alert_type as unresolved_alert_type',
            ])
            ->orderByDesc('v.entry_time')
            ->orderByDesc('v.visit_id')
            ->limit(100)
            ->get();

        $activeVisitors = $activeVisitorsRows->map(function ($row) {
            $firstName = trim((string) ($row->first_name ?? ''));
            $lastName = trim((string) ($row->last_name ?? ''));
            $visitorName = trim($firstName . ' ' . $lastName);
            $visitorName = $visitorName !== '' ? $visitorName : 'Unknown Visitor';

            $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
            if ($initials === '') {
                $initials = 'NA';
            }

            $passNumber = trim((string) ($row->pass_number ?? ''));
            if ($passNumber === '') {
                $passNumber = trim((string) ($row->control_number ?? ''));
            }
            if ($passNumber === '') {
                $passNumber = 'No pass/control number';
            }

            $entryTime = '—';
            try {
                if (!empty($row->entry_time)) {
                    $entryTime = Carbon::parse($row->entry_time)->format('h:i A');
                }
            } catch (\Throwable $e) {
                $entryTime = '—';
            }

            $durationMinutes = $row->duration_minutes !== null ? (int) $row->duration_minutes : null;
            if ($durationMinutes === null) {
                try {
                    if (!empty($row->entry_time)) {
                        $durationMinutes = max(0, Carbon::parse($row->entry_time)->diffInMinutes(now()));
                    }
                } catch (\Throwable $e) {
                    $durationMinutes = null;
                }
            }

            $durationLabel = '—';
            if ($durationMinutes !== null) {
                $hours = intdiv($durationMinutes, 60);
                $minutes = $durationMinutes % 60;
                if ($hours > 0) {
                    $durationLabel = $hours . ' hr' . ($hours > 1 ? 's' : '');
                    if ($minutes > 0) {
                        $durationLabel .= ' ' . $minutes . ' min' . ($minutes > 1 ? 's' : '');
                    }
                } else {
                    $durationLabel = $minutes . ' min' . ($minutes === 1 ? '' : 's');
                }
            }

            $exitStatus = strtolower(trim((string) ($row->exit_status_name ?? '')));
            $validationStatus = strtolower(trim((string) ($row->validation_status_name ?? '')));
            $alertType = trim((string) ($row->unresolved_alert_type ?? ''));
            $alertLabel = $alertType !== '' ? ucwords(strtolower($alertType)) : 'None';

            $statusLabel = 'Arrived';
            $statusClass = 'arrived';

            if ($exitStatus === 'ready to exit') {
                $statusLabel = 'Ready to Exit';
                $statusClass = 'exit';
            } elseif (str_contains($validationStatus, 'transit')) {
                $statusLabel = 'In Transit';
                $statusClass = 'transit';
            }

            return [
                'visit_id' => (int) ($row->visit_id ?? 0),
                'initials' => $initials,
                'visitor_name' => $visitorName,
                'pass_number' => $passNumber,
                'office_name' => trim((string) ($row->office_name ?? '')) ?: 'No destination',
                'entry_time' => $entryTime,
                'duration' => $durationLabel,
                'status_label' => $statusLabel,
                'status_class' => $statusClass,
                'alert' => $alertLabel,
            ];
        })->values();

        return view('guard.dashboard', [
            'activeVisitorsCount' => $activeVisitorsCount,
            'activeAlertsCount' => $unresolvedAlertsCount,
            'unresolvedAlertsCount' => $unresolvedAlertsCount,
            'exitedTodayCount' => $exitedTodayCount,
            'entriesTodayCount' => $entriesTodayCount,
            'pendingExitScansCount' => $pendingExitScansCount,
            'resolvedTodayCount' => $resolvedTodayCount,
            'activeVisitors' => $activeVisitors,
        ]);
    }

    public function visitDetails(int $visitId)
    {
        $visit = DB::table('visit as v')
            ->leftJoin('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('address as a', 'a.address_id', '=', 'vr.address_id')
            ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
            ->leftJoin('office as po', 'po.office_id', '=', 'v.primary_office_id')
            ->leftJoin('exit_status as es', 'es.exit_status_id', '=', 'v.exit_status_id')
            ->leftJoin('users as ru', 'ru.user_id', '=', 'v.guard_user_id')
            ->where('v.visit_id', $visitId)
            ->select([
                'v.visit_id',
                'v.visitor_id',
                'v.entry_time',
                'v.exit_time',
                'v.duration_minutes',
                'v.purpose_reason',
                'v.destination_text',
                'vr.first_name',
                'vr.last_name',
                'vr.pass_number',
                'vr.control_number',
                'vr.contact_no',
                'vr.visitor_photo_with_id_url',
                'a.house_no',
                'a.street',
                'a.barangay',
                'a.city_municipality',
                'a.province',
                'vt.visit_type_name',
                'po.office_name as primary_office_name',
                'es.exit_status_name',
                'ru.first_name as registered_by_first_name',
                'ru.last_name as registered_by_last_name',
            ])
            ->first();

        if (! $visit) {
            return response()->json(['message' => 'Visit not found.'], 404);
        }

        $expectedRoute = DB::table('office_expectation as oe')
            ->leftJoin('office as o', 'o.office_id', '=', 'oe.office_id')
            ->leftJoin('expectation_status as xs', 'xs.expectation_status_id', '=', 'oe.expectation_status_id')
            ->where('oe.visit_id', $visitId)
            ->select([
                'oe.expected_order',
                'o.office_name',
                'xs.status_name as expectation_status_name',
                'oe.arrived_at',
            ])
            ->orderBy('oe.expected_order')
            ->get();

        $scans = DB::table('office_scan as os')
            ->leftJoin('office as o', 'o.office_id', '=', 'os.office_id')
            ->leftJoin('users as u', 'u.user_id', '=', 'os.scanned_by_user_id')
            ->leftJoin('validation_status as vs', 'vs.validation_status_id', '=', 'os.validation_status_id')
            ->where('os.visit_id', $visitId)
            ->select([
                'os.scan_id',
                'o.office_name',
                'os.scan_time',
                'os.remarks',
                'vs.status_name as validation_status_name',
                'u.first_name as scanned_by_first_name',
                'u.last_name as scanned_by_last_name',
            ])
            ->orderByDesc('os.scan_time')
            ->orderByDesc('os.scan_id')
            ->get();

        $latestAlert = DB::table('alerts as al')
            ->leftJoin('users as ru', 'ru.user_id', '=', 'al.resolved_by')
            ->where('al.visit_id', $visitId)
            ->select([
                'al.alert_id',
                'al.alert_type',
                'al.severity',
                'al.message',
                'al.status',
                'al.created_at',
                'al.resolved_at',
                'al.resolution_notes',
                'ru.first_name as resolved_by_first_name',
                'ru.last_name as resolved_by_last_name',
            ])
            ->orderByDesc('al.alert_id')
            ->first();

        $addressParts = array_filter([
            trim((string) ($visit->house_no ?? '')),
            trim((string) ($visit->street ?? '')),
            trim((string) ($visit->barangay ?? '')),
            trim((string) ($visit->city_municipality ?? '')),
            trim((string) ($visit->province ?? '')),
        ], fn ($value) => $value !== '');

        $durationMinutes = $visit->duration_minutes !== null ? (int) $visit->duration_minutes : null;
        if ($durationMinutes === null && !empty($visit->entry_time)) {
            try {
                $endTime = !empty($visit->exit_time) ? Carbon::parse($visit->exit_time) : now();
                $durationMinutes = max(0, Carbon::parse($visit->entry_time)->diffInMinutes($endTime));
            } catch (\Throwable $e) {
                $durationMinutes = null;
            }
        }

        $formatMinutes = function (?int $minutes): string {
            if ($minutes === null) {
                return '—';
            }
            $hours = intdiv($minutes, 60);
            $mins = $minutes % 60;
            if ($hours > 0) {
                return $hours . ' hr' . ($hours > 1 ? 's' : '') . ($mins > 0 ? ' ' . $mins . ' min' . ($mins > 1 ? 's' : '') : '');
            }

            return $mins . ' min' . ($mins === 1 ? '' : 's');
        };

        $statusLabel = trim((string) ($visit->exit_status_name ?? ''));
        if ($statusLabel === '') {
            $statusLabel = empty($visit->exit_time) ? 'Still Inside' : 'Exited';
        }

        return response()->json([
            'visitor' => [
                'visitor_id' => (int) ($visit->visitor_id ?? 0),
                'name' => trim(((string) ($visit->first_name ?? '')) . ' ' . ((string) ($visit->last_name ?? ''))) ?: 'Unknown Visitor',
                'pass_number' => trim((string) ($visit->pass_number ?? '')) ?: '—',
                'control_number' => trim((string) ($visit->control_number ?? '')) ?: '—',
                'contact_no' => trim((string) ($visit->contact_no ?? '')) ?: '—',
                'photo_url' => $this->resolveVisitorPhotoUrl((string) ($visit->visitor_photo_with_id_url ?? '')),
                'address' => count($addressParts) > 0 ? implode(', ', $addressParts) : '—',
                'status' => $statusLabel,
            ],
            'visit' => [
                'visit_id' => (int) ($visit->visit_id ?? 0),
                'visit_type' => trim((string) ($visit->visit_type_name ?? '')) ?: '—',
                'purpose_reason' => trim((string) ($visit->purpose_reason ?? '')) ?: '—',
                'primary_office' => trim((string) ($visit->primary_office_name ?? '')) ?: '—',
                'entry_time' => $visit->entry_time,
                'exit_time' => $visit->exit_time,
                'duration_minutes' => $durationMinutes,
                'duration_label' => $formatMinutes($durationMinutes),
                'exit_status' => $statusLabel,
                'registered_by' => trim(((string) ($visit->registered_by_first_name ?? '')) . ' ' . ((string) ($visit->registered_by_last_name ?? ''))) ?: '—',
                'destination_text' => trim((string) ($visit->destination_text ?? '')) ?: '—',
            ],
            'expected_route' => $expectedRoute->map(function ($row) {
                return [
                    'expected_order' => $row->expected_order !== null ? (int) $row->expected_order : null,
                    'office_name' => trim((string) ($row->office_name ?? '')) ?: 'Unknown Office',
                    'status' => trim((string) ($row->expectation_status_name ?? '')) ?: 'Pending',
                    'arrived_at' => $row->arrived_at,
                ];
            })->values(),
            'scans' => $scans->map(function ($row) {
                return [
                    'scan_id' => (int) ($row->scan_id ?? 0),
                    'office_name' => trim((string) ($row->office_name ?? '')) ?: 'Unknown Office',
                    'scan_time' => $row->scan_time,
                    'validation_status' => trim((string) ($row->validation_status_name ?? '')) ?: 'Unknown',
                    'scanned_by' => trim(((string) ($row->scanned_by_first_name ?? '')) . ' ' . ((string) ($row->scanned_by_last_name ?? ''))) ?: 'Unknown scanner',
                    'remarks' => trim((string) ($row->remarks ?? '')) ?: '—',
                ];
            })->values(),
            'alert' => $latestAlert ? [
                'alert_id' => (int) ($latestAlert->alert_id ?? 0),
                'alert_type' => trim((string) ($latestAlert->alert_type ?? '')) ?: 'General Alert',
                'severity' => trim((string) ($latestAlert->severity ?? '')) ?: 'Medium',
                'message' => trim((string) ($latestAlert->message ?? '')) ?: '—',
                'status' => trim((string) ($latestAlert->status ?? '')) ?: 'Unresolved',
                'created_at' => $latestAlert->created_at,
                'resolved_at' => $latestAlert->resolved_at,
                'resolved_by' => trim(((string) ($latestAlert->resolved_by_first_name ?? '')) . ' ' . ((string) ($latestAlert->resolved_by_last_name ?? ''))) ?: '—',
                'resolution_notes' => trim((string) ($latestAlert->resolution_notes ?? '')) ?: '—',
            ] : null,
        ]);
    }

    protected function resolveVisitorPhotoUrl(string $photoPath): ?string
    {
        $cleanPath = trim($photoPath);
        if ($cleanPath === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $cleanPath) === 1) {
            return $cleanPath;
        }

        if (str_starts_with($cleanPath, '/storage/') || str_starts_with($cleanPath, 'storage/')) {
            return url('/' . ltrim($cleanPath, '/'));
        }

        [$bucket, $objectPath] = $this->parseStorageObjectPathForPreview($cleanPath);
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
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->timeout(20)->post($supabaseUrl . '/storage/v1/object/sign/' . $encodedBucket . '/' . $encodedObjectPath, [
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
                            return $supabaseUrl . '/' . $signedPath;
                        }
                        if (Str::startsWith($signedPath, 'object/')) {
                            return $supabaseUrl . '/storage/v1/' . $signedPath;
                        }

                        return $supabaseUrl . '/' . $signedPath;
                    }
                }
            } catch (\Throwable $e) {
                logger()->warning('Unable to sign visitor preview URL: ' . $e->getMessage());
            }
        }

        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $objectPath)));
        return $supabaseUrl . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . $encodedPath;
    }

    protected function parseStorageObjectPathForPreview(string $rawPath): array
    {
        $path = trim($rawPath);
        if ($path === '') {
            return [null, null];
        }

        $path = preg_replace('#^https?://[^/]+/#i', '', $path) ?? $path;
        $path = ltrim($path, '/');

        $publicPrefix = 'storage/v1/object/public/';
        if (Str::startsWith($path, $publicPrefix)) {
            $path = Str::after($path, $publicPrefix);
        }

        $signPrefix = 'storage/v1/object/sign/';
        if (Str::startsWith($path, $signPrefix)) {
            $path = Str::after($path, $signPrefix);
            $path = strtok($path, '?') ?: $path;
        }

        $segments = array_values(array_filter(explode('/', $path), fn ($segment) => $segment !== ''));
        if (count($segments) < 2) {
            return [null, null];
        }

        $bucket = array_shift($segments);
        $objectPath = implode('/', $segments);

        return [$bucket, $objectPath];
    }
}
