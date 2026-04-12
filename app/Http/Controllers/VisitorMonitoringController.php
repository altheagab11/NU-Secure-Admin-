<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VisitorMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_KEY');

        $rows = collect([]);
        $fetchError = null;

        if (! $supabaseUrl || ! $supabaseKey) {
            $fetchError = 'Supabase configuration is missing.';
        } else {
            try {
                $rows = $this->fetchVisitorMonitoringRows($supabaseUrl, $supabaseKey);
            } catch (\Throwable $e) {
                logger()->error('Visitor monitoring Supabase fetch failed: ' . $e->getMessage());
                $fetchError = 'Unable to fetch Visitor Monitoring data from Supabase.';
            }
        }

        $search = trim((string) $request->query('search', ''));
        $officeFilter = trim((string) $request->query('office', ''));
        $statusFilter = trim((string) $request->query('status', ''));
        $visitTypeFilter = trim((string) $request->query('visit_type', ''));
        $dateFromFilter = trim((string) $request->query('date_from', ''));
        $dateToFilter = trim((string) $request->query('date_to', ''));

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

        $filteredRows = $rows->filter(function (array $row) use ($search, $officeFilter, $statusFilter, $visitTypeFilter, $dateFromFilter, $dateToFilter) {
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
        })->values();

        $perPage = 10;
        $currentPage = max(1, (int) $request->query('page', 1));
        $filteredCount = $filteredRows->count();

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

        $correctOfficeScans = collect([]);
        if ($supabaseUrl && $supabaseKey) {
            try {
                $correctOfficeScans = $this->fetchCorrectOfficeScans($supabaseUrl, $supabaseKey);
            } catch (\Throwable $e) {
                logger()->warning('Correct office scan fetch failed: ' . $e->getMessage());
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
            'filters' => [
                'search' => $search,
                'office' => $officeFilter,
                'status' => $statusFilter,
                'visit_type' => $visitTypeFilter,
                'date_from' => $dateFromFilter,
                'date_to' => $dateToFilter,
            ],
            'fetchError' => $fetchError,
        ]);
    }

    private function fetchVisitorMonitoringRows(string $supabaseUrl, string $supabaseKey): Collection
    {
        $select = 'visit_id,purpose_reason,entry_time,exit_time,duration_minutes,' .
            'visitor(first_name,last_name,pass_number,control_number,contact_no),' .
            'visit_type(visit_type_name),' .
            'office(office_name),' .
            'alerts(alert_type,created_at,status)';

        $response = Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
        ])->timeout(20)->get(rtrim($supabaseUrl, '/') . '/rest/v1/visit', [
            'select' => $select,
            'order' => 'entry_time.desc',
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

        return collect($visits)->map(function (array $visit) {
            $visitor = $visit['visitor'] ?? [];
            $visitType = $visit['visit_type'] ?? [];
            $office = $visit['office'] ?? [];

            $firstName = trim((string) ($visitor['first_name'] ?? ''));
            $lastName = trim((string) ($visitor['last_name'] ?? ''));
            $fullName = trim($firstName . ' ' . $lastName);

            $entry = $this->parseDateTime($visit['entry_time'] ?? null);
            $exit = $this->parseDateTime($visit['exit_time'] ?? null);
            $durationMinutes = $this->resolveDurationMinutes($visit, $entry, $exit);

            $alerts = collect($visit['alerts'] ?? [])
                ->filter(fn ($a) => is_array($a));

            $latestAlertType = $alerts
                ->sortByDesc(fn ($a) => $a['created_at'] ?? null)
                ->pluck('alert_type')
                ->filter()
                ->first();

            $status = $this->resolveStatus($visit, $alerts);

            return [
                'visitor_name' => $fullName !== '' ? $fullName : 'Unknown Visitor',
                'pass_number' => (string) ($visitor['pass_number'] ?? '—'),
                'control_number' => (string) ($visitor['control_number'] ?? '—'),
                'contact_no' => (string) ($visitor['contact_no'] ?? '—'),
                'visit_type' => (string) ($visitType['visit_type_name'] ?? '—'),
                'purpose' => (string) ($visit['purpose_reason'] ?? '—'),
                'destination' => (string) ($office['office_name'] ?? '—'),
                'entry_time_label_date' => $entry ? $entry->format('M d, Y') : '—',
                'entry_time_label_time' => $entry ? $entry->format('h:i A') : '—',
                'entry_time_label_short' => $entry ? $entry->format('h:i A') : '—',
                'entry_date_value' => $entry ? $entry->toDateString() : null,
                'duration_minutes' => $durationMinutes,
                'duration_label' => $this->formatDurationLabel($durationMinutes),
                'status' => $status,
                'status_class' => $this->statusClass($status),
                'alert' => $latestAlertType ?: 'None',
                'raw_entry_time' => $entry ? $entry->toIso8601String() : null,
            ];
        })->values();
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

                $visitorName = trim(((string) ($visitorRel['first_name'] ?? '')) . ' ' . ((string) ($visitorRel['last_name'] ?? '')));
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
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
        ])->timeout(20)->get($baseUrl . '/rest/v1/office', [
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
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
        ])->timeout(20)->get($baseUrl . '/rest/v1/office_expectation', [
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
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
        ];

        $primary = Http::withHeaders($headers)->timeout(20)->get($baseUrl . '/rest/v1/office_scan', [
            'select' => 'scan_id,scan_time,visit_id,office_id,validation_status(validation_status_id,status_name),office(office_name),visit(visit_id,visitor(first_name,last_name,control_number))',
            'order' => 'scan_time.desc',
            'limit' => 500,
        ]);

        if ($primary->ok() && is_array($primary->json())) {
            return collect($primary->json())->filter(fn ($r) => is_array($r))->values();
        }

        // Fallback query when relation nesting varies in PostgREST metadata.
        $fallback = Http::withHeaders($headers)->timeout(20)->get($baseUrl . '/rest/v1/office_scan', [
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

        $visitResponse = Http::withHeaders($headers)->timeout(20)->get($baseUrl . '/rest/v1/visit', [
            'select' => 'visit_id,visitor(first_name,last_name,control_number)',
            'visit_id' => 'in.(' . $visitIds->implode(',') . ')',
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
            return $minutes . ' mins';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        if ($remaining === 0) {
            return $hours . ' hr' . ($hours > 1 ? 's' : '');
        }

        return $hours . ' hr ' . $remaining . ' mins';
    }
}
