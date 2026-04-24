<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuardAlertController extends Controller
{
    public function index(Request $request)
    {
        $unresolvedAlertsCount = DB::table('alerts')
            ->whereRaw('LOWER(TRIM(COALESCE(status, \'\'))) = ?', ['unresolved'])
            ->count();

        $unresolvedAlertsRows = DB::table('alerts as al')
            ->leftJoin('visitor as vr', 'vr.visitor_id', '=', 'al.visitor_id')
            ->leftJoin('visit as v', 'v.visit_id', '=', 'al.visit_id')
            ->leftJoin('office_scan as os', 'os.scan_id', '=', 'al.scan_id')
            ->leftJoin('office as so', 'so.office_id', '=', 'os.office_id')
            ->leftJoin('office_expectation as oe', function ($join) {
                $join->on('oe.visit_id', '=', 'al.visit_id')
                    ->where('oe.expected_order', '=', 1);
            })
            ->leftJoin('office as eo', 'eo.office_id', '=', 'oe.office_id')
            ->leftJoin('office as po', 'po.office_id', '=', 'v.primary_office_id')
            ->leftJoin('users as su', 'su.user_id', '=', 'os.scanned_by_user_id')
            ->select([
                'al.alert_id',
                'al.visit_id',
                'al.scan_id',
                'al.alert_type',
                'al.severity',
                'al.message',
                'al.status',
                'al.created_at',
                'al.resolved_at',
                'al.resolution_notes',
                'vr.first_name',
                'vr.last_name',
                'vr.pass_number',
                'vr.control_number',
                'vr.contact_no',
                'v.purpose_reason',
                'v.entry_time',
                'v.exit_time',
                'v.duration_minutes',
                'eo.office_name as expected_office_name',
                'po.office_name as primary_office_name',
                'so.office_name as scanned_office_name',
                'os.scan_time',
                'os.remarks as scan_remarks',
                'su.first_name as scanned_by_first_name',
                'su.last_name as scanned_by_last_name',
            ])
            ->whereRaw('LOWER(TRIM(COALESCE(al.status, \'\'))) = ?', ['unresolved'])
            ->orderByDesc('al.created_at')
            ->orderByDesc('al.alert_id')
            ->limit(20)
            ->get();

        $unresolvedAlerts = $unresolvedAlertsRows->map(function ($row) {
            $firstName = trim((string) ($row->first_name ?? ''));
            $lastName = trim((string) ($row->last_name ?? ''));
            $visitorName = trim($firstName . ' ' . $lastName);

            $passNumber = trim((string) ($row->pass_number ?? ''));
            if ($passNumber === '') {
                $passNumber = trim((string) ($row->control_number ?? ''));
            }

            $expectedOffice = trim((string) ($row->expected_office_name ?? ''));
            if ($expectedOffice === '') {
                $expectedOffice = trim((string) ($row->primary_office_name ?? ''));
            }
            if ($expectedOffice === '') {
                $expectedOffice = trim((string) ($row->purpose_reason ?? ''));
            }

            $createdAtLabel = '—';
            try {
                if (!empty($row->created_at)) {
                    $createdAtLabel = Carbon::parse($row->created_at)->format('M d, Y g:i A');
                }
            } catch (\Throwable $e) {
                $createdAtLabel = '—';
            }

            $scannedBy = trim(((string) ($row->scanned_by_first_name ?? '')) . ' ' . ((string) ($row->scanned_by_last_name ?? '')));
            $severity = trim((string) ($row->severity ?? ''));
            $alertType = trim((string) ($row->alert_type ?? ''));
            $alertTypeLabel = $alertType !== ''
                ? ucwords(strtolower($alertType))
                : 'General Alert';

            $message = trim((string) ($row->message ?? ''));
            if ($message === '') {
                $message = $alertTypeLabel . ' detected';
            }

            return [
                'alert_id' => (int) ($row->alert_id ?? 0),
                'visit_id' => (int) ($row->visit_id ?? 0),
                'scan_id' => (int) ($row->scan_id ?? 0),
                'alert_type' => $alertTypeLabel,
                'status' => trim((string) ($row->status ?? 'Unresolved')) ?: 'Unresolved',
                'severity' => $severity !== '' ? ucfirst(strtolower($severity)) : 'High',
                'visitor_name' => $visitorName !== '' ? $visitorName : 'Unknown Visitor',
                'pass_number' => $passNumber !== '' ? $passNumber : 'No pass/control number',
                'control_number' => trim((string) ($row->control_number ?? '')),
                'contact_no' => trim((string) ($row->contact_no ?? '')),
                'expected_office' => $expectedOffice !== '' ? $expectedOffice : 'No expected office',
                'scanned_office' => trim((string) ($row->scanned_office_name ?? '')) ?: 'No scanned office',
                'message' => $message,
                'time' => $createdAtLabel,
                'created_at' => $row->created_at,
                'resolved_at' => $row->resolved_at,
                'resolution_notes' => trim((string) ($row->resolution_notes ?? '')),
                'purpose_reason' => trim((string) ($row->purpose_reason ?? '')),
                'entry_time' => $row->entry_time,
                'exit_time' => $row->exit_time,
                'duration_minutes' => $row->duration_minutes !== null ? (int) $row->duration_minutes : null,
                'scan_remarks' => trim((string) ($row->scan_remarks ?? '')),
                'scanned_by' => $scannedBy !== '' ? $scannedBy : 'Unknown scanner',
            ];
        })->values();

        $readyToExitBaseQuery = DB::table('visit as v')
            ->leftJoin('exit_status as es', 'es.exit_status_id', '=', 'v.exit_status_id')
            ->whereNull('v.exit_time')
            ->where(function ($query) {
                $query
                    ->whereRaw('LOWER(TRIM(COALESCE(es.exit_status_name, \'\'))) = ?', ['completed'])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(es.exit_status_name, \'\'))) = ?', ['ready to exit']);
            });

        $readyToExitCount = (clone $readyToExitBaseQuery)->count('v.visit_id');

        $completedVisitorsRows = (clone $readyToExitBaseQuery)
            ->leftJoin('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'v.primary_office_id')
            ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
            ->select([
                'v.visit_id',
                'v.entry_time',
                'v.exit_time',
                'v.duration_minutes',
                'v.purpose_reason',
                'vr.visitor_id',
                'vr.first_name',
                'vr.last_name',
                'vr.pass_number',
                'vr.control_number',
                'vr.contact_no',
                'vr.visitor_photo_with_id_url',
                'o.office_name',
                'es.exit_status_name',
                'vt.visit_type_name',
            ])
            ->orderByDesc('v.entry_time')
            ->orderByDesc('v.visit_id')
            ->limit(20)
            ->get();

        $completedVisitors = $completedVisitorsRows->map(function ($row) {
            $firstName = trim((string) ($row->first_name ?? ''));
            $lastName = trim((string) ($row->last_name ?? ''));
            $fullName = trim($firstName . ' ' . $lastName);

            $officeName = trim((string) ($row->office_name ?? ''));
            if ($officeName === '') {
                $officeName = trim((string) ($row->purpose_reason ?? ''));
            }

            $passNumber = trim((string) ($row->pass_number ?? ''));
            if ($passNumber === '') {
                $passNumber = trim((string) ($row->control_number ?? ''));
            }

            $completedAtSource = $row->exit_time ?: $row->entry_time;
            $completedAtLabel = '—';

            try {
                if (!empty($completedAtSource)) {
                    $completedAtLabel = Carbon::parse($completedAtSource)->format('g:i A');
                }
            } catch (\Throwable $e) {
                $completedAtLabel = '—';
            }

            $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
            if ($initials === '') {
                $initials = 'NA';
            }

            return [
                'visit_id' => (int) ($row->visit_id ?? 0),
                'initials' => $initials,
                'visitor_name' => $fullName !== '' ? $fullName : 'Unknown Visitor',
                'office_name' => $officeName !== '' ? $officeName : 'No office assigned',
                'pass_number' => $passNumber !== '' ? $passNumber : 'No pass/control number',
                'completed_at' => $completedAtLabel,
                'status' => trim((string) ($row->exit_status_name ?? 'Ready to Exit')) ?: 'Ready to Exit',
            ];
        })->values();

        return view('guard.alert', [
            'unresolvedAlertsCount' => $unresolvedAlertsCount,
            'readyToExitCount' => $readyToExitCount,
            'activeAlertsCount' => $unresolvedAlertsCount,
            'completedVisitors' => $completedVisitors,
            'unresolvedAlerts' => $unresolvedAlerts,
        ]);
    }

    public function resolve(Request $request, $alertId)
    {
        $validated = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:2000'],
        ]);

        $resolvedById = optional($request->user())->user_id
            ?? optional($request->user())->id
            ?? session('user_id');

        $updated = DB::table('alerts')
            ->where('alert_id', (int) $alertId)
            ->update([
                'status' => 'Resolved',
                'resolved_at' => now(),
                'resolved_by' => $resolvedById,
                'resolution_notes' => $validated['resolution_notes'],
            ]);

        if ($updated === 0) {
            return response()->json([
                'message' => 'Alert not found or already updated.',
            ], 404);
        }

        return response()->json([
            'message' => 'Alert resolved successfully.',
        ]);
    }
}
