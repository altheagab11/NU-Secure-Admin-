<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OfficeDashboardController extends Controller
{
    private const ALLOWED_PER_PAGE = [5, 10, 25, 50, 75, 100];

    public function __construct(protected OfficeVisitorQueryService $queries) {}

    public function index(Request $request): View
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;

        $stats = $this->queries->dashboardStats($officeId);
        $previewRequest = Request::create($request->url());
        $recentActivity = $this->paginateCollection(
            $this->queries->recentActivity($officeId, 5, true),
            $previewRequest,
            'scans_page',
            'scans_per_page'
        );
        $expectedPreview = $this->paginateCollection(
            $this->queries->expectedVisitorsPreview($officeId, null)->sortByDesc('entry_time')->values(),
            $previewRequest,
            'expected_page',
            'expected_per_page'
        );
        $live = $this->queries->liveMonitoring($officeId);
        $liveWaiting = $this->paginateCollection(
            collect($live['waiting'] ?? [])->sortByDesc('previous_arrived_at')->values(),
            $previewRequest,
            'ready_page',
            'ready_per_page',
            5
        );
        $notifications = $this->queries->unreadNotifications((int) $office->user_id, 10);

        $staffName = trim(trim((string) ($office->first_name ?? '')).' '.trim((string) ($office->last_name ?? '')));
        if ($staffName === '') {
            $staffName = 'Office Staff';
        }

        return view('office.dashboard', [
            'pageTitle' => 'Dashboard',
            'office' => $office,
            'staffName' => $staffName,
            'staffRole' => trim((string) ($office->position ?? 'Office Staff')) ?: 'Office Staff',
            'currentDate' => Carbon::now('Asia/Manila')->format('l, F j, Y'),
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'expectedPreview' => $expectedPreview,
            'live' => $live,
            'liveWaiting' => $liveWaiting,
            'notifications' => $notifications,
            'officeStatus' => ! empty($office->office_is_active) ? 'Open' : 'Inactive',
        ]);
    }

    public function liveData(Request $request)
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;

        if ($request->has('dashboard_list')) {
            return $this->dashboardList($request, $officeId);
        }

        $recentActivity = $this->paginateCollection(
            $this->queries->recentActivity($officeId, 500, true),
            $request,
            'scans_page',
            'scans_per_page'
        );
        $expectedPreview = $this->paginateCollection(
            $this->queries->expectedVisitorsPreview($officeId, null)->sortByDesc('entry_time')->values(),
            $request,
            'expected_page',
            'expected_per_page'
        );
        $live = $this->queries->liveMonitoring($officeId);
        $liveWaiting = $this->paginateCollection(
            collect($live['waiting'] ?? [])->sortByDesc('previous_arrived_at')->values(),
            $request,
            'ready_page',
            'ready_per_page',
            5
        );

        return response()->json([
            'success' => true,
            'stats' => $this->queries->dashboardStats($officeId),
            'live' => [
                'waiting' => $liveWaiting->getCollection()->values()->all(),
                'latest_scan' => $live['latest_scan'] ?? null,
                'meta' => $this->paginatorMeta($liveWaiting),
            ],
            'recent_activity' => [
                'data' => $this->formatRecentActivityForLive($recentActivity->getCollection()),
                'meta' => $this->paginatorMeta($recentActivity),
            ],
            'expected_visitors' => [
                'data' => $this->formatExpectedVisitorsForLive($expectedPreview->getCollection()),
                'meta' => $this->paginatorMeta($expectedPreview),
            ],
            'server_time' => Carbon::now('Asia/Manila')->toDateTimeString(),
        ]);
    }

    private function dashboardList(Request $request, int $officeId)
    {
        $filters = $request->validate([
            'dashboard_list' => ['required', 'in:ready,scans,expected'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:80'],
            'previous_office' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:5,10,25,50,75,100'],
        ]);
        $kind = $filters['dashboard_list'];
        $perPage = (int) ($filters['per_page'] ?? 10);

        if ($kind === 'scans') {
            $today = Carbon::now('Asia/Manila')->toDateString();
            $historyRequest = Request::create($request->url(), 'GET', array_merge($filters, ['from' => $today, 'to' => $today]));
            $paginator = $this->queries->visitHistoryPaginated($historyRequest, $officeId, $perPage);
            $data = $this->formatRecentActivityForLive($paginator->getCollection(), false);
            $options = ['statuses' => ['Valid', 'Invalid', 'Unauthorized'], 'previous_offices' => []];
        } else {
            $rows = $kind === 'ready'
                ? collect($this->queries->liveMonitoring($officeId, false)['waiting'] ?? [])->sortByDesc('previous_arrived_at')->values()
                : collect($this->formatExpectedVisitorsForLive($this->queries->expectedVisitorsPreview($officeId, null)->sortByDesc('entry_time')->values(), false));
            $statusField = $kind === 'ready' ? 'status' : 'route_status_key';
            $options = [
                'statuses' => $rows->pluck($statusField)->filter()->unique()->values()->all(),
                'previous_offices' => $rows->pluck('previous_office')->filter()->unique()->sort()->values()->all(),
            ];
            $needle = Str::lower(trim($filters['search'] ?? ''));
            $rows = $rows->filter(function ($row) use ($filters, $needle, $statusField) {
                $searchable = implode(' ', [data_get($row, 'visitor_name'), data_get($row, 'control_number'), data_get($row, 'purpose'), data_get($row, 'previous_office')]);

                return ($needle === '' || str_contains(Str::lower($searchable), $needle))
                    && (empty($filters['status']) || Str::lower((string) data_get($row, $statusField)) === Str::lower($filters['status']))
                    && (empty($filters['previous_office']) || data_get($row, 'previous_office') === $filters['previous_office']);
            })->values();
            $paginationRequest = Request::create($request->url(), 'GET', ['page' => $filters['page'] ?? 1, 'per_page' => $perPage]);
            $paginator = $this->paginateCollection($rows, $paginationRequest, 'page', 'per_page', 10);
            $data = $paginator->getCollection()->values()->all();
            if ($kind === 'ready') {
                $data = $this->queries->resolveWaitingPhotos($data);
            }
        }

        return response()->json(['success' => true, 'data' => $data, 'meta' => $this->paginatorMeta($paginator), 'filters' => $options]);
    }

    protected function paginateCollection(
        Collection $items,
        Request $request,
        string $pageName,
        string $perPageParam,
        int $defaultPerPage = 5
    ): LengthAwarePaginator {
        $perPage = (int) $request->query($perPageParam, $defaultPerPage);
        if (! in_array($perPage, self::ALLOWED_PER_PAGE, true)) {
            $perPage = $defaultPerPage;
        }

        $total = $items->count();
        $lastPage = max(1, (int) ceil(($total > 0 ? $total : 1) / $perPage));
        $page = max(1, (int) $request->query($pageName, 1));
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => $pageName,
            ]
        );
    }

    protected function paginatorMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'from' => $paginator->firstItem() ?? 0,
            'to' => $paginator->lastItem() ?? 0,
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => max(1, $paginator->lastPage()),
            'per_page' => $paginator->perPage(),
            'first_url' => $paginator->url(1),
            'prev_url' => $paginator->previousPageUrl(),
            'next_url' => $paginator->nextPageUrl(),
            'last_url' => $paginator->url(max(1, $paginator->lastPage())),
        ];
    }

    protected function formatRecentActivityForLive($rows, bool $compact = true): array
    {
        return collect($rows)->map(function ($row) use ($compact) {
            $status = trim((string) ($row->validation_status ?? ''));

            return [
                'visit_id' => (int) ($row->visit_id ?? 0),
                'visitor_name' => (string) ($row->visitor_name ?? 'Visitor'),
                'control_number' => (string) (($row->control_number ?? '') !== '' ? $row->control_number : '—'),
                'purpose' => $compact ? Str::limit((string) ($row->purpose_reason ?? '—'), 28) : (string) ($row->purpose_reason ?? '—'),
                'time_label' => (string) ($row->scan_time_label ?? (! empty($row->scan_time) ? Carbon::parse($row->scan_time)->timezone('Asia/Manila')->format('g:i A') : '—')),
                'validation_status' => $status !== '' ? $status : '—',
                'view_url' => route('office.visitors.show', (int) ($row->visit_id ?? 0)),
            ];
        })->values()->all();
    }

    protected function formatExpectedVisitorsForLive($rows, bool $compact = true): array
    {
        return collect($rows)->map(function ($row) use ($compact) {
            $arrival = ! empty($row->expected_arrival)
                ? Carbon::parse($row->expected_arrival)->timezone('Asia/Manila')->format('M j, g:i A')
                : '—';
            $statusKey = (string) ($row->route_status_key ?? '');

            return [
                'visit_id' => (int) ($row->visit_id ?? 0),
                'control_number' => (string) (($row->control_number ?? '') !== '' ? $row->control_number : '—'),
                'visitor_name' => (string) ($row->visitor_name ?? 'Visitor'),
                'purpose' => $compact ? Str::limit((string) ($row->purpose_reason ?? '—'), 32) : (string) ($row->purpose_reason ?? '—'),
                'previous_office' => (string) ($row->previous_office ?? '—'),
                'expected_label' => $arrival,
                'route_status' => (string) ($row->route_status ?? 'Expected'),
                'route_status_key' => $statusKey,
                'badge' => (string) ($row->badge ?? 'info'),
                'view_url' => route('office.visitors.show', (int) ($row->visit_id ?? 0)),
                'scan_url' => $statusKey === 'ready'
                    ? route('office.scanner', ['visit' => (int) ($row->visit_id ?? 0)])
                    : null,
            ];
        })->values()->all();
    }
}
