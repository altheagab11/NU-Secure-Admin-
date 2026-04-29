<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalVisitorsToday = DB::table('visit')
            ->whereDate('entry_time', today())
            ->count();

        $currentlyInside = DB::table('visit')
            ->whereNotNull('entry_time')
            ->whereNull('exit_time')
            ->count();

        $activeOffices = DB::table('office')
            ->where('is_active', true)
            ->count();

        $averageDurationMinutes = DB::table('visit')
            ->whereNotNull('duration_minutes')
            ->avg('duration_minutes');

        $averageDuration = $averageDurationMinutes !== null
            ? (int) round((float) $averageDurationMinutes) . 'm'
            : '0m';

        $severityCountsToday = DB::table('alerts')
            ->selectRaw("LOWER(TRIM(COALESCE(severity, ''))) as severity_key, COUNT(*) as total")
            ->whereDate('created_at', today())
            ->groupBy('severity_key')
            ->pluck('total', 'severity_key');

        $criticalAlerts = (int) ($severityCountsToday['critical'] ?? 0);
        $highAlerts = (int) ($severityCountsToday['high'] ?? 0);
        $mediumAlerts = (int) ($severityCountsToday['medium'] ?? 0);
        $lowAlerts = (int) ($severityCountsToday['low'] ?? 0);

        $totalAlertsToday = DB::table('alerts')
            ->whereDate('created_at', today())
            ->count();

        $unresolvedAlerts = DB::table('alerts')
            ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", ['unresolved'])
            ->count();

        $mostCommonAlert = DB::table('alerts')
            ->select('alert_type', DB::raw('COUNT(*) as total'))
            ->whereNotNull('alert_type')
            ->whereRaw("TRIM(COALESCE(alert_type, '')) <> ''")
            ->groupBy('alert_type')
            ->orderByDesc('total')
            ->orderBy('alert_type')
            ->value('alert_type');

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
        ]);
    }
}
