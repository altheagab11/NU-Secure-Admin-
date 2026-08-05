<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeDashboardController extends Controller
{
    public function __construct(protected OfficeVisitorQueryService $queries)
    {
    }

    public function index(Request $request): View
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;

        $stats = $this->queries->dashboardStats($officeId);
        $recentActivity = $this->queries->recentActivity($officeId, 8);
        $expectedPreview = $this->queries->expectedVisitorsPreview($officeId, 6);
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

        return response()->json([
            'success' => true,
            'stats' => $this->queries->dashboardStats($officeId),
            'live' => $this->queries->liveMonitoring($officeId),
            'recent_activity' => $this->queries->recentActivity($officeId, 8),
            'server_time' => Carbon::now('Asia/Manila')->toDateTimeString(),
        ]);
    }
}
