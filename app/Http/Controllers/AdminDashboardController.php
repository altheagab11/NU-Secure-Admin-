<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateFilter = strtolower(trim((string) $request->query('date_filter', '')));
        $officeFilter = (int) $request->query('office', 0);
        $visitorTypeFilter = (int) $request->query('visitor_type', 0);
        $statusFilterRaw = trim((string) $request->query('status', ''));
        $statusFilter = strtolower($statusFilterRaw);

        $allowedDateFilters = ['today', 'week', 'month'];
        if (!in_array($dateFilter, $allowedDateFilters, true)) {
            $dateFilter = '';
        }

        $applyDateFilter = function ($query, string $column) use ($dateFilter) {
            if ($dateFilter === 'today') {
                $query->whereDate($column, today());
            } elseif ($dateFilter === 'week') {
                $query->whereDate($column, '>=', today()->subDays(6));
            } elseif ($dateFilter === 'month') {
                $query->whereDate($column, '>=', today()->subDays(29));
            }

            return $query;
        };

        $applyVisitFilters = function ($query, string $dateColumn = 'entry_time') use (
            $applyDateFilter,
            $officeFilter,
            $visitorTypeFilter,
            $statusFilter
        ) {
            $applyDateFilter($query, $dateColumn);

            if ($officeFilter > 0) {
                $query->where('visit.primary_office_id', $officeFilter);
            }

            if ($visitorTypeFilter > 0) {
                $query->where('visit.visit_type_id', $visitorTypeFilter);
            }

            if ($statusFilter === 'inside') {
                $query->whereNull('visit.exit_time');
            } elseif (in_array($statusFilter, ['exited', 'completed'], true)) {
                $query->whereNotNull('visit.exit_time');
            }

            return $query;
        };

        $applyAlertFilters = function ($query) use (
            $applyDateFilter,
            $officeFilter,
            $visitorTypeFilter,
            $statusFilter
        ) {
            $applyDateFilter($query, 'a.created_at');

            if ($officeFilter > 0 || $visitorTypeFilter > 0) {
                $query->leftJoin('visit as vf', 'vf.visit_id', '=', 'a.visit_id');
            }

            if ($officeFilter > 0) {
                $query->where(function ($q) use ($officeFilter) {
                    $q->where('vf.primary_office_id', $officeFilter)
                        ->orWhereExists(function ($sub) use ($officeFilter) {
                            $sub->select(DB::raw(1))
                                ->from('office_scan as ofs')
                                ->whereColumn('ofs.scan_id', 'a.scan_id')
                                ->where('ofs.office_id', $officeFilter);
                        });
                });
            }

            if ($visitorTypeFilter > 0) {
                $query->where('vf.visit_type_id', $visitorTypeFilter);
            }

            if (in_array($statusFilter, ['resolved', 'unresolved'], true)) {
                $query->whereRaw("LOWER(TRIM(COALESCE(a.status, ''))) = ?", [$statusFilter]);
            }

            return $query;
        };

        $totalVisitorsTodayQuery = DB::table('visit');
        $applyVisitFilters($totalVisitorsTodayQuery, 'entry_time');
        $totalVisitorsToday = $totalVisitorsTodayQuery->count();

        $currentlyInside = DB::table('visit')
            ->whereNotNull('entry_time')
            ->whereNull('exit_time');
        $applyVisitFilters($currentlyInside, 'entry_time');
        $currentlyInside = $currentlyInside
            ->count();

        $activeOfficesQuery = DB::table('office')
            ->where('is_active', true)
            ->when($officeFilter > 0, function ($query) use ($officeFilter) {
                $query->where('office_id', $officeFilter);
            });
        $activeOffices = $activeOfficesQuery->count();

        $averageDurationQuery = DB::table('visit')
            ->whereNotNull('duration_minutes');
        $applyVisitFilters($averageDurationQuery, 'entry_time');
        $averageDurationMinutes = $averageDurationQuery->avg('duration_minutes');

        $averageDuration = $averageDurationMinutes !== null
            ? (int) round((float) $averageDurationMinutes) . 'm'
            : '0m';

        $severityCountsToday = DB::table('alerts as a')
            ->selectRaw("LOWER(TRIM(COALESCE(severity, ''))) as severity_key, COUNT(*) as total")
            ->groupBy('severity_key');
        $applyAlertFilters($severityCountsToday);
        $severityCountsToday = $severityCountsToday->pluck('total', 'severity_key');

        $criticalAlerts = (int) ($severityCountsToday['critical'] ?? 0);
        $highAlerts = (int) ($severityCountsToday['high'] ?? 0);
        $mediumAlerts = (int) ($severityCountsToday['medium'] ?? 0);
        $lowAlerts = (int) ($severityCountsToday['low'] ?? 0);

        $totalAlertsQuery = DB::table('alerts as a');
        $applyAlertFilters($totalAlertsQuery);
        $totalAlertsToday = $totalAlertsQuery->count();

        $unresolvedAlertsQuery = DB::table('alerts as a')
            ->whereRaw("LOWER(TRIM(COALESCE(a.status, ''))) = ?", ['unresolved']);
        $applyAlertFilters($unresolvedAlertsQuery);
        $unresolvedAlerts = $unresolvedAlertsQuery->count();

        $mostCommonAlertQuery = DB::table('alerts as a')
            ->select('a.alert_type', DB::raw('COUNT(*) as total'))
            ->whereNotNull('a.alert_type')
            ->whereRaw("TRIM(COALESCE(a.alert_type, '')) <> ''")
            ->groupBy('a.alert_type')
            ->orderByDesc('total')
            ->orderBy('a.alert_type');
        $applyAlertFilters($mostCommonAlertQuery);
        $mostCommonAlert = $mostCommonAlertQuery->value('a.alert_type');

        $visitorTrendRows = DB::table('visit')
            ->selectRaw('DATE(entry_time) as visit_date, COUNT(*) as total_visitors')
            ->whereDate('entry_time', '>=', today()->subDays(6));
        $applyVisitFilters($visitorTrendRows, 'entry_time');
        $visitorTrendRows = $visitorTrendRows
            ->groupBy('visit_date')
            ->orderBy('visit_date')
            ->get();

        $visitorTrendByDate = $visitorTrendRows
            ->mapWithKeys(function ($row) {
                return [(string) $row->visit_date => (int) $row->total_visitors];
            });

        $visitorTrendLabels = [];
        $visitorTrendData = [];
        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $date = Carbon::today()->subDays($daysAgo);
            $dateKey = $date->toDateString();
            $visitorTrendLabels[] = $date->format('M d');
            $visitorTrendData[] = (int) ($visitorTrendByDate[$dateKey] ?? 0);
        }

        $visitorStatusRows = DB::table('visit')
            ->selectRaw("CASE WHEN exit_time IS NULL THEN 'Currently Inside' ELSE 'Exited' END as status_label, COUNT(*) as total");
        $applyVisitFilters($visitorStatusRows, 'entry_time');
        $visitorStatusRows = $visitorStatusRows
            ->groupBy('status_label')
            ->orderBy('status_label')
            ->get();

        $visitorStatusCounts = $visitorStatusRows->mapWithKeys(function ($row) {
            return [(string) $row->status_label => (int) $row->total];
        });

        $visitorStatusLabels = ['Currently Inside', 'Exited'];
        $visitorStatusData = [
            (int) ($visitorStatusCounts['Currently Inside'] ?? 0),
            (int) ($visitorStatusCounts['Exited'] ?? 0),
        ];

        $visitorsByHourQuery = DB::table('visit')
            ->selectRaw('EXTRACT(HOUR FROM entry_time) as visit_hour, COUNT(*) as total_visitors')
            ->whereNotNull('entry_time');
        if ($dateFilter === '') {
            $visitorsByHourQuery->whereDate('entry_time', today());
        }
        $applyVisitFilters($visitorsByHourQuery, 'entry_time');
        $visitorsByHourRows = $visitorsByHourQuery
            ->groupBy('visit_hour')
            ->orderBy('visit_hour')
            ->get();

        $visitorsByHourMap = $visitorsByHourRows->mapWithKeys(function ($row) {
            return [(int) $row->visit_hour => (int) $row->total_visitors];
        });

        $visitorHourLabels = [];
        $visitorHourData = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $displayHour = Carbon::createFromTime($hour, 0)->format('g A');
            $visitorHourLabels[] = $displayHour;
            $visitorHourData[] = (int) ($visitorsByHourMap[$hour] ?? 0);
        }

        $visitorsByOfficeQuery = DB::table('visit as v')
            ->join('office as o', 'v.primary_office_id', '=', 'o.office_id')
            ->select('o.office_name', DB::raw('COUNT(*) as total_visitors'))
            ->whereNotNull('v.entry_time');
        if ($dateFilter === '') {
            $visitorsByOfficeQuery->whereDate('v.entry_time', today());
        }
        $visitorsByOfficeQuery
            ->when($dateFilter === 'today', fn ($query) => $query->whereDate('v.entry_time', today()))
            ->when($dateFilter === 'week', fn ($query) => $query->whereDate('v.entry_time', '>=', today()->subDays(6)))
            ->when($dateFilter === 'month', fn ($query) => $query->whereDate('v.entry_time', '>=', today()->subDays(29)))
            ->when($officeFilter > 0, fn ($query) => $query->where('v.primary_office_id', $officeFilter))
            ->when($visitorTypeFilter > 0, fn ($query) => $query->where('v.visit_type_id', $visitorTypeFilter))
            ->when($statusFilter === 'inside', fn ($query) => $query->whereNull('v.exit_time'))
            ->when(in_array($statusFilter, ['exited', 'completed'], true), fn ($query) => $query->whereNotNull('v.exit_time'))
            ->groupBy('o.office_name')
            ->orderByDesc('total_visitors')
            ->orderBy('o.office_name');
        $visitorsByOfficeRows = $visitorsByOfficeQuery->get();

        $visitorOfficeLabels = $visitorsByOfficeRows->pluck('office_name')->values()->all();
        $visitorOfficeData = $visitorsByOfficeRows->pluck('total_visitors')->map(fn ($v) => (int) $v)->values()->all();

        $latestScanPerVisit = DB::table('office_scan as os')
            ->select('os.visit_id', DB::raw('MAX(os.scan_id) as latest_scan_id'))
            ->groupBy('os.visit_id');

        $liveVisitorRows = DB::table('visit as v')
            ->join('visitor as vis', 'vis.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'v.primary_office_id')
            ->leftJoinSub($latestScanPerVisit, 'latest_scan', function ($join) {
                $join->on('latest_scan.visit_id', '=', 'v.visit_id');
            })
            ->leftJoin('office_scan as os', 'os.scan_id', '=', 'latest_scan.latest_scan_id')
            ->leftJoin('validation_status as vs', 'vs.validation_status_id', '=', 'os.validation_status_id')
            ->whereNotNull('v.entry_time')
            ->select([
                'v.visit_id',
                'v.entry_time',
                'v.exit_time',
                'vis.first_name',
                'vis.last_name',
                'o.office_name',
                'vs.status_name as validation_status_name',
            ])
            ->when($dateFilter === 'today', fn ($query) => $query->whereDate('v.entry_time', today()))
            ->when($dateFilter === 'week', fn ($query) => $query->whereDate('v.entry_time', '>=', today()->subDays(6)))
            ->when($dateFilter === 'month', fn ($query) => $query->whereDate('v.entry_time', '>=', today()->subDays(29)))
            ->when($officeFilter > 0, fn ($query) => $query->where('v.primary_office_id', $officeFilter))
            ->when($visitorTypeFilter > 0, fn ($query) => $query->where('v.visit_type_id', $visitorTypeFilter))
            ->when($statusFilter === 'inside', fn ($query) => $query->whereNull('v.exit_time'))
            ->when(in_array($statusFilter, ['exited', 'completed'], true), fn ($query) => $query->whereNotNull('v.exit_time'))
            ->when($statusFilter === 'in transit', fn ($query) => $query->whereRaw("LOWER(TRIM(COALESCE(vs.status_name, ''))) like ?", ['%transit%']))
            ->orderByDesc('v.entry_time')
            ->paginate(10, ['*'], 'live_page')
            ->withQueryString();

        $liveVisitorRows->setCollection(
            $liveVisitorRows->getCollection()->map(function ($row) {
                $name = trim(((string) ($row->first_name ?? '')) . ' ' . ((string) ($row->last_name ?? '')));
                if ($name === '') {
                    $name = 'Unknown Visitor';
                }

                $status = 'Inside';
                $validationStatus = strtolower(trim((string) ($row->validation_status_name ?? '')));
                if (!empty($row->exit_time)) {
                    $status = 'Exited';
                } elseif (str_contains($validationStatus, 'transit')) {
                    $status = 'In Transit';
                }

                $timeIn = '—';
                try {
                    if (!empty($row->entry_time)) {
                        $timeIn = Carbon::parse($row->entry_time)->format('h:i A');
                    }
                } catch (\Throwable $e) {
                    $timeIn = '—';
                }

                return [
                    'visit_id' => (int) ($row->visit_id ?? 0),
                    'name' => $name,
                    'status' => $status,
                    'location' => trim((string) ($row->office_name ?? '')) ?: 'No office set',
                    'time_in' => $timeIn,
                ];
            })
        );

        $recentAlertRows = DB::table('alerts as a')
            ->leftJoin('visit as v', 'v.visit_id', '=', 'a.visit_id')
            ->leftJoin('visitor as vis', 'vis.visitor_id', '=', 'a.visitor_id')
            ->leftJoin('visitor as vv', 'vv.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('office_scan as os', 'os.scan_id', '=', 'a.scan_id')
            ->select([
                'a.alert_id',
                'a.created_at',
                'a.alert_type',
                'a.severity',
                'a.status',
                'vis.first_name as direct_first_name',
                'vis.last_name as direct_last_name',
                'vv.first_name as visit_first_name',
                'vv.last_name as visit_last_name',
            ])
            ->when($dateFilter === 'today', fn ($query) => $query->whereDate('a.created_at', today()))
            ->when($dateFilter === 'week', fn ($query) => $query->whereDate('a.created_at', '>=', today()->subDays(6)))
            ->when($dateFilter === 'month', fn ($query) => $query->whereDate('a.created_at', '>=', today()->subDays(29)))
            ->when($officeFilter > 0, function ($query) use ($officeFilter) {
                $query->where(function ($q) use ($officeFilter) {
                    $q->where('v.primary_office_id', $officeFilter)
                        ->orWhere('os.office_id', $officeFilter);
                });
            })
            ->when($visitorTypeFilter > 0, fn ($query) => $query->where('v.visit_type_id', $visitorTypeFilter))
            ->when(in_array($statusFilter, ['resolved', 'unresolved'], true), fn ($query) => $query->whereRaw("LOWER(TRIM(COALESCE(a.status, ''))) = ?", [$statusFilter]))
            ->orderByDesc('a.created_at')
            ->orderByDesc('a.alert_id')
            ->paginate(10, ['*'], 'alerts_page')
            ->withQueryString();

        $recentAlertRows->setCollection(
            $recentAlertRows->getCollection()->map(function ($row) {
                $firstName = trim((string) ($row->direct_first_name ?? ''));
                $lastName = trim((string) ($row->direct_last_name ?? ''));

                if ($firstName === '' && $lastName === '') {
                    $firstName = trim((string) ($row->visit_first_name ?? ''));
                    $lastName = trim((string) ($row->visit_last_name ?? ''));
                }

                $visitorName = trim($firstName . ' ' . $lastName);
                if ($visitorName === '') {
                    $visitorName = 'Unknown Visitor';
                }

                $timeLabel = '—';
                try {
                    if (!empty($row->created_at)) {
                        $timeLabel = Carbon::parse($row->created_at)->format('h:i A');
                    }
                } catch (\Throwable $e) {
                    $timeLabel = '—';
                }

                return [
                    'alert_id' => (int) ($row->alert_id ?? 0),
                    'time' => $timeLabel,
                    'visitor' => $visitorName,
                    'type' => trim((string) ($row->alert_type ?? '')) ?: 'General Alert',
                    'severity' => ucfirst(strtolower(trim((string) ($row->severity ?? '')) ?: 'Low')),
                    'status' => ucfirst(strtolower(trim((string) ($row->status ?? '')) ?: 'Unresolved')),
                ];
            })
        );

        $officeOptions = DB::table('office')
            ->select('office_id', 'office_name')
            ->where('is_active', true)
            ->orderBy('office_name')
            ->get();

        $visitTypeOptions = DB::table('visit_type')
            ->select('visit_type_id', 'visit_type_name')
            ->orderBy('visit_type_name')
            ->get();

        $statusOptions = [
            'Inside',
            'In Transit',
            'Exited',
            'Completed',
            'Resolved',
            'Unresolved',
        ];

        $peakVisitorHourRow = DB::table('visit')
            ->selectRaw('EXTRACT(HOUR FROM entry_time) as visit_hour, COUNT(*) as total_visitors')
            ->whereDate('entry_time', today())
            ->whereNotNull('entry_time')
            ->groupBy('visit_hour')
            ->orderByDesc('total_visitors')
            ->orderBy('visit_hour')
            ->first();

        $peakVisitorHourInsight = 'No visitor entries yet today.';
        if ($peakVisitorHourRow && $peakVisitorHourRow->visit_hour !== null) {
            $hour = (int) $peakVisitorHourRow->visit_hour;
            $hourStart = Carbon::createFromTime($hour, 0)->format('g:i A');
            $hourEnd = Carbon::createFromTime($hour, 0)->addHour()->format('g:i A');
            $peakVisitorHourInsight = "Peak visitor hour is {$hourStart} to {$hourEnd}.";
        }

        $topOfficeTodayRow = DB::table('visit as v')
            ->join('office as o', 'v.primary_office_id', '=', 'o.office_id')
            ->select('o.office_name', DB::raw('COUNT(*) as total_visitors'))
            ->whereDate('v.entry_time', today())
            ->groupBy('o.office_name')
            ->orderByDesc('total_visitors')
            ->orderBy('o.office_name')
            ->first();

        $topOfficeTodayInsight = 'No office visits recorded today.';
        if ($topOfficeTodayRow && !empty($topOfficeTodayRow->office_name)) {
            $topOfficeTodayInsight = $topOfficeTodayRow->office_name . ' receives the most visitors today.';
        }

        $unresolvedAlertsInsight = $unresolvedAlerts . ' unresolved alert' . ($unresolvedAlerts === 1 ? '' : 's') . ' need immediate attention.';

        $longestAvgDurationOfficeRow = DB::table('visit as v')
            ->join('office as o', 'v.primary_office_id', '=', 'o.office_id')
            ->select('o.office_name', DB::raw('AVG(v.duration_minutes) as average_duration'))
            ->whereNotNull('v.duration_minutes')
            ->groupBy('o.office_name')
            ->orderByDesc('average_duration')
            ->orderBy('o.office_name')
            ->first();

        $longestAvgDurationInsight = 'No completed visit duration data yet.';
        if ($longestAvgDurationOfficeRow && !empty($longestAvgDurationOfficeRow->office_name)) {
            $avgDuration = (int) round((float) ($longestAvgDurationOfficeRow->average_duration ?? 0));
            $longestAvgDurationInsight = $longestAvgDurationOfficeRow->office_name . ' has the longest average visit duration (' . $avgDuration . 'm).';
        }

        return view('admin.dashboard', [
            'totalVisitorsToday' => $totalVisitorsToday,
            'currentlyInside' => $currentlyInside,
            'activeOffices' => $activeOffices,
            'averageDuration' => $averageDuration,
            'criticalAlerts' => $criticalAlerts,
            'highAlerts' => $highAlerts,
            'mediumAlerts' => $mediumAlerts,
            'lowAlerts' => $lowAlerts,
            'totalAlertsToday' => $totalAlertsToday,
            'unresolvedAlerts' => $unresolvedAlerts,
            'mostCommonAlert' => $mostCommonAlert ?: 'N/A',
            'visitorTrendLabels' => $visitorTrendLabels,
            'visitorTrendData' => $visitorTrendData,
            'visitorStatusLabels' => $visitorStatusLabels,
            'visitorStatusData' => $visitorStatusData,
            'visitorHourLabels' => $visitorHourLabels,
            'visitorHourData' => $visitorHourData,
            'visitorOfficeLabels' => $visitorOfficeLabels,
            'visitorOfficeData' => $visitorOfficeData,
            'liveVisitors' => $liveVisitorRows,
            'recentAlerts' => $recentAlertRows,
            'officeOptions' => $officeOptions,
            'visitTypeOptions' => $visitTypeOptions,
            'statusOptions' => $statusOptions,
            'selectedDateFilter' => $dateFilter,
            'selectedOfficeFilter' => $officeFilter,
            'selectedVisitorTypeFilter' => $visitorTypeFilter,
            'selectedStatusFilter' => $statusFilterRaw,
            'peakVisitorHourInsight' => $peakVisitorHourInsight,
            'topOfficeTodayInsight' => $topOfficeTodayInsight,
            'unresolvedAlertsInsight' => $unresolvedAlertsInsight,
            'longestAvgDurationInsight' => $longestAvgDurationInsight,
        ]);
    }
}
