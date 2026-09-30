<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OfficeProfileController extends Controller
{
    public function __construct(protected OfficeVisitorQueryService $queries)
    {
    }

    public function notifications(Request $request): View
    {
        $office = $request->attributes->get('office_context');
        $officeId = (int) ($office->office_id ?? 0);
        $userId = (int) ($office->user_id ?? 0);

        if ($officeId > 0) {
            app(\App\Services\OfficeIncomingVisitorNotifier::class)
                ->syncActiveVisitorsForOffice($officeId);
        }

        $notifications = DB::table('notification as n')
            ->leftJoin('notif_type as nt', 'nt.notif_type_id', '=', 'n.notif_type_id')
            ->where('n.recipient_user_id', $userId)
            ->orderByDesc('n.sent_at')
            ->select('n.*', 'nt.notif_type_name')
            ->paginate($this->resolvePerPage($request))
            ->withQueryString()
            ->through(function ($row) {
                $message = (string) ($row->message ?? '');
                $typeName = trim((string) ($row->notif_type_name ?? 'Notice'));
                $isIncoming = Str::contains(Str::lower($typeName), 'incoming')
                    || Str::contains(Str::lower($message), 'incoming visitor');

                $visitorName = null;
                if (preg_match('/:\s*([^·]+?)(?:\s*·|$)/u', $message, $matches)) {
                    $visitorName = trim($matches[1]);
                }

                $controlNumber = null;
                if (preg_match('/Control No\.\s*([A-Za-z0-9\-]+)/u', $message, $matches)) {
                    $controlNumber = trim($matches[1]);
                }

                $row->is_incoming = $isIncoming;
                $row->visitor_name = $visitorName;
                $row->control_number = $controlNumber;
                $row->display_type = $isIncoming ? 'Incoming Visitor' : ($typeName !== '' ? $typeName : 'Notice');
                $row->is_unread = empty($row->read_at);

                return $row;
            });

        $staffName = trim(trim((string) ($office->first_name ?? '')).' '.trim((string) ($office->last_name ?? '')));

        return view('office.notifications', [
            'pageTitle' => 'Notifications',
            'office' => $office,
            'staffName' => $staffName !== '' ? $staffName : 'Office Staff',
            'staffRole' => trim((string) ($office->position ?? 'Office Staff')) ?: 'Office Staff',
            'currentDate' => Carbon::now('Asia/Manila')->format('l, F j, Y'),
            'notificationList' => $notifications,
            'notifications' => $this->queries->unreadNotifications($userId, 10),
            'unreadIncomingCount' => $notifications->getCollection()->where('is_incoming', true)->where('is_unread', true)->count(),
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
