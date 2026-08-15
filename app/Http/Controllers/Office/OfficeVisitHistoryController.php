<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeVisitHistoryController extends Controller
{
    public function __construct(protected OfficeVisitorQueryService $queries)
    {
    }

    public function index(Request $request): View
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;
        $perPage = $this->resolvePerPage($request);

        $history = $this->queries->visitHistoryPaginated($request, $officeId, $perPage);
        $staffOptions = $this->queries->officeStaffOptions($officeId);

        $staffName = trim(trim((string) ($office->first_name ?? '')).' '.trim((string) ($office->last_name ?? '')));

        return view('office.visit-history', [
            'pageTitle' => 'Visit History',
            'office' => $office,
            'staffName' => $staffName !== '' ? $staffName : 'Office Staff',
            'staffRole' => trim((string) ($office->position ?? 'Office Staff')) ?: 'Office Staff',
            'currentDate' => Carbon::now('Asia/Manila')->format('l, F j, Y'),
            'history' => $history,
            'staffOptions' => $staffOptions,
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'from' => (string) $request->query('from', ''),
                'to' => (string) $request->query('to', ''),
                'status' => (string) $request->query('status', ''),
                'staff' => (string) $request->query('staff', ''),
            ],
            'notifications' => $this->queries->unreadNotifications((int) $office->user_id, 10),
        ]);
    }
}
