<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\OfficeScanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuardAlertController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'list' => ['nullable', 'in:completed,alerts'],
            'search' => ['nullable', 'string', 'max:255'],
            'severity' => ['nullable', 'in:Critical,High,Medium,Low'],
            'type' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:Completed,Ready to Exit'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:5,10,25,50,75,100'],
        ]);
        $kind = $filters['list'] ?? null;
        $perPage = $kind ? (int) ($filters['per_page'] ?? 5) : 5;
        $alertTypes = DB::table('alerts')->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", ['unresolved'])
            ->distinct()->orderBy('alert_type')->pluck('alert_type')->filter()->values();
        $unresolvedAlertsCount = DB::table('alerts')
            ->whereRaw('LOWER(TRIM(COALESCE(status, \'\'))) = ?', ['unresolved'])
            ->count();

        $unresolvedAlertsQuery = DB::table('alerts as al')
            ->leftJoin('visitor as vr', 'vr.visitor_id', '=', 'al.visitor_id')
            ->leftJoin('visit as v', 'v.visit_id', '=', 'al.visit_id')
            ->leftJoin('office_scan as os', 'os.scan_id', '=', 'al.scan_id')
            ->leftJoin('office as so', 'so.office_id', '=', 'os.office_id')
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
                'v.pass_number',
                'v.control_number',
                'vr.contact_no',
                'v.purpose_reason',
                'v.entry_time',
                'v.exit_time',
                'v.duration_minutes',
                'v.destination_text',
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
            ->when($kind === 'alerts', function ($query) use ($filters) {
                if (! empty($filters['search'])) {
                    $query->where(function ($search) use ($filters) {
                        foreach (['vr.first_name', 'vr.last_name', 'v.pass_number', 'v.control_number', 'al.message'] as $column) {
                            $search->orWhereRaw('LOWER(COALESCE('.$column.", '')) LIKE ?", ['%'.mb_strtolower($filters['search']).'%']);
                        }
                        $search->orWhereRaw("LOWER(CONCAT(COALESCE(vr.first_name, ''), ' ', COALESCE(vr.last_name, ''))) LIKE ?", ['%'.mb_strtolower($filters['search']).'%']);
                    });
                }
                if (! empty($filters['severity'])) {
                    $query->whereRaw('LOWER(al.severity) = ?', [strtolower($filters['severity'])]);
                }
                if (! empty($filters['type'])) {
                    $query->where('al.alert_type', $filters['type']);
                }
            });
        $alertPaginator = $kind === 'completed' ? null : $unresolvedAlertsQuery->paginate($perPage, ['*'], 'page', $kind ? null : 1);
        $unresolvedAlertsRows = $kind === 'completed' ? collect() : ($alertPaginator ? $alertPaginator->getCollection() : $unresolvedAlertsQuery->limit(5)->get());

        $pendingExpectedOfficesByVisit = $this->resolveCurrentExpectedOfficesByVisit(
            $unresolvedAlertsRows
                ->pluck('visit_id')
                ->filter(fn ($visitId) => (int) $visitId > 0)
                ->map(fn ($visitId) => (int) $visitId)
                ->unique()
                ->values()
                ->all()
        );

        $unresolvedAlerts = $unresolvedAlertsRows->map(function ($row) use ($pendingExpectedOfficesByVisit) {
            $firstName = trim((string) ($row->first_name ?? ''));
            $lastName = trim((string) ($row->last_name ?? ''));
            $visitorName = trim($firstName.' '.$lastName);

            $passNumber = trim((string) ($row->pass_number ?? ''));
            if ($passNumber === '') {
                $passNumber = trim((string) ($row->control_number ?? ''));
            }

            $message = trim((string) ($row->message ?? ''));
            $visitId = (int) ($row->visit_id ?? 0);
            $expectedOffice = $this->extractExpectedOfficeFromMessage($message);
            if ($expectedOffice === '' && $visitId > 0) {
                $expectedOffice = trim((string) ($pendingExpectedOfficesByVisit[$visitId] ?? ''));
            }
            if ($expectedOffice === '') {
                $expectedOffice = trim((string) ($row->primary_office_name ?? ''));
            }
            if ($expectedOffice === '') {
                $expectedOffice = trim((string) ($row->destination_text ?? ''));
            }
            if ($expectedOffice === '') {
                $expectedOffice = trim((string) ($row->purpose_reason ?? ''));
            }

            $createdAtLabel = '—';
            try {
                if (! empty($row->created_at)) {
                    $createdAtLabel = Carbon::parse($row->created_at, 'Asia/Manila')->format('M d, Y g:i A');
                }
            } catch (\Throwable $e) {
                $createdAtLabel = '—';
            }

            $scannedBy = trim(((string) ($row->scanned_by_first_name ?? '')).' '.((string) ($row->scanned_by_last_name ?? '')));
            $severity = trim((string) ($row->severity ?? ''));
            $alertType = trim((string) ($row->alert_type ?? ''));
            $alertTypeLabel = $alertType !== ''
                ? ucwords(strtolower($alertType))
                : 'General Alert';

            if ($message === '') {
                $message = $alertTypeLabel.' detected';
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
                'duration_minutes' => $this->resolveVisitDurationMinutesForDisplay(
                    $row->entry_time ?? null,
                    $row->exit_time ?? null,
                    $row->duration_minutes ?? null
                ),
                'scan_remarks' => trim((string) ($row->scan_remarks ?? '')),
                'scanned_by' => $scannedBy !== '' ? $scannedBy : 'Unknown scanner',
            ];
        })->values();

        $pendingExpectationNames = ['pending', 'not arrived', 'awaiting', 'scheduled', 'expected', 'open'];

        $readyToExitBaseQuery = DB::table('visit as v')
            ->leftJoin('exit_status as es', 'es.exit_status_id', '=', 'v.exit_status_id')
            ->whereNull('v.exit_time')
            ->where(function ($query) use ($pendingExpectationNames) {
                $query
                    ->whereRaw('LOWER(TRIM(COALESCE(es.exit_status_name, \'\'))) = ?', ['completed'])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(es.exit_status_name, \'\'))) = ?', ['ready to exit'])
                    // Also treat visit as "Ready to Exit" when all expected offices are already completed.
                    ->orWhere(function ($expectationQuery) use ($pendingExpectationNames) {
                        $expectationQuery
                            // Visit must have an expected office route.
                            ->whereExists(function ($existsQuery) {
                                $existsQuery->select(DB::raw(1))
                                    ->from('office_expectation as oe_any')
                                    ->whereColumn('oe_any.visit_id', 'v.visit_id');
                            })
                            // No expectation row should still be in a pending-like status.
                            ->whereNotExists(function ($pendingQuery) use ($pendingExpectationNames) {
                                $pendingQuery->select(DB::raw(1))
                                    ->from('office_expectation as oe_pending')
                                    ->leftJoin('expectation_status as xs_pending', 'xs_pending.expectation_status_id', '=', 'oe_pending.expectation_status_id')
                                    ->whereColumn('oe_pending.visit_id', 'v.visit_id')
                                    ->where(function ($statusQuery) use ($pendingExpectationNames) {
                                        $statusQuery
                                            ->whereNull('oe_pending.expectation_status_id')
                                            ->orWhereNull('xs_pending.status_name')
                                            ->orWhereIn(
                                                DB::raw('LOWER(TRIM(COALESCE(xs_pending.status_name, \'\')))'),
                                                $pendingExpectationNames
                                            );
                                    });
                            });
                    });
            });

        $readyToExitCount = (clone $readyToExitBaseQuery)->count('v.visit_id');

        $completedVisitorsQuery = (clone $readyToExitBaseQuery)
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
                'v.pass_number',
                'v.control_number',
                'vr.contact_no',
                'vr.visitor_photo_with_id_url',
                'o.office_name',
                'es.exit_status_name',
                'vt.visit_type_name',
            ])
            ->selectSub(function ($query) {
                $query->from('office_expectation as finished')->selectRaw('MAX(finished.arrived_at)')
                    ->whereColumn('finished.visit_id', 'v.visit_id');
            }, 'completed_at_source')
            ->orderByDesc('completed_at_source')
            ->orderByDesc('v.entry_time')
            ->orderByDesc('v.visit_id')
            ->when($kind === 'completed', function ($query) use ($filters) {
                if (! empty($filters['search'])) {
                    $query->where(function ($search) use ($filters) {
                        foreach (['vr.first_name', 'vr.last_name', 'v.control_number', 'v.pass_number', 'o.office_name'] as $column) {
                            $search->orWhereRaw('LOWER(COALESCE('.$column.", '')) LIKE ?", ['%'.mb_strtolower($filters['search']).'%']);
                        }
                        $search->orWhereRaw("LOWER(CONCAT(COALESCE(vr.first_name, ''), ' ', COALESCE(vr.last_name, ''))) LIKE ?", ['%'.mb_strtolower($filters['search']).'%']);
                    });
                }
                if (($filters['status'] ?? '') === 'Completed') {
                    $query->whereRaw("LOWER(TRIM(COALESCE(es.exit_status_name, ''))) = ?", ['completed']);
                }
                if (($filters['status'] ?? '') === 'Ready to Exit') {
                    $query->whereRaw("LOWER(TRIM(COALESCE(es.exit_status_name, ''))) <> ?", ['completed']);
                }
            });
        $completedPaginator = $kind === 'alerts' ? null : $completedVisitorsQuery->paginate($perPage, ['*'], 'page', $kind ? null : 1);
        $completedVisitorsRows = $kind === 'alerts' ? collect() : ($completedPaginator ? $completedPaginator->getCollection() : $completedVisitorsQuery->limit(5)->get());

        $completedVisitors = $completedVisitorsRows->map(function ($row) {
            $firstName = trim((string) ($row->first_name ?? ''));
            $lastName = trim((string) ($row->last_name ?? ''));
            $fullName = trim($firstName.' '.$lastName);

            $officeName = trim((string) ($row->office_name ?? ''));
            if ($officeName === '') {
                $officeName = trim((string) ($row->purpose_reason ?? ''));
            }

            $passNumber = trim((string) ($row->pass_number ?? ''));
            if ($passNumber === '') {
                $passNumber = trim((string) ($row->control_number ?? ''));
            }

            $completedAtSource = $row->completed_at_source ?? null;
            $completedAtLabel = '—';

            try {
                if (! empty($completedAtSource)) {
                    $completedAtLabel = Carbon::parse($completedAtSource, 'Asia/Manila')->format('M d, g:i A');
                }
            } catch (\Throwable $e) {
                $completedAtLabel = '—';
            }

            $initials = strtoupper(substr($firstName, 0, 1).substr($lastName, 0, 1));
            if ($initials === '') {
                $initials = 'NA';
            }

            return [
                'visit_id' => (int) ($row->visit_id ?? 0),
                'initials' => $initials,
                'photo_url' => app(OfficeScanService::class)->resolveVisitorPhotoUrl($row->visitor_photo_with_id_url ?? null),
                'visitor_name' => $fullName !== '' ? $fullName : 'Unknown Visitor',
                'office_name' => $officeName !== '' ? $officeName : 'No office assigned',
                'pass_number' => $passNumber !== '' ? $passNumber : 'No pass/control number',
                'control_number' => trim((string) ($row->control_number ?? '')),
                'completed_at' => $completedAtLabel,
                'status' => trim((string) ($row->exit_status_name ?? 'Ready to Exit')) ?: 'Ready to Exit',
            ];
        })->values();

        if ($kind) {
            $paginator = $kind === 'completed' ? $completedPaginator : $alertPaginator;
            $rows = $kind === 'completed' ? $completedVisitors : $unresolvedAlerts;

            return response()->json([
                'success' => true,
                'html' => view('guard.partials.active-alert-records', compact('kind', 'rows'))->render(),
                'alerts' => $kind === 'alerts' ? $unresolvedAlerts : [],
                'meta' => ['current_page' => $paginator->currentPage(), 'last_page' => $paginator->lastPage(), 'total' => $paginator->total(), 'from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0],
            ]);
        }

        return view('guard.alert', [
            'unresolvedAlertsCount' => $unresolvedAlertsCount,
            'readyToExitCount' => $readyToExitCount,
            'activeAlertsCount' => $unresolvedAlertsCount,
            'completedVisitors' => $completedVisitors,
            'unresolvedAlerts' => $unresolvedAlerts,
            'alertTypes' => $alertTypes,
            'completedPaginator' => $completedPaginator,
            'alertPaginator' => $alertPaginator,
        ]);
    }

    /**
     * Prefer the office named in unauthorized alert messages (accurate at alert time).
     */
    private function extractExpectedOfficeFromMessage(string $message): string
    {
        if ($message === '') {
            return '';
        }

        if (preg_match('/was expected at\s+(.+?)\.?\s*$/i', $message, $matches) === 1) {
            return trim((string) ($matches[1] ?? ''));
        }

        return '';
    }

    /**
     * First unfinished office_expectation per visit (current enrollee/visitor route step).
     *
     * @param  array<int>  $visitIds
     * @return array<int, string> visit_id => office_name
     */
    private function resolveCurrentExpectedOfficesByVisit(array $visitIds): array
    {
        if ($visitIds === []) {
            return [];
        }

        $rows = DB::table('office_expectation as oe')
            ->leftJoin('office as o', 'o.office_id', '=', 'oe.office_id')
            ->whereIn('oe.visit_id', $visitIds)
            ->whereNull('oe.arrived_at')
            ->select([
                'oe.visit_id',
                'oe.expected_order',
                'oe.expectation_id',
                'o.office_name',
            ])
            ->orderBy('oe.visit_id')
            ->orderBy('oe.expected_order')
            ->orderBy('oe.expectation_id')
            ->get();

        $byVisit = [];
        foreach ($rows as $row) {
            $visitId = (int) ($row->visit_id ?? 0);
            if ($visitId <= 0 || isset($byVisit[$visitId])) {
                continue;
            }

            $officeName = trim((string) ($row->office_name ?? ''));
            if ($officeName !== '') {
                $byVisit[$visitId] = $officeName;
            }
        }

        return $byVisit;
    }

    /**
     * Minutes on-site: if no exit yet, use entry_time → now (live); if exited, prefer stored duration_minutes else entry→exit.
     */
    private function resolveVisitDurationMinutesForDisplay($entryTime, $exitTime, $durationColumn): ?int
    {
        try {
            if (empty($entryTime)) {
                return null;
            }
            $entry = Carbon::parse($entryTime);
            if (empty($exitTime)) {
                return max(0, $entry->diffInMinutes(now()));
            }
            $exit = Carbon::parse($exitTime);
            if ($durationColumn !== null && is_numeric($durationColumn)) {
                return max(0, (int) $durationColumn);
            }

            return max(0, $entry->diffInMinutes($exit));
        } catch (\Throwable $e) {
            return null;
        }
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

        $alert = DB::table('alerts as al')
            ->leftJoin('visitor as vr', 'vr.visitor_id', '=', 'al.visitor_id')
            ->where('al.alert_id', (int) $alertId)
            ->select([
                'al.alert_id',
                'al.alert_type',
                'vr.first_name',
                'vr.last_name',
            ])
            ->first();

        $visitorName = trim(((string) ($alert->first_name ?? '')).' '.((string) ($alert->last_name ?? '')));
        $alertType = trim((string) ($alert->alert_type ?? 'Alert'));
        $forVisitor = $visitorName !== '' ? ' for '.$visitorName : '';

        ActivityLogService::log(
            action: 'Resolved Alert',
            module: 'Alerts',
            description: ActivityLogService::actorLabel().' resolved '.($alertType !== '' ? $alertType.' ' : '').'Alert #'.$alertId.$forVisitor.'.',
            entityType: 'Alert',
            entityId: (int) $alertId,
            oldValues: ['status' => 'Unresolved'],
            newValues: [
                'status' => 'Resolved',
                'resolution_notes' => $validated['resolution_notes'],
            ]
        );

        return response()->json([
            'message' => 'Alert resolved successfully.',
        ]);
    }
}
