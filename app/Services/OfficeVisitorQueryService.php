<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfficeVisitorQueryService
{
    public function __construct(protected OfficeScanService $scanService)
    {
    }

    public function philippinesTodayBounds(): array
    {
        $start = Carbon::now('Asia/Manila')->startOfDay();
        $end = Carbon::now('Asia/Manila')->endOfDay();

        return [$start, $end];
    }

    public function dashboardStats(int $officeId): array
    {
        [$start, $end] = $this->philippinesTodayBounds();

        $todaysVisitors = (int) (DB::table('office_scan as os')
            ->join('validation_status as vs', 'vs.validation_status_id', '=', 'os.validation_status_id')
            ->where('os.office_id', $officeId)
            ->whereBetween('os.scan_time', [$start, $end])
            ->whereRaw("LOWER(TRIM(COALESCE(vs.status_name, ''))) = ?", ['valid'])
            ->selectRaw('COUNT(DISTINCT os.visit_id) as aggregate')
            ->value('aggregate') ?? 0);

        $completedCheckIns = (int) DB::table('office_scan as os')
            ->join('validation_status as vs', 'vs.validation_status_id', '=', 'os.validation_status_id')
            ->where('os.office_id', $officeId)
            ->whereBetween('os.scan_time', [$start, $end])
            ->whereRaw("LOWER(TRIM(COALESCE(vs.status_name, ''))) = ?", ['valid'])
            ->count();

        $pendingOfficeScans = $this->countPendingOfficeScans($officeId);
        $expectedVisitors = $this->countExpectedVisitors($officeId);

        return [
            'todays_visitors' => $todaysVisitors,
            'pending_office_scans' => $pendingOfficeScans,
            'expected_visitors' => $expectedVisitors,
            'completed_check_ins' => $completedCheckIns,
        ];
    }

    public function countPendingOfficeScans(int $officeId): int
    {
        $activeVisitIds = DB::table('visit')->whereNull('exit_time')->pluck('visit_id');
        if ($activeVisitIds->isEmpty()) {
            return 0;
        }

        $visits = DB::table('visit as v')
            ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
            ->whereIn('v.visit_id', $activeVisitIds)
            ->select('v.visit_id', 'vt.visit_type_name')
            ->get()
            ->keyBy('visit_id');

        $count = 0;
        $routes = DB::table('office_expectation as oe')
            ->leftJoin('expectation_status as xs', 'xs.expectation_status_id', '=', 'oe.expectation_status_id')
            ->whereIn('oe.visit_id', $activeVisitIds)
            ->select([
                'oe.visit_id',
                'oe.office_id',
                'oe.expected_order',
                'oe.arrived_at',
                'xs.status_name',
            ])
            ->orderBy('oe.visit_id')
            ->orderBy('oe.expected_order')
            ->orderBy('oe.expectation_id')
            ->get()
            ->groupBy('visit_id');

        foreach ($routes as $visitId => $steps) {
            $visit = $visits->get($visitId);
            if (! $visit) {
                continue;
            }

            if (! $this->scanService->isSequentialRoute($visit)) {
                $pendingAtOffice = $steps->first(fn ($step) => (int) $step->office_id === $officeId
                    && ! $this->scanService->isExpectationDone($step));

                if ($pendingAtOffice) {
                    $count++;
                }

                continue;
            }

            $current = null;
            foreach ($steps as $step) {
                if (! $this->scanService->isExpectationDone($step)) {
                    $current = $step;
                    break;
                }
            }
            if ($current && (int) $current->office_id === $officeId) {
                $count++;
            }
        }

        return $count;
    }

    public function countExpectedVisitors(int $officeId): int
    {
        // Active visits whose route includes this office and this office step is not yet done.
        return (int) (DB::table('office_expectation as oe')
            ->join('visit as v', 'v.visit_id', '=', 'oe.visit_id')
            ->leftJoin('expectation_status as xs', 'xs.expectation_status_id', '=', 'oe.expectation_status_id')
            ->where('oe.office_id', $officeId)
            ->whereNull('v.exit_time')
            ->whereNull('oe.arrived_at')
            ->where(function ($q) {
                $q->whereNull('xs.status_name')
                    ->orWhereRaw("LOWER(TRIM(COALESCE(xs.status_name, ''))) NOT IN (?, ?, ?, ?)", [
                        'arrived', 'completed', 'complete', 'skipped',
                    ]);
            })
            ->selectRaw('COUNT(DISTINCT oe.visit_id) as aggregate')
            ->value('aggregate') ?? 0);
    }

    public function recentActivity(int $officeId, int $limit = 10, bool $todayOnly = false)
    {
        $query = DB::table('office_scan as os')
            ->join('visit as v', 'v.visit_id', '=', 'os.visit_id')
            ->join('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'os.office_id')
            ->leftJoin('validation_status as vs', 'vs.validation_status_id', '=', 'os.validation_status_id')
            ->leftJoin('users as u', 'u.user_id', '=', 'os.scanned_by_user_id')
            ->where('os.office_id', $officeId);

        if ($todayOnly) {
            [$start, $end] = $this->philippinesTodayBounds();
            $query->whereBetween('os.scan_time', [$start, $end]);
        }

        return $query
            ->select([
                'os.scan_id',
                'os.scan_time',
                'os.remarks',
                'os.visit_id',
                'v.control_number',
                'v.pass_number',
                'v.purpose_reason',
                'vr.first_name',
                'vr.last_name',
                'o.office_name',
                'vs.status_name as validation_status',
                'u.first_name as staff_first_name',
                'u.last_name as staff_last_name',
            ])
            ->orderByDesc('os.scan_time')
            ->orderByDesc('os.scan_id')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->visitor_name = trim(trim((string) $row->first_name).' '.trim((string) $row->last_name));
                $row->staff_name = trim(trim((string) ($row->staff_first_name ?? '')).' '.trim((string) ($row->staff_last_name ?? '')));
                $row->previous_office = $this->previousOfficeName((int) $row->visit_id, (int) ($row->scan_id ?? 0));
                $scanTime = filled($row->scan_time ?? null)
                    ? Carbon::parse($row->scan_time)->timezone('Asia/Manila')
                    : null;
                $row->scan_time_label = $scanTime ? $scanTime->format('g:i A') : '—';

                return $row;
            });
    }

    public function expectedVisitorsPreview(int $officeId, ?int $limit = 8)
    {
        $rows = $this->expectedVisitorsQuery($officeId)
            ->get()
            ->unique('visit_id')
            ->values()
            ->map(fn ($row) => $this->enrichExpectedRow($row, $officeId));

        if ($limit !== null) {
            $rows = $rows->take($limit)->values();
        }

        return $rows;
    }

    public function expectedVisitorsPaginated(Request $request, int $officeId, int $perPage = 10)
    {
        $search = trim((string) $request->query('search', ''));
        $date = trim((string) $request->query('date', ''));
        $status = Str::lower(trim((string) $request->query('status', '')));
        $previousOffice = trim((string) $request->query('previous_office', ''));

        $rows = $this->expectedVisitorsQuery($officeId)->get()->unique('visit_id')->values();

        $mapped = $rows->map(fn ($row) => $this->enrichExpectedRow($row, $officeId));

        if ($search !== '') {
            $needle = Str::lower($search);
            $mapped = $mapped->filter(function ($row) use ($needle) {
                $haystack = Str::lower(implode(' ', [
                    $row->visitor_name ?? '',
                    $row->control_number ?? '',
                    $row->pass_number ?? '',
                    $row->purpose_reason ?? '',
                ]));

                return str_contains($haystack, $needle);
            })->values();
        }

        if ($date !== '') {
            try {
                $day = Carbon::parse($date, 'Asia/Manila')->toDateString();
                $mapped = $mapped->filter(function ($row) use ($day) {
                    if (empty($row->entry_time)) {
                        return false;
                    }

                    return Carbon::parse($row->entry_time, 'Asia/Manila')->toDateString() === $day;
                })->values();
            } catch (\Throwable $e) {
            }
        }

        if ($status !== '') {
            $mapped = $mapped->filter(fn ($row) => Str::lower((string) ($row->route_status_key ?? '')) === $status)->values();
        }

        if ($previousOffice !== '') {
            $mapped = $mapped->filter(fn ($row) => (string) ($row->previous_office_id ?? '') === $previousOffice)->values();
        }

        $page = max(1, (int) $request->query('page', 1));
        $total = $mapped->count();
        $items = $mapped->slice(($page - 1) * $perPage, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    protected function expectedVisitorsQuery(int $officeId)
    {
        return DB::table('office_expectation as oe')
            ->join('visit as v', 'v.visit_id', '=', 'oe.visit_id')
            ->join('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
            ->leftJoin('expectation_status as xs', 'xs.expectation_status_id', '=', 'oe.expectation_status_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'oe.office_id')
            ->where('oe.office_id', $officeId)
            ->whereNull('v.exit_time')
            ->whereNull('oe.arrived_at')
            ->where(function ($q) {
                $q->whereNull('xs.status_name')
                    ->orWhereRaw("LOWER(TRIM(COALESCE(xs.status_name, ''))) NOT IN (?, ?, ?, ?)", [
                        'arrived', 'completed', 'complete', 'skipped',
                    ]);
            })
            ->select([
                'oe.expectation_id',
                'oe.expected_order',
                'oe.office_id',
                'oe.arrived_at',
                'oe.created_at as expectation_created_at',
                'v.visit_id',
                'v.control_number',
                'v.pass_number',
                'v.purpose_reason',
                'v.entry_time',
                'v.destination_text',
                'vr.visitor_id',
                'vr.first_name',
                'vr.last_name',
                'o.office_name',
                'xs.status_name',
                'vt.visit_type_name',
            ])
            ->orderBy('oe.expected_order')
            ->orderBy('v.entry_time')
            ->orderBy('v.visit_id');
    }

    protected function enrichExpectedRow(object $row, int $officeId): object
    {
        $row->visitor_name = trim(trim((string) $row->first_name).' '.trim((string) $row->last_name));
        $route = $this->scanService->loadRoute((int) $row->visit_id);
        $visit = (object) [
            'visit_id' => (int) $row->visit_id,
            'visit_type_name' => (string) ($row->visit_type_name ?? ''),
        ];
        $sequential = $this->scanService->isSequentialRoute($visit);
        $current = $this->scanService->resolveCurrentExpectation($route);

        $previous = collect($route)
            ->filter(fn ($step) => (int) $step->expected_order < (int) $row->expected_order)
            ->sortByDesc('expected_order')
            ->first(fn ($step) => $this->scanService->isExpectationDone($step));

        if (! $previous) {
            $previous = collect($route)
                ->filter(fn ($step) => (int) $step->expected_order < (int) $row->expected_order)
                ->sortByDesc('expected_order')
                ->first();
        }

        $row->previous_office = $previous->office_name ?? '—';
        $row->previous_office_id = $previous->office_id ?? null;

        if ($this->scanService->isExpectationDone($row)) {
            $row->route_status = 'Checked In';
            $row->route_status_key = 'checked_in';
            $row->badge = 'success';
        } elseif (! $sequential && (int) $row->office_id === $officeId) {
            $row->route_status = 'Ready for Office Check-in';
            $row->route_status_key = 'ready';
            $row->badge = 'info';
        } elseif ($sequential && $current && (int) $current->office_id === $officeId && (int) $current->expectation_id === (int) $row->expectation_id) {
            $row->route_status = 'Ready for Office Check-in';
            $row->route_status_key = 'ready';
            $row->badge = 'info';
        } elseif ($sequential && $current && (int) $current->expected_order < (int) $row->expected_order) {
            $row->route_status = 'Waiting for Previous Office';
            $row->route_status_key = 'waiting';
            $row->badge = 'warning';
        } else {
            $row->route_status = 'Expected';
            $row->route_status_key = 'expected';
            $row->badge = 'info';
        }

        $row->expected_arrival = $row->entry_time;

        return $row;
    }

    public function visitHistoryPaginated(Request $request, int $officeId, int $perPage = 10)
    {
        $query = DB::table('office_scan as os')
            ->join('visit as v', 'v.visit_id', '=', 'os.visit_id')
            ->join('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'os.office_id')
            ->leftJoin('validation_status as vs', 'vs.validation_status_id', '=', 'os.validation_status_id')
            ->leftJoin('users as u', 'u.user_id', '=', 'os.scanned_by_user_id')
            ->where('os.office_id', $officeId)
            ->select([
                'os.scan_id',
                'os.scan_time',
                'os.remarks',
                'os.visit_id',
                'os.scanned_by_user_id',
                'v.control_number',
                'v.pass_number',
                'v.purpose_reason',
                'vr.first_name',
                'vr.last_name',
                'o.office_name',
                'vs.status_name as validation_status',
                'u.first_name as staff_first_name',
                'u.last_name as staff_last_name',
            ]);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%'.Str::lower($search).'%';
            $query->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(TRIM(COALESCE(vr.first_name, \'\'))) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(vr.last_name, \'\'))) LIKE ?', [$like])
                    ->orWhereRaw("LOWER(TRIM(COALESCE(vr.first_name, '') || ' ' || COALESCE(vr.last_name, ''))) LIKE ?", [$like])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(v.control_number, \'\'))) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(v.pass_number, \'\'))) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(v.purpose_reason, \'\'))) LIKE ?', [$like]);
            });
        }

        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));
        if ($from !== '') {
            try {
                $query->where('os.scan_time', '>=', Carbon::parse($from, 'Asia/Manila')->startOfDay());
            } catch (\Throwable $e) {
            }
        }
        if ($to !== '') {
            try {
                $query->where('os.scan_time', '<=', Carbon::parse($to, 'Asia/Manila')->endOfDay());
            } catch (\Throwable $e) {
            }
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $query->whereRaw('LOWER(TRIM(COALESCE(vs.status_name, \'\'))) = ?', [Str::lower($status)]);
        }

        $staffId = trim((string) $request->query('staff', ''));
        if ($staffId !== '' && ctype_digit($staffId)) {
            $query->where('os.scanned_by_user_id', (int) $staffId);
        }

        return $query->orderByDesc('os.scan_time')->orderByDesc('os.scan_id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($row) {
                $row->visitor_name = trim(trim((string) $row->first_name).' '.trim((string) $row->last_name));
                $row->staff_name = trim(trim((string) ($row->staff_first_name ?? '')).' '.trim((string) ($row->staff_last_name ?? '')));

                return $row;
            });
    }

    public function officeStaffOptions(int $officeId)
    {
        return DB::table('office_staff as s')
            ->join('users as u', 'u.user_id', '=', 's.user_id')
            ->where('s.office_id', $officeId)
            ->select('u.user_id', 'u.first_name', 'u.last_name')
            ->orderBy('u.first_name')
            ->get()
            ->map(function ($row) {
                $row->full_name = trim(trim((string) $row->first_name).' '.trim((string) $row->last_name));

                return $row;
            });
    }

    public function liveMonitoring(int $officeId): array
    {
        $waiting = [];

        $activeVisitIds = DB::table('visit')->whereNull('exit_time')->pluck('visit_id');
        if ($activeVisitIds->isNotEmpty()) {
            $visits = DB::table('visit as v')
                ->join('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
                ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
                ->whereIn('v.visit_id', $activeVisitIds)
                ->select('v.visit_id', 'v.control_number', 'v.purpose_reason', 'v.entry_time', 'vr.first_name', 'vr.last_name', 'vt.visit_type_name')
                ->get()
                ->keyBy('visit_id');

            $routes = DB::table('office_expectation as oe')
                ->leftJoin('expectation_status as xs', 'xs.expectation_status_id', '=', 'oe.expectation_status_id')
                ->leftJoin('office as o', 'o.office_id', '=', 'oe.office_id')
                ->whereIn('oe.visit_id', $activeVisitIds)
                ->select([
                    'oe.*',
                    'xs.status_name',
                    'o.office_name',
                ])
                ->orderBy('oe.visit_id')
                ->orderBy('oe.expected_order')
                ->get()
                ->groupBy('visit_id');

            foreach ($routes as $visitId => $steps) {
                $visit = $visits->get($visitId);
                if (! $visit) {
                    continue;
                }

                $sequential = $this->scanService->isSequentialRoute($visit);
                $current = null;
                $lastDone = null;
                foreach ($steps as $step) {
                    if ($this->scanService->isExpectationDone($step)) {
                        $lastDone = $step;
                    } elseif ($current === null) {
                        $current = $step;
                    }
                }

                if ($sequential) {
                    if (! $current || (int) $current->office_id !== $officeId) {
                        continue;
                    }
                } else {
                    $pendingAtOffice = $steps->first(fn ($step) => (int) $step->office_id === $officeId
                        && ! $this->scanService->isExpectationDone($step));

                    if (! $pendingAtOffice) {
                        continue;
                    }

                    $current = $pendingAtOffice;
                }

                $name = trim(trim((string) $visit->first_name).' '.trim((string) $visit->last_name));
                $waiting[] = [
                    'visit_id' => (int) $visitId,
                    'visitor_name' => $name,
                    'control_number' => (string) ($visit->control_number ?? ''),
                    'purpose' => (string) ($visit->purpose_reason ?? ''),
                    'previous_office' => $lastDone->office_name ?? 'Main Lobby',
                    'previous_arrived_at' => $lastDone->arrived_at ?? $visit->entry_time,
                    'route_progress' => $this->routeProgressLabel($steps),
                    'status' => 'Ready for Office Check-in',
                    'current_office' => $current->office_name,
                ];
            }
        }

        $latestScan = DB::table('office_scan as os')
            ->leftJoin('validation_status as vs', 'vs.validation_status_id', '=', 'os.validation_status_id')
            ->where('os.office_id', $officeId)
            ->orderByDesc('os.scan_time')
            ->select('os.scan_time', 'vs.status_name', 'os.remarks')
            ->first();

        return [
            'waiting' => $waiting,
            'latest_scan' => $latestScan,
        ];
    }

    public function visitDetails(int $visitId, int $officeId): ?array
    {
        $visit = DB::table('visit as v')
            ->join('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('exit_status as es', 'es.exit_status_id', '=', 'v.exit_status_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'v.primary_office_id')
            ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
            ->where('v.visit_id', $visitId)
            ->select(
                'v.*',
                'vr.first_name',
                'vr.last_name',
                'vr.visitor_photo_with_id_url',
                'vr.contact_no',
                'es.exit_status_name',
                'o.office_name as primary_office_name',
                'vt.visit_type_name'
            )
            ->first();

        if (! $visit) {
            return null;
        }

        $onRoute = DB::table('office_expectation')
            ->where('visit_id', $visitId)
            ->where('office_id', $officeId)
            ->exists();

        $scannedHere = DB::table('office_scan')
            ->where('visit_id', $visitId)
            ->where('office_id', $officeId)
            ->exists();

        if (! $onRoute && ! $scannedHere) {
            return null;
        }

        $route = $this->scanService->loadRoute($visitId);
        $scans = DB::table('office_scan as os')
            ->leftJoin('users as u', 'u.user_id', '=', 'os.scanned_by_user_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'os.office_id')
            ->leftJoin('validation_status as vs', 'vs.validation_status_id', '=', 'os.validation_status_id')
            ->where('os.visit_id', $visitId)
            ->select([
                'os.*',
                'o.office_name',
                'vs.status_name as validation_status',
                'u.first_name as staff_first_name',
                'u.last_name as staff_last_name',
            ])
            ->orderBy('os.scan_time')
            ->get()
            ->map(function ($row) {
                $row->staff_name = trim(trim((string) ($row->staff_first_name ?? '')).' '.trim((string) ($row->staff_last_name ?? '')));

                return $row;
            });

        $timeline = [];
        foreach ($route as $step) {
            $matchingScan = $scans->first(function ($scan) use ($step) {
                return (int) $scan->office_id === (int) $step->office_id
                    && Str::lower((string) ($scan->validation_status ?? '')) === 'valid'
                    && (
                        empty($step->arrived_at)
                        || Carbon::parse($scan->scan_time)->diffInMinutes(Carbon::parse($step->arrived_at)) <= 5
                    );
            });

            $state = $this->scanService->isExpectationDone($step) ? 'done' : 'pending';
            $timeline[] = [
                'office_name' => $step->office_name,
                'order' => (int) $step->expected_order,
                'arrived_at' => $step->arrived_at,
                'status_name' => $step->status_name,
                'state' => $state,
                'staff_name' => $matchingScan->staff_name ?? null,
                'scan_time' => $matchingScan->scan_time ?? $step->arrived_at,
                'remarks' => $matchingScan->remarks ?? null,
            ];
        }

        $current = $this->scanService->resolveCurrentExpectation($route);
        $flexibleRoute = ! $this->scanService->isSequentialRoute($visit);
        foreach ($timeline as &$item) {
            if ($flexibleRoute) {
                if ($item['state'] === 'pending') {
                    $item['state'] = 'current';
                }
            } elseif ($item['state'] === 'pending' && $current && (int) $current->expected_order === (int) $item['order']) {
                $item['state'] = 'current';
            }
        }
        unset($item);

        $fullName = trim(trim((string) $visit->first_name).' '.trim((string) $visit->last_name));

        return [
            'visit' => $visit,
            'visitor_name' => $fullName !== '' ? $fullName : 'Visitor',
            'photo_url' => $this->scanService->resolveVisitorPhotoUrl($visit->visitor_photo_with_id_url ?? null),
            'timeline' => $timeline,
            'scans' => $scans,
            'current' => $current,
            'qr_status' => empty($visit->exit_time) ? 'Active' : 'Expired / Checked Out',
        ];
    }

    public function unreadNotifications(int $userId, int $limit = 20)
    {
        return DB::table('notification as n')
            ->leftJoin('notif_type as nt', 'nt.notif_type_id', '=', 'n.notif_type_id')
            ->where('n.recipient_user_id', $userId)
            ->whereNull('n.read_at')
            ->orderByDesc('n.sent_at')
            ->limit($limit)
            ->select('n.*', 'nt.notif_type_name')
            ->get();
    }

    protected function previousOfficeName(int $visitId, int $scanId): string
    {
        $scan = DB::table('office_scan')->where('scan_id', $scanId)->first();
        if (! $scan) {
            return '—';
        }

        $previous = DB::table('office_scan as os')
            ->join('office as o', 'o.office_id', '=', 'os.office_id')
            ->join('validation_status as vs', 'vs.validation_status_id', '=', 'os.validation_status_id')
            ->where('os.visit_id', $visitId)
            ->where('os.scan_id', '<', $scanId)
            ->whereRaw("LOWER(TRIM(COALESCE(vs.status_name, ''))) = ?", ['valid'])
            ->orderByDesc('os.scan_id')
            ->value('o.office_name');

        return $previous ?: 'Main Lobby';
    }

    protected function routeProgressLabel($steps): string
    {
        $total = count($steps);
        $done = 0;
        foreach ($steps as $step) {
            if ($this->scanService->isExpectationDone($step)) {
                $done++;
            }
        }

        return $done.'/'.$total;
    }
}
