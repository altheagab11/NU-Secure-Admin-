<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OfficeProfileController extends Controller
{
    public function __construct(protected OfficeVisitorQueryService $queries)
    {
    }

    public function notifications(Request $request): View
    {
        $office = $request->attributes->get('office_context');
        $notifications = DB::table('notification as n')
            ->leftJoin('notif_type as nt', 'nt.notif_type_id', '=', 'n.notif_type_id')
            ->where('n.recipient_user_id', (int) $office->user_id)
            ->orderByDesc('n.sent_at')
            ->select('n.*', 'nt.notif_type_name')
            ->paginate($this->resolvePerPage($request))
            ->withQueryString();

        $staffName = trim(trim((string) ($office->first_name ?? '')).' '.trim((string) ($office->last_name ?? '')));

        return view('office.notifications', [
            'pageTitle' => 'Notifications',
            'office' => $office,
            'staffName' => $staffName !== '' ? $staffName : 'Office Staff',
            'staffRole' => trim((string) ($office->position ?? 'Office Staff')) ?: 'Office Staff',
            'currentDate' => Carbon::now('Asia/Manila')->format('l, F j, Y'),
            'notificationList' => $notifications,
            'notifications' => $this->queries->unreadNotifications((int) $office->user_id, 10),
        ]);
    }

    public function markNotificationRead(Request $request, int $notifId)
    {
        $office = $request->attributes->get('office_context');

        DB::table('notification')
            ->where('notif_id', $notifId)
            ->where('recipient_user_id', (int) $office->user_id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now('Asia/Manila')]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
