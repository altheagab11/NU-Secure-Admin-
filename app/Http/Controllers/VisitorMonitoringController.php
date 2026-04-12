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

        $officeOptions = $rows
            ->pluck('destination')
            ->filter(fn ($v) => filled($v))
            ->unique()
            ->sort()
            ->values();

        $statusOptions = collect(['Arrived', 'In Transit', 'Completed', 'Overstay']);

        $filteredRows = $rows->filter(function (array $row) use ($search, $officeFilter, $statusFilter) {
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

            return $matchesSearch && $matchesOffice && $matchesStatus;
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

        return view('admin.visitor', [
            'rows' => $paginatedRows,
            'totalRows' => $rows->count(),
            'filteredCount' => $filteredCount,
            'officeOptions' => $officeOptions,
            'statusOptions' => $statusOptions,
            'activeByOffice' => $activeByOffice,
            'maxOfficeCount' => $maxOfficeCount,
            'recentVisitors' => $recentVisitors,
            'correctOfficeScans' => $correctOfficeScans,
            'filters' => [
                'search' => $search,
                'office' => $officeFilter,
                'status' => $statusFilter,
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
                'duration_minutes' => $durationMinutes,
                'duration_label' => $this->formatDurationLabel($durationMinutes),
                'status' => $status,
                'status_class' => $this->statusClass($status),
                'alert' => $latestAlertType ?: 'None',
                'raw_entry_time' => $entry ? $entry->toIso8601String() : null,
            ];
        })->values();
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
