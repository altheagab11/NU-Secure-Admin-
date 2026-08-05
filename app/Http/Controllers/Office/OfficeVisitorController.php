<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OfficeVisitorController extends Controller
{
    public function __construct(protected OfficeVisitorQueryService $queries)
    {
    }

    public function expected(Request $request): View
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;
        $perPage = $this->resolvePerPage($request);

        $visitors = $this->queries->expectedVisitorsPaginated($request, $officeId, $perPage);
        $offices = DB::table('office')->where('is_active', true)->orderBy('office_name')->get(['office_id', 'office_name']);

        $staffName = trim(trim((string) ($office->first_name ?? '')).' '.trim((string) ($office->last_name ?? '')));

        return view('office.expected-visitors', [
            'pageTitle' => 'Expected Visitors',
            'office' => $office,
            'staffName' => $staffName !== '' ? $staffName : 'Office Staff',
            'staffRole' => trim((string) ($office->position ?? 'Office Staff')) ?: 'Office Staff',
            'currentDate' => Carbon::now('Asia/Manila')->format('l, F j, Y'),
            'visitors' => $visitors,
            'offices' => $offices,
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'date' => (string) $request->query('date', ''),
                'status' => (string) $request->query('status', ''),
                'previous_office' => (string) $request->query('previous_office', ''),
            ],
            'notifications' => $this->queries->unreadNotifications((int) $office->user_id, 10),
        ]);
    }

    public function show(Request $request, int $visit): View
    {
        $office = $request->attributes->get('office_context');
        $details = $this->queries->visitDetails($visit, (int) $office->office_id);

        if (! $details) {
            abort(404, 'Visitor record not found for your office.');
        }

        $staffName = trim(trim((string) ($office->first_name ?? '')).' '.trim((string) ($office->last_name ?? '')));

        return view('office.visitor-details', [
            'pageTitle' => 'Visitor Details',
            'office' => $office,
            'staffName' => $staffName !== '' ? $staffName : 'Office Staff',
            'staffRole' => trim((string) ($office->position ?? 'Office Staff')) ?: 'Office Staff',
            'currentDate' => Carbon::now('Asia/Manila')->format('l, F j, Y'),
            'details' => $details,
            'notifications' => $this->queries->unreadNotifications((int) $office->user_id, 10),
        ]);
    }

    public function detailsJson(Request $request, int $visit)
    {
        $office = $request->attributes->get('office_context');
        $details = $this->queries->visitDetails($visit, (int) $office->office_id);

        if (! $details) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor record not found for your office.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $details,
        ]);
    }
}
