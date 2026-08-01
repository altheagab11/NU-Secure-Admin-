<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LiveDataController extends Controller
{
    /**
     * Lightweight fingerprint of campus activity so pages can auto-refresh on changes.
     */
    public function status(): JsonResponse
    {
        $activeVisits = (int) DB::table('visit')->whereNull('exit_time')->count();
        $maxVisitId = (int) (DB::table('visit')->max('visit_id') ?? 0);
        $maxEntryTime = (string) (DB::table('visit')->max('entry_time') ?? '');
        $maxExitTime = (string) (DB::table('visit')->max('exit_time') ?? '');

        $unresolvedAlerts = (int) DB::table('alerts')
            ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", ['unresolved'])
            ->count();
        $maxAlertId = (int) (DB::table('alerts')->max('alert_id') ?? 0);
        $maxAlertCreated = (string) (DB::table('alerts')->max('created_at') ?? '');
        $maxAlertResolved = (string) (DB::table('alerts')->max('resolved_at') ?? '');

        $maxScanId = (int) (DB::table('office_scan')->max('scan_id') ?? 0);
        $maxScanTime = (string) (DB::table('office_scan')->max('scan_time') ?? '');
        $maxArrivedExpectationId = (int) (DB::table('office_expectation')
            ->whereNotNull('arrived_at')
            ->max('expectation_id') ?? 0);

        $fingerprint = implode('|', [
            $activeVisits,
            $maxVisitId,
            $maxEntryTime,
            $maxExitTime,
            $unresolvedAlerts,
            $maxAlertId,
            $maxAlertCreated,
            $maxAlertResolved,
            $maxScanId,
            $maxScanTime,
            $maxArrivedExpectationId,
        ]);

        return response()->json([
            'success' => true,
            'fingerprint' => $fingerprint,
            'counts' => [
                'active_visits' => $activeVisits,
                'unresolved_alerts' => $unresolvedAlerts,
            ],
        ]);
    }
}
