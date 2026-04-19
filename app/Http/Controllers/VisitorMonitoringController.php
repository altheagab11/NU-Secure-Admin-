<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VisitorMonitoringController extends Controller
{
    public function index(Request $request)
    {
        [$rows, $fetchError] = $this->loadRowsAndFetchError();
        $filters = $this->extractFilters($request);

        $officeOptions = $rows
            ->pluck('destination')
            ->filter(fn ($v) => filled($v))
            ->unique()
            ->sort()
            ->values();

        $statusOptions = collect(['Arrived', 'In Transit', 'Completed', 'Overstay']);
        $visitTypeOptions = $rows
            ->pluck('visit_type')
            ->filter(fn ($v) => filled($v) && $v !== '—')
            ->unique()
            ->sort()
            ->values();

        $filteredRows = $this->applyFilters($rows, $filters)->values();

        $perPage = 10;
        $currentPage = max(1, (int) $request->query('page', 1));
        $filteredCount = $filteredRows->count();

        $todayDate = Carbon::today()->toDateString();
        $totalTodayCount = $filteredRows
            ->filter(fn (array $row) => ($row['entry_date_value'] ?? null) === $todayDate)
            ->count();

        if ($totalTodayCount === 0) {
            $latestEntryDate = $filteredRows
                ->pluck('entry_date_value')
                ->filter(fn ($value) => filled($value))
                ->sortDesc()
                ->first();

            if (filled($latestEntryDate)) {
                $totalTodayCount = $filteredRows
                    ->filter(fn (array $row) => ($row['entry_date_value'] ?? null) === $latestEntryDate)
                    ->count();
            }
        }

        $activeCount = $filteredRows
            ->filter(fn (array $row) => in_array(($row['status'] ?? ''), ['Arrived', 'In Transit', 'Overstay'], true))
            ->count();

        $completedCount = $filteredRows
            ->filter(fn (array $row) => ($row['status'] ?? '') === 'Completed')
            ->count();

        $alertsCount = $filteredRows
            ->filter(function (array $row) {
                $alert = trim((string) ($row['alert'] ?? ''));

                return $alert !== '' && Str::lower($alert) !== 'none';
            })
            ->count();

        $paginatedRows = new LengthAwarePaginator(
            $filteredRows->forPage($currentPage, $perPage)->values(),
            $filteredCount,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $activeRows = $filteredRows->filter(fn ($r) => ($r['status'] ?? '') !== 'Completed');

        $activeByOffice = $activeRows
            ->groupBy(fn ($r) => $r['destination'] ?: 'Unknown Office')
            ->map(fn (Collection $group, string $office) => [
                'office_name' => $office,
                'count' => $group->count(),
            ])
            ->values()
            ->sortByDesc('count')
            ->values();

        $maxOfficeCount = max(1, (int) $activeByOffice->max('count'));

        $recentVisitors = $filteredRows
            ->take(3)
            ->map(fn ($row) => [
                'visitor_name' => $row['visitor_name'],
                'destination' => $row['destination'],
                'time_label' => $row['entry_time_label_short'],
                'status' => $row['status'],
                'status_class' => $row['status_class'],
            ]);

        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_KEY');

        $correctOfficeScans = collect([]);
        if ($supabaseUrl && $supabaseKey) {
            try {
                $correctOfficeScans = $this->fetchCorrectOfficeScans($supabaseUrl, $supabaseKey);
            } catch (\Throwable $e) {
                logger()->warning('Correct office scan fetch failed: '.$e->getMessage());
            }
        }

        if ($correctOfficeScans->isEmpty()) {
            // Safe fallback so card does not go blank when source tables are unavailable.
            $correctOfficeScans = $filteredRows
                ->filter(fn ($row) => ! in_array($row['status'], ['In Transit', 'Overstay'], true))
                ->take(3)
                ->map(fn ($row) => [
                    'visitor_name' => $row['visitor_name'],
                    'destination' => $row['destination'],
                    'control_number' => $row['control_number'],
                    'time_label' => $row['entry_time_label_short'],
                    'result' => 'MATCHED',
                ]);
        }

        return view('admin.visitor', [
            'rows' => $paginatedRows,
            'totalRows' => $rows->count(),
            'filteredCount' => $filteredCount,
            'officeOptions' => $officeOptions,
            'statusOptions' => $statusOptions,
            'visitTypeOptions' => $visitTypeOptions,
            'activeByOffice' => $activeByOffice,
            'maxOfficeCount' => $maxOfficeCount,
            'recentVisitors' => $recentVisitors,
            'correctOfficeScans' => $correctOfficeScans,
            'summaryCards' => [
                [
                    'label' => 'Total Today',
                    'value' => $totalTodayCount,
                    'modifier' => 'total',
                ],
                [
                    'label' => 'Active',
                    'value' => $activeCount,
                    'modifier' => 'active',
                ],
                [
                    'label' => 'Completed',
                    'value' => $completedCount,
                    'modifier' => 'completed',
                ],
                [
                    'label' => 'Alerts',
                    'value' => $alertsCount,
                    'modifier' => 'alerts',
                ],
            ],
            'filters' => [
                'search' => $filters['search'],
                'office' => $filters['office'],
                'status' => $filters['status'],
                'visit_type' => $filters['visit_type'],
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
            ],
            'fetchError' => $fetchError,
        ]);
    }

    public function export(Request $request)
    {
        [$rows] = $this->loadRowsAndFetchError();
        $filters = $this->extractFilters($request);
        $filteredRows = $this->applyFilters($rows, $filters)->values();

        $filename = 'visitor-monitoring-'.now()->format('Ymd_His').'.xls';
        $headers = [
            'Visitor',
            'Pass No.',
            'Control #',
            'Contact No.',
            'Visit Type',
            'Purpose',
            'Destination',
            'Time In Date',
            'Time In',
            'Duration',
            'Status',
            'Alert',
        ];

        return response()->streamDownload(function () use ($filteredRows, $headers) {
            $stream = fopen('php://output', 'wb');
            if (! $stream) {
                return;
            }

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $headers, "\t");

            foreach ($filteredRows as $row) {
                fputcsv($stream, [
                    $row['visitor_name'] ?? '—',
                    $row['pass_number'] ?? '—',
                    $row['control_number'] ?? '—',
                    $row['contact_no'] ?? '—',
                    $row['visit_type'] ?? '—',
                    $row['purpose'] ?? '—',
                    $row['destination'] ?? '—',
                    $row['entry_time_label_date'] ?? '—',
                    $row['entry_time_label_time'] ?? '—',
                    $row['duration_label'] ?? '—',
                    $row['status'] ?? '—',
                    $row['alert'] ?? 'None',
                ], "\t");
            }

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function loadRowsAndFetchError(): array
    {
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_KEY');

        $rows = collect([]);
        $fetchError = null;

        if (! $supabaseUrl || ! $supabaseKey) {
            $fetchError = 'Supabase configuration is missing.';

            return [$rows, $fetchError];
        }

        try {
            $rows = $this->fetchVisitorMonitoringRows($supabaseUrl, $supabaseKey);

            if ($rows->isEmpty()) {
                $fallbackRows = $this->fetchVisitorMonitoringRowsFromDatabase();

                if ($fallbackRows->isNotEmpty()) {
                    $rows = $fallbackRows;

                    $keyRole = $this->tokenRole($supabaseKey);
                    $fetchError = $keyRole === 'anon'
                        ? 'Supabase REST is using an anon key (RLS-limited). Showing database fallback data; update SUPABASE_SERVICE_ROLE_KEY with your service_role key.'
                        : 'Supabase REST returned no rows. Showing database fallback data.';

                    logger()->warning('Visitor monitoring is using DB fallback data', [
                        'supabase_key_role' => $keyRole,
                        'rest_rows' => 0,
                        'db_rows' => $rows->count(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            logger()->error('Visitor monitoring Supabase fetch failed: '.$e->getMessage());

            $fallbackRows = $this->fetchVisitorMonitoringRowsFromDatabase();
            if ($fallbackRows->isNotEmpty()) {
                $rows = $fallbackRows;
                $fetchError = 'Unable to fetch Visitor Monitoring data from Supabase. Showing database fallback data.';

                logger()->warning('Visitor monitoring fallback after Supabase failure', [
                    'db_rows' => $rows->count(),
                ]);
            } else {
                $fetchError = 'Unable to fetch Visitor Monitoring data from Supabase.';
            }
        }

        return [$rows, $fetchError];
    }

    private function extractFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'office' => trim((string) $request->query('office', '')),
            'status' => trim((string) $request->query('status', '')),
            'visit_type' => trim((string) $request->query('visit_type', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];
    }

    private function applyFilters(Collection $rows, array $filters): Collection
    {
        $search = (string) ($filters['search'] ?? '');
        $officeFilter = (string) ($filters['office'] ?? '');
        $statusFilter = (string) ($filters['status'] ?? '');
        $visitTypeFilter = (string) ($filters['visit_type'] ?? '');
        $dateFromFilter = (string) ($filters['date_from'] ?? '');
        $dateToFilter = (string) ($filters['date_to'] ?? '');

        return $rows->filter(function (array $row) use ($search, $officeFilter, $statusFilter, $visitTypeFilter, $dateFromFilter, $dateToFilter) {
            $matchesSearch = true;
            if ($search !== '') {
                $haystack = Str::lower(implode(' ', [
                    $row['visitor_name'] ?? '',
                    $row['pass_number'] ?? '',
                    $row['control_number'] ?? '',
                    $row['contact_no'] ?? '',
                    $row['visit_type'] ?? '',
                    $row['purpose'] ?? '',
                    $row['destination'] ?? '',
                    $row['alert'] ?? '',
                ]));
                $matchesSearch = Str::contains($haystack, Str::lower($search));
            }

            $matchesOffice = $officeFilter === '' || ($row['destination'] ?? '') === $officeFilter;
            $matchesStatus = $statusFilter === '' || ($row['status'] ?? '') === $statusFilter;
            $matchesVisitType = $visitTypeFilter === '' || ($row['visit_type'] ?? '') === $visitTypeFilter;
            $entryDate = (string) ($row['entry_date_value'] ?? '');
            $matchesDateFrom = $dateFromFilter === '' || ($entryDate !== '' && $entryDate >= $dateFromFilter);
            $matchesDateTo = $dateToFilter === '' || ($entryDate !== '' && $entryDate <= $dateToFilter);

            return $matchesSearch
                && $matchesOffice
                && $matchesStatus
                && $matchesVisitType
                && $matchesDateFrom
                && $matchesDateTo;
        });
    }

    private function fetchVisitorMonitoringRows(string $supabaseUrl, string $supabaseKey): Collection
    {
        $select = 'visit_id,visitor_id,guard_user_id,purpose_reason,entry_time,exit_time,duration_minutes,'.
            'visitor:visitor!visit_visitor_id_fkey(visitor_id,first_name,last_name,pass_number,control_number,contact_no,visitor_photo_with_id_url,address_id),'.
            'visit_type:visit_type!visit_visit_type_id_fkey(visit_type_name),'.
            'office:office!visit_primary_office_id_fkey(office_name),'.
            'exit_status:exit_status!visit_exit_status_id_fkey(exit_status_name),'.
            'registered_guard:users!visit_guard_user_id_fkey(first_name,last_name),'.
            'office_expectations:office_expectation!office_expectation_visit_id_fkey(expectation_id,expected_order,arrived_at,office:office!office_expectation_office_id_fkey(office_name),expectation_status:expectation_status!office_expectation_expectation_status_id_fkey(status_name)),'.
            'alerts(alert_id,alert_type,severity,message,status,created_at,resolved_at,resolved_by,resolution_notes,resolved_user:users!fk_alerts_resolved_by(first_name,last_name))';

        $response = Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer '.$supabaseKey,
            'Accept' => 'application/json',
        ])->timeout(20)->get(rtrim($supabaseUrl, '/').'/rest/v1/visit', [
            'select' => $select,
            'order' => 'visit_id.desc',
            'limit' => 200,
        ]);

        if (! $response->ok()) {
            logger()->warning('Supabase visit fetch returned non-OK status', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return collect([]);
        }

        $visits = $response->json();

        if (! is_array($visits)) {
            return collect([]);
        }

        $baseUrl = rtrim($supabaseUrl, '/');
        $visitIds = collect($visits)
            ->pluck('visit_id')
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $addressIds = collect($visits)
            ->map(function (array $visit) {
                $visitor = is_array($visit['visitor'] ?? null) ? $visit['visitor'] : [];

                return $visitor['address_id'] ?? null;
            })
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $addressMap = $this->fetchAddressMapFromRest($baseUrl, $supabaseKey, $addressIds);
        $photoPathMap = $this->buildSignedPhotoUrlMap(
            collect($visits)
                ->map(function (array $visit) {
                    $visitor = is_array($visit['visitor'] ?? null) ? $visit['visitor'] : [];

                    return (string) ($visitor['visitor_photo_with_id_url'] ?? '');
                })
                ->filter(fn ($value) => trim((string) $value) !== '')
                ->values(),
            $baseUrl,
            $supabaseKey
        );
        $officeRouteMap = $this->fetchOfficeRouteMapFromRest($baseUrl, $supabaseKey, $visitIds);
        $latestOfficeScanMap = $this->fetchLatestOfficeScanMapFromRest($baseUrl, $supabaseKey, $visitIds);

        return collect($visits)->map(function (array $visit) use ($addressMap, $photoPathMap, $officeRouteMap, $latestOfficeScanMap) {
            $visitor = $visit['visitor'] ?? [];
            $visitType = $visit['visit_type'] ?? [];
            $office = $visit['office'] ?? [];
            $exitStatusRel = $this->extractRelation($visit, 'exit_status');
            $guardRel = $this->extractRelation($visit, 'registered_guard');

            $firstName = trim((string) ($visitor['first_name'] ?? ''));
            $lastName = trim((string) ($visitor['last_name'] ?? ''));
            $fullName = trim($firstName.' '.$lastName);

            $entry = $this->parseDateTime($visit['entry_time'] ?? null);
            $exit = $this->parseDateTime($visit['exit_time'] ?? null);
            $durationMinutes = $this->resolveDurationMinutes($visit, $entry, $exit);

            $alerts = collect($visit['alerts'] ?? [])
                ->filter(fn ($a) => is_array($a));

            $officeRoute = collect($officeRouteMap->get((string) ($visit['visit_id'] ?? ''), []));

            if ($officeRoute->isEmpty()) {
                $officeRoute = collect($visit['office_expectations'] ?? [])
                    ->filter(fn ($expectation) => is_array($expectation))
                    ->sortBy(fn ($expectation) => (int) ($expectation['expected_order'] ?? PHP_INT_MAX))
                    ->map(function (array $expectation) {
                        $expectedOfficeRel = $this->extractRelation($expectation, 'office');
                        $expectationStatusRel = $this->extractRelation($expectation, 'expectation_status');
                        $arrivedAt = $this->parseDateTime($expectation['arrived_at'] ?? null);

                        $expectedOffice = trim((string) ($expectedOfficeRel['office_name'] ?? ''));
                        $statusName = trim((string) ($expectationStatusRel['status_name'] ?? ''));

                        return [
                            'expected_office' => $expectedOffice !== '' ? $expectedOffice : '—',
                            'expected_order' => isset($expectation['expected_order']) ? (string) $expectation['expected_order'] : '—',
                            'expectation_status' => $statusName !== '' ? $statusName : 'Pending',
                            'arrived_at' => $arrivedAt ? $arrivedAt->format('M d, Y h:i A') : '—',
                        ];
                    })
                    ->values();
            }

            $latestAlert = $alerts
                ->sortByDesc(fn ($a) => $a['created_at'] ?? null)
                ->first();

            $latestAlertType = (string) ($latestAlert['alert_type'] ?? '');
            $resolvedUser = $this->extractRelation(is_array($latestAlert) ? $latestAlert : [], 'resolved_user');
            $resolvedByName = trim(((string) ($resolvedUser['first_name'] ?? '')).' '.((string) ($resolvedUser['last_name'] ?? '')));

            $alertCreated = $this->parseDateTime($latestAlert['created_at'] ?? null);
            $alertResolved = $this->parseDateTime($latestAlert['resolved_at'] ?? null);

            $status = $this->resolveStatus($visit, $alerts);
            $scanData = $latestOfficeScanMap->get((string) ($visit['visit_id'] ?? ''), []);

            $addressId = (string) ($visitor['address_id'] ?? '');
            $address = $addressMap->get($addressId, '—');
            $rawPhotoPath = (string) ($visitor['visitor_photo_with_id_url'] ?? '');
            $photoUrl = (string) ($photoPathMap->get($rawPhotoPath) ?? '');
            $exitStatusName = trim((string) ($exitStatusRel['exit_status_name'] ?? ''));
            $registeredBy = trim(((string) ($guardRel['first_name'] ?? '')).' '.((string) ($guardRel['last_name'] ?? '')));
            if ($registeredBy === '' && ! empty($visit['guard_user_id'])) {
                $registeredBy = $this->resolveGuardNameByUserId((int) $visit['guard_user_id']);
            }

            return [
                'visit_id' => $visit['visit_id'] ?? null,
                'visitor_id' => $visitor['visitor_id'] ?? ($visit['visitor_id'] ?? null),
                'visitor_name' => $fullName !== '' ? $fullName : 'Unknown Visitor',
                'pass_number' => (string) ($visitor['pass_number'] ?? '—'),
                'control_number' => (string) ($visitor['control_number'] ?? '—'),
                'contact_no' => (string) ($visitor['contact_no'] ?? '—'),
                'visitor_photo_with_id_url' => $photoUrl,
                'address' => $address,
                'visit_type' => (string) ($visitType['visit_type_name'] ?? '—'),
                'purpose' => (string) ($visit['purpose_reason'] ?? '—'),
                'destination' => (string) ($office['office_name'] ?? '—'),
                'entry_time_label_date' => $entry ? $entry->format('M d, Y') : '—',
                'entry_time_label_time' => $entry ? $entry->format('h:i A') : '—',
                'entry_time_label_short' => $entry ? $entry->format('h:i A') : '—',
                'exit_time_label' => $exit ? $exit->format('M d, Y h:i A') : '—',
                'entry_date_value' => $entry ? $entry->toDateString() : null,
                'duration_minutes' => $durationMinutes,
                'duration_label' => $this->formatDurationLabel($durationMinutes),
                'status' => $status,
                'exit_status' => $exitStatusName !== '' ? $exitStatusName : $status,
                'registered_by_guard' => $registeredBy !== '' ? $registeredBy : '—',
                'office_route' => $officeRoute->toArray(),
                'status_class' => $this->statusClass($status),
                'alert' => $latestAlertType !== '' ? $latestAlertType : 'None',
                'alert_id' => $latestAlert['alert_id'] ?? null,
                'alert_type' => $latestAlertType !== '' ? $latestAlertType : 'None',
                'alert_severity' => (string) ($latestAlert['severity'] ?? '—'),
                'alert_message' => (string) ($latestAlert['message'] ?? '—'),
                'alert_status' => (string) ($latestAlert['status'] ?? '—'),
                'alert_created_at' => $alertCreated ? $alertCreated->format('M d, Y h:i A') : '—',
                'alert_resolved_at' => $alertResolved ? $alertResolved->format('M d, Y h:i A') : '—',
                'alert_resolved_by' => $resolvedByName !== '' ? $resolvedByName : '—',
                'alert_resolution_notes' => (string) ($latestAlert['resolution_notes'] ?? '—'),
                'scan_id' => (string) ($scanData['scan_id'] ?? '—'),
                'scan_time_label' => (string) ($scanData['scan_time_label'] ?? '—'),
                'scan_remarks' => (string) ($scanData['scan_remarks'] ?? '—'),
                'scanned_office' => (string) ($scanData['scanned_office'] ?? '—'),
                'scanned_by' => (string) ($scanData['scanned_by'] ?? '—'),
                'validation_status' => (string) ($scanData['validation_status'] ?? 'Unknown'),
                'raw_entry_time' => $entry ? $entry->toIso8601String() : null,
                'raw_visit_id' => (int) ($visit['visit_id'] ?? 0),
            ];
        })
            ->sortByDesc('raw_visit_id')
            ->values();
    }

    private function fetchOfficeRouteMapFromRest(string $baseUrl, string $supabaseKey, Collection $visitIds): Collection
    {
        if ($visitIds->isEmpty()) {
            return collect([]);
        }

        $response = Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer '.$supabaseKey,
            'Accept' => 'application/json',
        ])->timeout(20)->get($baseUrl.'/rest/v1/office_expectation', [
            'select' => 'visit_id,expected_order,arrived_at,office:office!office_expectation_office_id_fkey(office_name),expectation_status:expectation_status!office_expectation_expectation_status_id_fkey(status_name)',
            'visit_id' => 'in.('.$visitIds->implode(',').')',
            'order' => 'expected_order.asc',
            'limit' => 5000,
        ]);

        if (! $response->ok() || ! is_array($response->json())) {
            return collect([]);
        }

        return collect($response->json())
            ->filter(fn ($row) => is_array($row) && isset($row['visit_id']))
            ->groupBy(fn ($row) => (string) ($row['visit_id'] ?? ''))
            ->map(function (Collection $rows) {
                return $rows
                    ->map(function (array $expectation) {
                        $expectedOfficeRel = $this->extractRelation($expectation, 'office');
                        $expectationStatusRel = $this->extractRelation($expectation, 'expectation_status');
                        $arrivedAt = $this->parseDateTime($expectation['arrived_at'] ?? null);

                        $expectedOffice = trim((string) ($expectedOfficeRel['office_name'] ?? ''));
                        $statusName = trim((string) ($expectationStatusRel['status_name'] ?? ''));

                        return [
                            'expected_office' => $expectedOffice !== '' ? $expectedOffice : '—',
                            'expected_order' => isset($expectation['expected_order']) ? (string) $expectation['expected_order'] : '—',
                            'expectation_status' => $statusName !== '' ? $statusName : 'Pending',
                            'arrived_at' => $arrivedAt ? $arrivedAt->format('M d, Y h:i A') : '—',
                        ];
                    })
                    ->values()
                    ->all();
            });
    }

    private function fetchLatestOfficeScanMapFromRest(string $baseUrl, string $supabaseKey, Collection $visitIds): Collection
    {
        if ($visitIds->isEmpty()) {
            return collect([]);
        }

        $headers = [
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer '.$supabaseKey,
            'Accept' => 'application/json',
        ];

        $response = Http::withHeaders($headers)->timeout(20)->get($baseUrl.'/rest/v1/office_scan', [
            'select' => 'visit_id,scan_id,scan_time,remarks,office:office!office_scan_office_id_fkey(office_name),scanner:users!office_scan_scanned_by_user_id_fkey(first_name,last_name),validation_status:validation_status!office_scan_validation_status_id_fkey(status_name)',
            'visit_id' => 'in.('.$visitIds->implode(',').')',
            'order' => 'scan_time.desc',
            'limit' => 5000,
        ]);

        if (! $response->ok() || ! is_array($response->json())) {
            $fallback = Http::withHeaders($headers)->timeout(20)->get($baseUrl.'/rest/v1/office_scan', [
                'select' => 'visit_id,scan_id,scan_time,remarks,office_id,scanned_by_user_id,validation_status_id',
                'visit_id' => 'in.('.$visitIds->implode(',').')',
                'order' => 'scan_time.desc',
                'limit' => 5000,
            ]);

            if (! $fallback->ok() || ! is_array($fallback->json())) {
                return collect([]);
            }

            $rows = collect($fallback->json())
                ->filter(fn ($row) => is_array($row) && isset($row['visit_id']))
                ->sortByDesc(fn ($row) => (string) ($row['scan_time'] ?? ''))
                ->groupBy(fn ($row) => (string) ($row['visit_id'] ?? ''))
                ->map(fn (Collection $group) => (array) $group->first());

            $officeIds = $rows->pluck('office_id')->filter()->map(fn ($id) => (string) $id)->unique()->values();
            $userIds = $rows->pluck('scanned_by_user_id')->filter()->map(fn ($id) => (string) $id)->unique()->values();
            $validationIds = $rows->pluck('validation_status_id')->filter()->map(fn ($id) => (string) $id)->unique()->values();

            $officeNameMap = $this->fetchOfficeMap($baseUrl, $supabaseKey);

            $userMap = collect([]);
            if ($userIds->isNotEmpty()) {
                $usersResponse = Http::withHeaders($headers)->timeout(20)->get($baseUrl.'/rest/v1/users', [
                    'select' => 'user_id,first_name,last_name',
                    'user_id' => 'in.('.$userIds->implode(',').')',
                    'limit' => 2000,
                ]);

                if ($usersResponse->ok() && is_array($usersResponse->json())) {
                    $userMap = collect($usersResponse->json())
                        ->filter(fn ($row) => is_array($row) && isset($row['user_id']))
                        ->mapWithKeys(function (array $row) {
                            $fullName = trim(((string) ($row['first_name'] ?? '')).' '.((string) ($row['last_name'] ?? '')));

                            return [
                                (string) $row['user_id'] => $fullName !== '' ? $fullName : '—',
                            ];
                        });
                }
            }

            $validationMap = collect([]);
            if ($validationIds->isNotEmpty()) {
                $validationResponse = Http::withHeaders($headers)->timeout(20)->get($baseUrl.'/rest/v1/validation_status', [
                    'select' => 'validation_status_id,status_name',
                    'validation_status_id' => 'in.('.$validationIds->implode(',').')',
                    'limit' => 200,
                ]);

                if ($validationResponse->ok() && is_array($validationResponse->json())) {
                    $validationMap = collect($validationResponse->json())
                        ->filter(fn ($row) => is_array($row) && isset($row['validation_status_id']))
                        ->mapWithKeys(fn (array $row) => [
                            (string) $row['validation_status_id'] => (string) ($row['status_name'] ?? 'Unknown'),
                        ]);
                }
            }

            return $rows->map(function (array $scan) use ($officeNameMap, $userMap, $validationMap) {
                $scanTime = $this->parseDateTime($scan['scan_time'] ?? null);
                $officeName = (string) ($officeNameMap->get((string) ($scan['office_id'] ?? '')) ?? '');
                $scannedBy = (string) ($userMap->get((string) ($scan['scanned_by_user_id'] ?? '')) ?? '');
                $validationStatus = (string) ($validationMap->get((string) ($scan['validation_status_id'] ?? '')) ?? 'Unknown');

                return [
                    'scan_id' => (string) ($scan['scan_id'] ?? '—'),
                    'scan_time_label' => $scanTime ? $scanTime->format('M d, Y h:i A') : '—',
                    'scan_remarks' => (string) ($scan['remarks'] ?? '—'),
                    'scanned_office' => $officeName !== '' ? $officeName : '—',
                    'scanned_by' => $scannedBy !== '' ? $scannedBy : '—',
                    'validation_status' => $validationStatus !== '' ? $validationStatus : 'Unknown',
                ];
            });
        }

        return collect($response->json())
            ->filter(fn ($row) => is_array($row) && isset($row['visit_id']))
            ->groupBy(fn ($row) => (string) ($row['visit_id'] ?? ''))
            ->map(function (Collection $group) {
                $scan = (array) $group
                    ->sortByDesc(fn ($row) => (string) ($row['scan_time'] ?? ''))
                    ->first();

                $officeRel = $this->extractRelation($scan, 'office');
                $scannerRel = $this->extractRelation($scan, 'scanner');
                $validationRel = $this->extractRelation($scan, 'validation_status');
                $scanTime = $this->parseDateTime($scan['scan_time'] ?? null);

                $scannerName = trim(((string) ($scannerRel['first_name'] ?? '')).' '.((string) ($scannerRel['last_name'] ?? '')));

                return [
                    'scan_id' => (string) ($scan['scan_id'] ?? '—'),
                    'scan_time_label' => $scanTime ? $scanTime->format('M d, Y h:i A') : '—',
                    'scan_remarks' => (string) ($scan['remarks'] ?? '—'),
                    'scanned_office' => (string) ($officeRel['office_name'] ?? '—'),
                    'scanned_by' => $scannerName !== '' ? $scannerName : '—',
                    'validation_status' => (string) ($validationRel['status_name'] ?? 'Unknown'),
                ];
            });
    }

    private function fetchCorrectOfficeScans(string $supabaseUrl, string $supabaseKey): Collection
    {
        $baseUrl = rtrim($supabaseUrl, '/');

        $officeMap = $this->fetchOfficeMap($baseUrl, $supabaseKey);
        $expectationMap = $this->fetchOfficeExpectationMap($baseUrl, $supabaseKey, $officeMap);

        $scanRows = $this->fetchOfficeScansWithRelations($baseUrl, $supabaseKey);

        return $scanRows
            ->map(function (array $scan) use ($expectationMap, $officeMap) {
                $visitId = $scan['visit_id'] ?? null;

                $validation = $this->extractRelation($scan, 'validation_status');
                $validationName = (string) ($validation['status_name'] ?? $scan['validation_status'] ?? '');

                $scannedOfficeRel = $this->extractRelation($scan, 'office');
                $scannedOfficeName = (string) ($scannedOfficeRel['office_name'] ?? '');

                $scannedOfficeId = $scan['office_id'] ?? null;
                $expected = $expectationMap->get((string) $visitId, null);
                $expectedOfficeId = $expected['office_id'] ?? null;
                $expectedOfficeName = (string) ($expected['office_name'] ?? '');

                if ($scannedOfficeName === '' && $scannedOfficeId !== null) {
                    $scannedOfficeName = (string) ($officeMap->get((string) $scannedOfficeId) ?? '');
                }

                $isMatchedByStatus = Str::contains(Str::lower($validationName), 'match');
                $isMatchedByOffice = $expectedOfficeId !== null
                    && (string) $expectedOfficeId !== ''
                    && (string) $expectedOfficeId === (string) $scannedOfficeId;

                if (! $isMatchedByStatus && ! $isMatchedByOffice) {
                    return null;
                }

                $visitRel = $this->extractRelation($scan, 'visit');
                $visitorRel = $this->extractRelation($visitRel, 'visitor');

                $visitorName = trim(((string) ($visitorRel['first_name'] ?? '')).' '.((string) ($visitorRel['last_name'] ?? '')));
                $controlNo = (string) ($visitorRel['control_number'] ?? '—');

                $scanTime = $this->parseDateTime($scan['scan_time'] ?? null);

                return [
                    'visitor_name' => $visitorName !== '' ? $visitorName : 'Unknown Visitor',
                    'destination' => $expectedOfficeName !== ''
                        ? $expectedOfficeName
                        : ($scannedOfficeName !== '' ? $scannedOfficeName : '—'),
                    'control_number' => $controlNo,
                    'time_label' => $scanTime ? $scanTime->format('h:i A') : '—',
                    'result' => 'MATCHED',
                    'raw_scan_time' => $scanTime ? $scanTime->getTimestamp() : 0,
                ];
            })
            ->filter()
            ->sortByDesc('raw_scan_time')
            ->take(3)
            ->values();
    }

    private function fetchOfficeMap(string $baseUrl, string $supabaseKey): Collection
    {
        $response = Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer '.$supabaseKey,
            'Accept' => 'application/json',
        ])->timeout(20)->get($baseUrl.'/rest/v1/office', [
            'select' => 'office_id,office_name',
            'limit' => 1000,
        ]);

        if (! $response->ok() || ! is_array($response->json())) {
            return collect([]);
        }

        return collect($response->json())
            ->filter(fn ($row) => is_array($row))
            ->mapWithKeys(function (array $row) {
                return [
                    (string) ($row['office_id'] ?? '') => (string) ($row['office_name'] ?? ''),
                ];
            });
    }

    private function fetchOfficeExpectationMap(string $baseUrl, string $supabaseKey, Collection $officeMap): Collection
    {
        $response = Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer '.$supabaseKey,
            'Accept' => 'application/json',
        ])->timeout(20)->get($baseUrl.'/rest/v1/office_expectation', [
            'select' => '*',
            'limit' => 2000,
        ]);

        if (! $response->ok() || ! is_array($response->json())) {
            return collect([]);
        }

        return collect($response->json())
            ->filter(fn ($row) => is_array($row))
            ->mapWithKeys(function (array $row) use ($officeMap) {
                $visitId = $row['visit_id'] ?? null;
                if ($visitId === null || $visitId === '') {
                    return [];
                }

                $officeId = $this->firstFilled($row, [
                    'expected_office_id',
                    'office_id',
                    'target_office_id',
                    'destination_office_id',
                ]);

                $officeName = '';
                if ($officeId !== null && $officeId !== '') {
                    $officeName = (string) ($officeMap->get((string) $officeId) ?? '');
                }

                return [
                    (string) $visitId => [
                        'office_id' => $officeId,
                        'office_name' => $officeName,
                    ],
                ];
            });
    }

    private function fetchOfficeScansWithRelations(string $baseUrl, string $supabaseKey): Collection
    {
        $headers = [
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer '.$supabaseKey,
            'Accept' => 'application/json',
        ];

        $primary = Http::withHeaders($headers)->timeout(20)->get($baseUrl.'/rest/v1/office_scan', [
            'select' => 'scan_id,scan_time,visit_id,office_id,validation_status(validation_status_id,status_name),office(office_name),visit(visit_id,visitor(first_name,last_name,control_number))',
            'order' => 'scan_time.desc',
            'limit' => 500,
        ]);

        if ($primary->ok() && is_array($primary->json())) {
            return collect($primary->json())->filter(fn ($r) => is_array($r))->values();
        }

        // Fallback query when relation nesting varies in PostgREST metadata.
        $fallback = Http::withHeaders($headers)->timeout(20)->get($baseUrl.'/rest/v1/office_scan', [
            'select' => 'scan_id,scan_time,visit_id,office_id,validation_status(validation_status_id,status_name)',
            'order' => 'scan_time.desc',
            'limit' => 500,
        ]);

        if (! $fallback->ok() || ! is_array($fallback->json())) {
            logger()->warning('office_scan fetch failed', [
                'primary_status' => $primary->status(),
                'primary_body' => $primary->body(),
                'fallback_status' => $fallback->status(),
                'fallback_body' => $fallback->body(),
            ]);

            return collect([]);
        }

        $fallbackRows = collect($fallback->json())->filter(fn ($r) => is_array($r))->values();

        $visitIds = $fallbackRows->pluck('visit_id')->filter()->unique()->values();
        if ($visitIds->isEmpty()) {
            return $fallbackRows;
        }

        $visitResponse = Http::withHeaders($headers)->timeout(20)->get($baseUrl.'/rest/v1/visit', [
            'select' => 'visit_id,visitor(first_name,last_name,control_number)',
            'visit_id' => 'in.('.$visitIds->implode(',').')',
            'limit' => 1000,
        ]);

        if (! $visitResponse->ok() || ! is_array($visitResponse->json())) {
            return $fallbackRows;
        }

        $visitMap = collect($visitResponse->json())
            ->filter(fn ($v) => is_array($v) && isset($v['visit_id']))
            ->mapWithKeys(fn ($v) => [(string) $v['visit_id'] => $v]);

        return $fallbackRows->map(function (array $row) use ($visitMap) {
            $visit = $visitMap->get((string) ($row['visit_id'] ?? ''), null);
            if ($visit) {
                $row['visit'] = $visit;
            }

            return $row;
        });
    }

    private function extractRelation($source, string $key): array
    {
        if (! is_array($source)) {
            return [];
        }

        $value = $source[$key] ?? null;

        if (is_array($value) && array_key_exists(0, $value) && is_array($value[0])) {
            return $value[0];
        }

        return is_array($value) ? $value : [];
    }

    private function firstFilled(array $source, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $source) && $source[$key] !== null && $source[$key] !== '') {
                return $source[$key];
            }
        }

        return null;
    }

    private function parseDateTime($value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveDurationMinutes(array $visit, ?Carbon $entry, ?Carbon $exit): int
    {
        $fromColumn = $visit['duration_minutes'] ?? null;
        if (is_numeric($fromColumn)) {
            return max(0, (int) $fromColumn);
        }

        if (! $entry) {
            return 0;
        }

        $end = $exit ?: now();

        return max(0, $entry->diffInMinutes($end));
    }

    private function resolveStatus(array $visit, Collection $alerts): string
    {
        if (filled($visit['exit_time'] ?? null)) {
            return 'Completed';
        }

        $alertTypes = $alerts
            ->pluck('alert_type')
            ->filter()
            ->map(fn ($x) => Str::lower((string) $x));

        if ($alertTypes->contains(fn ($t) => Str::contains($t, 'overstay'))) {
            return 'Overstay';
        }

        if ($alertTypes->contains(fn ($t) => Str::contains($t, 'wrong office') || Str::contains($t, 'transit'))) {
            return 'In Transit';
        }

        return 'Arrived';
    }

    private function statusClass(string $status): string
    {
        return match ($status) {
            'In Transit' => 'status-transit',
            'Completed' => 'status-completed',
            'Overstay' => 'status-overstay',
            default => 'status-arrived',
        };
    }

    private function formatDurationLabel(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes.' mins';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        if ($remaining === 0) {
            return $hours.' hr'.($hours > 1 ? 's' : '');
        }

        return $hours.' hr '.$remaining.' mins';
    }

    private function fetchVisitorMonitoringRowsFromDatabase(): Collection
    {
        try {
            $visits = DB::table('visit as v')
                ->leftJoin('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
                ->leftJoin('address as a', 'a.address_id', '=', 'vr.address_id')
                ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
                ->leftJoin('office as o', 'o.office_id', '=', 'v.primary_office_id')
                ->leftJoin('exit_status as es', 'es.exit_status_id', '=', 'v.exit_status_id')
                ->leftJoin('users as gu', 'gu.user_id', '=', 'v.guard_user_id')
                ->select([
                    'v.visit_id',
                    'v.purpose_reason',
                    'v.entry_time',
                    'v.exit_time',
                    'v.duration_minutes',
                    'v.exit_status_id',
                    'vr.visitor_id',
                    'vr.first_name as visitor_first_name',
                    'vr.last_name as visitor_last_name',
                    'vr.pass_number',
                    'vr.control_number',
                    'vr.contact_no',
                    'vr.visitor_photo_with_id_url',
                    'a.house_no as address_house_no',
                    'a.street as address_street',
                    'a.barangay as address_barangay',
                    'a.city_municipality as address_city_municipality',
                    'a.province as address_province',
                    'a.region as address_region',
                    'vt.visit_type_name',
                    'o.office_name',
                    'es.exit_status_name',
                    'gu.first_name as guard_first_name',
                    'gu.last_name as guard_last_name',
                ])
                ->orderByDesc('v.visit_id')
                ->orderByDesc('v.entry_time')
                ->limit(200)
                ->get();

            if ($visits->isEmpty()) {
                return collect([]);
            }

            $visitIds = $visits->pluck('visit_id')->filter()->values();

            $alertsByVisit = collect([]);
            if ($visitIds->isNotEmpty()) {
                $alertsByVisit = DB::table('alerts as al')
                    ->leftJoin('users as ru', 'ru.user_id', '=', 'al.resolved_by')
                    ->select([
                        'al.visit_id',
                        'al.alert_id',
                        'al.alert_type',
                        'al.severity',
                        'al.message',
                        'al.status',
                        'al.created_at',
                        'al.resolved_at',
                        'al.resolved_by',
                        'al.resolution_notes',
                        'ru.first_name as resolved_by_first_name',
                        'ru.last_name as resolved_by_last_name',
                    ])
                    ->whereIn('visit_id', $visitIds)
                    ->orderByDesc('al.created_at')
                    ->get()
                    ->groupBy('visit_id');
            }

            $officeExpectationsByVisit = collect([]);
            if ($visitIds->isNotEmpty()) {
                $officeExpectationsByVisit = DB::table('office_expectation as oe')
                    ->leftJoin('office as oex', 'oex.office_id', '=', 'oe.office_id')
                    ->leftJoin('expectation_status as exs', 'exs.expectation_status_id', '=', 'oe.expectation_status_id')
                    ->select([
                        'oe.visit_id',
                        'oe.expected_order',
                        'oe.arrived_at',
                        'oex.office_name as expected_office_name',
                        'exs.status_name as expectation_status_name',
                    ])
                    ->whereIn('oe.visit_id', $visitIds)
                    ->orderBy('oe.expected_order')
                    ->get()
                    ->groupBy('visit_id');
            }

            $latestScansByVisit = collect([]);
            if ($visitIds->isNotEmpty()) {
                $latestScansByVisit = DB::table('office_scan as os')
                    ->leftJoin('office as so', 'so.office_id', '=', 'os.office_id')
                    ->leftJoin('users as su', 'su.user_id', '=', 'os.scanned_by_user_id')
                    ->leftJoin('validation_status as vs', 'vs.validation_status_id', '=', 'os.validation_status_id')
                    ->select([
                        'os.visit_id',
                        'os.scan_id',
                        'os.scan_time',
                        'os.remarks',
                        'so.office_name as scanned_office_name',
                        'su.first_name as scanned_by_first_name',
                        'su.last_name as scanned_by_last_name',
                        'vs.status_name as validation_status_name',
                    ])
                    ->whereIn('os.visit_id', $visitIds)
                    ->orderByDesc('os.scan_time')
                    ->get()
                    ->groupBy('visit_id')
                    ->map(fn (Collection $group) => (array) ((array) $group->first()));
            }

            $supabaseUrl = rtrim((string) env('SUPABASE_URL', ''), '/');
            $supabaseKey = (string) (env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_KEY') ?: '');

            $photoPathMap = $this->buildSignedPhotoUrlMap(
                $visits
                    ->pluck('visitor_photo_with_id_url')
                    ->map(fn ($value) => (string) $value)
                    ->filter(fn ($value) => trim($value) !== '')
                    ->values(),
                $supabaseUrl,
                $supabaseKey
            );

            return $visits->map(function ($row) use ($alertsByVisit, $officeExpectationsByVisit, $photoPathMap, $latestScansByVisit) {
                $visit = (array) $row;
                $visitId = $visit['visit_id'] ?? null;

                $alerts = collect($alertsByVisit->get($visitId, []))
                    ->map(fn ($alert) => (array) $alert)
                    ->values();

                $officeRoute = collect($officeExpectationsByVisit->get($visitId, []))
                    ->map(function ($expectationRow) {
                        $expectation = (array) $expectationRow;
                        $arrivedAt = $this->parseDateTime($expectation['arrived_at'] ?? null);
                        $expectedOffice = trim((string) ($expectation['expected_office_name'] ?? ''));
                        $statusName = trim((string) ($expectation['expectation_status_name'] ?? ''));

                        return [
                            'expected_office' => $expectedOffice !== '' ? $expectedOffice : '—',
                            'expected_order' => isset($expectation['expected_order']) ? (string) $expectation['expected_order'] : '—',
                            'expectation_status' => $statusName !== '' ? $statusName : 'Pending',
                            'arrived_at' => $arrivedAt ? $arrivedAt->format('M d, Y h:i A') : '—',
                        ];
                    })
                    ->values();

                $firstName = trim((string) ($visit['visitor_first_name'] ?? ''));
                $lastName = trim((string) ($visit['visitor_last_name'] ?? ''));
                $fullName = trim($firstName.' '.$lastName);

                $entry = $this->parseDateTime($visit['entry_time'] ?? null);
                $exit = $this->parseDateTime($visit['exit_time'] ?? null);
                $durationMinutes = $this->resolveDurationMinutes($visit, $entry, $exit);

                $latestAlert = $alerts
                    ->sortByDesc(fn ($a) => $a['created_at'] ?? null)
                    ->first();

                $latestAlertType = (string) ($latestAlert['alert_type'] ?? '');
                $resolvedByName = trim(((string) ($latestAlert['resolved_by_first_name'] ?? '')).' '.((string) ($latestAlert['resolved_by_last_name'] ?? '')));
                $alertCreated = $this->parseDateTime($latestAlert['created_at'] ?? null);
                $alertResolved = $this->parseDateTime($latestAlert['resolved_at'] ?? null);

                $status = $this->resolveStatus($visit, $alerts);
                $rawPhotoPath = (string) ($visit['visitor_photo_with_id_url'] ?? '');
                $photoUrl = (string) ($photoPathMap->get($rawPhotoPath) ?? '');
                $registeredBy = trim(((string) ($visit['guard_first_name'] ?? '')).' '.((string) ($visit['guard_last_name'] ?? '')));
                $exitStatusName = trim((string) ($visit['exit_status_name'] ?? ''));
                $scan = (array) ($latestScansByVisit->get($visitId, []));
                $scanTime = $this->parseDateTime($scan['scan_time'] ?? null);
                $scanScannedBy = trim(((string) ($scan['scanned_by_first_name'] ?? '')).' '.((string) ($scan['scanned_by_last_name'] ?? '')));

                $address = $this->formatAddress([
                    'house_no' => $visit['address_house_no'] ?? null,
                    'street' => $visit['address_street'] ?? null,
                    'barangay' => $visit['address_barangay'] ?? null,
                    'city_municipality' => $visit['address_city_municipality'] ?? null,
                    'province' => $visit['address_province'] ?? null,
                    'region' => $visit['address_region'] ?? null,
                ]);

                return [
                    'visit_id' => $visit['visit_id'] ?? null,
                    'visitor_id' => $visit['visitor_id'] ?? null,
                    'visitor_name' => $fullName !== '' ? $fullName : 'Unknown Visitor',
                    'pass_number' => (string) ($visit['pass_number'] ?? '—'),
                    'control_number' => (string) ($visit['control_number'] ?? '—'),
                    'contact_no' => (string) ($visit['contact_no'] ?? '—'),
                    'visitor_photo_with_id_url' => $photoUrl,
                    'address' => $address,
                    'visit_type' => (string) ($visit['visit_type_name'] ?? '—'),
                    'purpose' => (string) ($visit['purpose_reason'] ?? '—'),
                    'destination' => (string) ($visit['office_name'] ?? '—'),
                    'entry_time_label_date' => $entry ? $entry->format('M d, Y') : '—',
                    'entry_time_label_time' => $entry ? $entry->format('h:i A') : '—',
                    'entry_time_label_short' => $entry ? $entry->format('h:i A') : '—',
                    'exit_time_label' => $exit ? $exit->format('M d, Y h:i A') : '—',
                    'entry_date_value' => $entry ? $entry->toDateString() : null,
                    'duration_minutes' => $durationMinutes,
                    'duration_label' => $this->formatDurationLabel($durationMinutes),
                    'status' => $status,
                    'exit_status' => $exitStatusName !== '' ? $exitStatusName : $status,
                    'registered_by_guard' => $registeredBy !== '' ? $registeredBy : '—',
                    'office_route' => $officeRoute->toArray(),
                    'status_class' => $this->statusClass($status),
                    'alert' => $latestAlertType !== '' ? $latestAlertType : 'None',
                    'alert_id' => $latestAlert['alert_id'] ?? null,
                    'alert_type' => $latestAlertType !== '' ? $latestAlertType : 'None',
                    'alert_severity' => (string) ($latestAlert['severity'] ?? '—'),
                    'alert_message' => (string) ($latestAlert['message'] ?? '—'),
                    'alert_status' => (string) ($latestAlert['status'] ?? '—'),
                    'alert_created_at' => $alertCreated ? $alertCreated->format('M d, Y h:i A') : '—',
                    'alert_resolved_at' => $alertResolved ? $alertResolved->format('M d, Y h:i A') : '—',
                    'alert_resolved_by' => $resolvedByName !== '' ? $resolvedByName : '—',
                    'alert_resolution_notes' => (string) ($latestAlert['resolution_notes'] ?? '—'),
                    'scan_id' => (string) ($scan['scan_id'] ?? '—'),
                    'scan_time_label' => $scanTime ? $scanTime->format('M d, Y h:i A') : '—',
                    'scan_remarks' => (string) ($scan['remarks'] ?? '—'),
                    'scanned_office' => (string) ($scan['scanned_office_name'] ?? '—'),
                    'scanned_by' => $scanScannedBy !== '' ? $scanScannedBy : '—',
                    'validation_status' => (string) ($scan['validation_status_name'] ?? 'Unknown'),
                    'raw_entry_time' => $entry ? $entry->toIso8601String() : null,
                    'raw_visit_id' => (int) ($visit['visit_id'] ?? 0),
                ];
            })
                ->sortByDesc('raw_visit_id')
                ->values();
        } catch (\Throwable $e) {
            logger()->warning('Visitor monitoring DB fallback failed: '.$e->getMessage());

            return collect([]);
        }
    }

    private function fetchAddressMapFromRest(string $baseUrl, string $supabaseKey, Collection $addressIds): Collection
    {
        if ($addressIds->isEmpty()) {
            return collect([]);
        }

        $response = Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer '.$supabaseKey,
            'Accept' => 'application/json',
        ])->timeout(20)->get($baseUrl.'/rest/v1/address', [
            'select' => 'address_id,house_no,street,barangay,city_municipality,province,region',
            'address_id' => 'in.('.$addressIds->implode(',').')',
            'limit' => 1000,
        ]);

        if (! $response->ok() || ! is_array($response->json())) {
            return collect([]);
        }

        return collect($response->json())
            ->filter(fn ($row) => is_array($row) && isset($row['address_id']))
            ->mapWithKeys(function (array $row) {
                return [
                    (string) $row['address_id'] => $this->formatAddress($row),
                ];
            });
    }

    private function formatAddress(array $address): string
    {
        $line1 = trim(((string) ($address['house_no'] ?? '')).' '.((string) ($address['street'] ?? '')));

        $parts = collect([
            $line1,
            $address['barangay'] ?? null,
            $address['city_municipality'] ?? null,
            $address['province'] ?? null,
            $address['region'] ?? null,
        ])->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values();

        return $parts->isNotEmpty() ? $parts->implode(', ') : '—';
    }

    private function buildSignedPhotoUrlMap(Collection $rawPaths, string $baseUrl, string $supabaseKey): Collection
    {
        $map = collect([]);

        if ($rawPaths->isEmpty() || $baseUrl === '' || $supabaseKey === '') {
            return $map;
        }

        foreach ($rawPaths->unique()->values() as $rawPath) {
            $rawPath = trim((string) $rawPath);
            if ($rawPath === '') {
                continue;
            }

            $signedUrl = $this->createSignedStorageUrl($baseUrl, $supabaseKey, $rawPath);
            if ($signedUrl !== null) {
                $map->put($rawPath, $signedUrl);
            }
        }

        return $map;
    }

    private function createSignedStorageUrl(string $baseUrl, string $supabaseKey, string $rawPath): ?string
    {
        // Already absolute URL: keep it as-is.
        if (preg_match('/^https?:\/\//i', $rawPath) === 1) {
            return $rawPath;
        }

        [$bucket, $objectPath] = $this->parseStorageObjectPath($rawPath);
        if ($bucket === null || $objectPath === null) {
            return null;
        }

        $encodedBucket = rawurlencode($bucket);
        $encodedObjectPath = collect(explode('/', $objectPath))
            ->map(fn ($segment) => rawurlencode($segment))
            ->implode('/');

        try {
            $response = Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer '.$supabaseKey,
                'Accept' => 'application/json',
            ])->timeout(20)->post($baseUrl.'/storage/v1/object/sign/'.$encodedBucket.'/'.$encodedObjectPath, [
                'expiresIn' => 3600,
            ]);

            if (! $response->ok()) {
                logger()->warning('Unable to sign visitor photo URL', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'bucket' => $bucket,
                    'object_path' => $objectPath,
                ]);

                return null;
            }

            $payload = $response->json();
            if (! is_array($payload)) {
                return null;
            }

            $signed = $payload['signedURL'] ?? $payload['signedUrl'] ?? null;
            if (! is_string($signed) || trim($signed) === '') {
                return null;
            }

            if (preg_match('/^https?:\/\//i', $signed) === 1) {
                return $signed;
            }

            $signedPath = ltrim($signed, '/');

            if (Str::startsWith($signedPath, 'storage/v1/')) {
                return $baseUrl.'/'.$signedPath;
            }

            if (Str::startsWith($signedPath, 'object/')) {
                return $baseUrl.'/storage/v1/'.$signedPath;
            }

            return $baseUrl.'/'.$signedPath;
        } catch (\Throwable $e) {
            logger()->warning('Visitor photo signing request failed: '.$e->getMessage());

            return null;
        }
    }

    private function parseStorageObjectPath(string $rawPath): array
    {
        $path = trim($rawPath);
        if ($path === '') {
            return [null, null];
        }

        $path = preg_replace('#^https?://[^/]+/#i', '', $path) ?? $path;
        $path = ltrim($path, '/');

        // e.g. storage/v1/object/public/<bucket>/<path>
        $publicPrefix = 'storage/v1/object/public/';
        if (Str::startsWith($path, $publicPrefix)) {
            $path = Str::after($path, $publicPrefix);
        }

        // e.g. storage/v1/object/sign/<bucket>/<path>?token=...
        $signPrefix = 'storage/v1/object/sign/';
        if (Str::startsWith($path, $signPrefix)) {
            $path = Str::after($path, $signPrefix);
            $path = explode('?', $path, 2)[0] ?? $path;
        }

        $segments = array_values(array_filter(explode('/', $path), fn ($segment) => $segment !== ''));
        if (count($segments) < 2) {
            return [null, null];
        }

        $bucket = (string) $segments[0];
        $objectPath = implode('/', array_slice($segments, 1));

        return [$bucket !== '' ? $bucket : null, $objectPath !== '' ? $objectPath : null];
    }

    private function resolveGuardNameByUserId(int $userId): string
    {
        if ($userId <= 0) {
            return '';
        }

        try {
            $guard = DB::table('users')
                ->select('first_name', 'last_name')
                ->where('user_id', $userId)
                ->first();

            if (! $guard) {
                return '';
            }

            $fullName = trim(((string) ($guard->first_name ?? '')).' '.((string) ($guard->last_name ?? '')));

            return $fullName;
        } catch (\Throwable $e) {
            logger()->warning('Unable to resolve guard name by user_id: '.$e->getMessage(), [
                'user_id' => $userId,
            ]);

            return '';
        }
    }

    private function tokenRole(?string $jwt): ?string
    {
        if (! is_string($jwt) || trim($jwt) === '' || ! Str::contains($jwt, '.')) {
            return null;
        }

        $parts = explode('.', $jwt);
        if (count($parts) < 2) {
            return null;
        }

        $payload = $parts[1];
        $payload = str_replace(['-', '_'], ['+', '/'], $payload);
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);

        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            return null;
        }

        $json = json_decode($decoded, true);
        if (! is_array($json)) {
            return null;
        }

        $role = $json['role'] ?? null;

        return is_string($role) ? Str::lower($role) : null;
    }
}
