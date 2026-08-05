<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function export(Request $request): StreamedResponse
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) $office->office_id;

        // Reuse filtered query with a high page size for CSV export.
        $request->query->set('per_page', 100);
        $rows = $this->queries->visitHistoryPaginated($request, $officeId, 5000);

        $filename = 'office-visit-history-'.Carbon::now('Asia/Manila')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Date/Time',
                'Control Number',
                'Visitor Name',
                'Purpose',
                'Office',
                'Staff',
                'Status',
                'Remarks',
            ]);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->scan_time,
                    $row->control_number,
                    $row->visitor_name,
                    $row->purpose_reason,
                    $row->office_name,
                    $row->staff_name,
                    $row->validation_status,
                    $row->remarks,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
