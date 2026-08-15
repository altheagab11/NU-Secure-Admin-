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

    public function __construct(protected OfficeVisitorQueryService $queries)
    {
    }

    public function index(Request $request): View
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;

        $stats = $this->queries->dashboardStats($officeId);
        $recentActivity = $this->paginateCollection(
            $this->queries->recentActivity($officeId, 500, true),
            $request,
            'scans_page',
            'scans_per_page'
        );
        $expectedPreview = $this->paginateCollection(
            $this->queries->expectedVisitorsPreview($officeId, null),
            $request,
            'expected_page',
            'expected_per_page'
        );
        $live = $this->queries->liveMonitoring($officeId);
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
            'notifications' => $notifications,
            'officeStatus' => ! empty($office->office_is_active) ? 'Open' : 'Inactive',
        ]);
    }

    public function liveData(Request $request)
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;

        $recentActivity = $this->paginateCollection(
            $this->queries->recentActivity($officeId, 500, true),
            $request,
            'scans_page',
            'scans_per_page'
        );
        $expectedPreview = $this->paginateCollection(
            $this->queries->expectedVisitorsPreview($officeId, null),
            $request,
            'expected_page',
            'expected_per_page'
        );

        return response()->json([
            'success' => true,
            'stats' => $this->queries->dashboardStats($officeId),
            'live' => $this->queries->liveMonitoring($officeId),
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

    protected function formatRecentActivityForLive($rows): array
    {
        return collect($rows)->map(function ($row) {
            $status = trim((string) ($row->validation_status ?? ''));

            return [
                'visit_id' => (int) ($row->visit_id ?? 0),
                'visitor_name' => (string) ($row->visitor_name ?? 'Visitor'),
                'control_number' => (string) (($row->control_number ?? '') !== '' ? $row->control_number : '—'),
                'purpose' => Str::limit((string) (($row->purpose_reason ?? '') !== '' ? $row->purpose_reason : '—'), 28),
                'time_label' => (string) ($row->scan_time_label ?? '—'),
                'validation_status' => $status !== '' ? $status : '—',
                'view_url' => route('office.visitors.show', (int) ($row->visit_id ?? 0)),
            ];
        })->values()->all();
    }

    protected function formatExpectedVisitorsForLive($rows): array
    {
        return collect($rows)->map(function ($row) {
            $arrival = ! empty($row->expected_arrival)
                ? Carbon::parse($row->expected_arrival)->timezone('Asia/Manila')->format('M j, g:i A')
                : '—';
            $statusKey = (string) ($row->route_status_key ?? '');

            return [
                'visit_id' => (int) ($row->visit_id ?? 0),
                'control_number' => (string) (($row->control_number ?? '') !== '' ? $row->control_number : '—'),
                'visitor_name' => (string) ($row->visitor_name ?? 'Visitor'),
                'purpose' => Str::limit((string) (($row->purpose_reason ?? '') !== '' ? $row->purpose_reason : '—'), 40),
                'previous_office' => (string) ($row->previous_office ?? '—'),
                'expected_label' => $arrival,
                'route_status' => (string) ($row->route_status ?? 'Expected'),
                'badge' => (string) ($row->badge ?? 'info'),
                'view_url' => route('office.visitors.show', (int) ($row->visit_id ?? 0)),
                'scan_url' => $statusKey === 'ready'
                    ? route('office.scanner', ['visit' => (int) ($row->visit_id ?? 0)])
                    : null,
            ];
        })->values()->all();
    }
}
