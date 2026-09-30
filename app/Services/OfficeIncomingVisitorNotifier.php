<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OfficeIncomingVisitorNotifier
{
    public function __construct(
        protected OfficeScanService $scanService
    ) {}

    /**
     * Notify offices on the visitor route after registration.
     * All pending destination offices are notified so staff can prepare.
     */
    public function notifyExpectedOffices(int $visitId, string $context = 'registered'): void
    {
        if ($visitId <= 0 || ! Schema::hasTable('notification')) {
            return;
        }

        try {
            $visit = $this->loadVisit($visitId);
            if (! $visit) {
                return;
            }

            $route = $this->scanService->loadRoute($visitId);
            if ($route->isEmpty()) {
                return;
            }

            foreach ($this->pendingOfficeIds($route) as $officeId) {
                $this->notifyOfficeStaff((int) $officeId, $visit, $context);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send incoming visitor office notifications.', [
                'visit_id' => $visitId,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * After an office check-in, notify the next office in the route.
     */
    public function notifyNextOfficeAfterCheckIn(int $visitId, int $completedOfficeId): void
    {
        if ($visitId <= 0) {
            return;
        }

        try {
            $visit = $this->loadVisit($visitId);
            if (! $visit) {
                return;
            }

            $route = $this->scanService->loadRoute($visitId);
            if ($route->isEmpty()) {
                return;
            }

            $next = $this->scanService->resolveCurrentExpectation($route);
            if (! $next) {
                return;
            }

            $nextOfficeId = (int) ($next->office_id ?? 0);
            if ($nextOfficeId <= 0 || $nextOfficeId === $completedOfficeId) {
                return;
            }

            $this->notifyOfficeStaff($nextOfficeId, $visit, 'proceeding');

            foreach ($route as $step) {
                if ($this->scanService->isExpectationDone($step)) {
                    continue;
                }
                if (! $this->scanService->isOptionalRouteOffice($step)) {
                    continue;
                }
                if ($this->scanService->findBlockingPreviousExpectation($route, (int) $step->expected_order)) {
                    continue;
                }

                $optionalOfficeId = (int) ($step->office_id ?? 0);
                if ($optionalOfficeId > 0 && $optionalOfficeId !== $nextOfficeId) {
                    $this->notifyOfficeStaff($optionalOfficeId, $visit, 'optional');
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to notify next office after check-in.', [
                'visit_id' => $visitId,
                'completed_office_id' => $completedOfficeId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create missing notifications for active visitors already expected at this office.
     * Called when staff open the Notifications page so existing visits appear immediately.
     */
    public function syncActiveVisitorsForOffice(int $officeId): int
    {
        if ($officeId <= 0 || ! Schema::hasTable('notification') || ! Schema::hasTable('office_expectation')) {
            return 0;
        }

        $created = 0;

        try {
            $staffUserIds = DB::table('office_staff')
                ->where('office_id', $officeId)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            if ($staffUserIds->isEmpty()) {
                return 0;
            }

            $typeId = $this->resolveIncomingNotifTypeId();
            $officeName = (string) (DB::table('office')->where('office_id', $officeId)->value('office_name') ?? '');

            $rows = DB::table('office_expectation as oe')
                ->join('visit as v', 'v.visit_id', '=', 'oe.visit_id')
                ->join('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
                ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
                ->where('oe.office_id', $officeId)
                ->whereNull('v.exit_time')
                ->whereNull('oe.arrived_at')
                ->orderByDesc('v.entry_time')
                ->limit(12)
                ->get([
                    'oe.visit_id',
                    'oe.expected_order',
                    'oe.office_id',
                    'v.control_number',
                    'v.pass_number',
                    'vr.first_name',
                    'vr.last_name',
                    'vt.visit_type_name',
                ]);

            foreach ($rows as $row) {
                $controlNumber = trim((string) ($row->control_number ?? ''));
                if ($controlNumber !== '' && $this->hasUnreadIncomingNoticeForUsers($staffUserIds->all(), $controlNumber, $typeId)) {
                    continue;
                }

                $visit = (object) [
                    'visit_id' => (int) $row->visit_id,
                    'control_number' => $controlNumber,
                    'pass_number' => trim((string) ($row->pass_number ?? '')),
                    'first_name' => (string) ($row->first_name ?? ''),
                    'last_name' => (string) ($row->last_name ?? ''),
                    'visit_type_name' => (string) ($row->visit_type_name ?? 'Visitor'),
                ];

                $route = $this->scanService->loadRoute((int) $row->visit_id);
                $blocking = $this->scanService->findBlockingPreviousExpectation(
                    $route,
                    (int) $row->expected_order
                );

                $context = 'registered';
                if (! $blocking) {
                    $context = $this->scanService->isOptionalRouteOffice((object) [
                        'office_name' => $officeName,
                    ]) ? 'optional' : 'proceeding';
                }

                $before = $this->countIncomingForControl($controlNumber, $officeId);
                $this->notifyOfficeStaff($officeId, $visit, $context);
                $after = $this->countIncomingForControl($controlNumber, $officeId);
                $created += max(0, $after - $before);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to sync office incoming visitor notifications.', [
                'office_id' => $officeId,
                'error' => $e->getMessage(),
            ]);
        }

        return $created;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $route
     * @return list<int>
     */
    protected function pendingOfficeIds($route): array
    {
        return $route
            ->filter(fn ($step) => ! $this->scanService->isExpectationDone($step))
            ->pluck('office_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function notifyOfficeStaff(int $officeId, object $visit, string $context): void
    {
        if ($officeId <= 0 || ! Schema::hasTable('office_staff')) {
            return;
        }

        $staffUserIds = DB::table('office_staff')
            ->where('office_id', $officeId)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($staffUserIds->isEmpty()) {
            return;
        }

        $typeId = $this->resolveIncomingNotifTypeId();
        $message = $this->buildMessage($visit, $context);
        $now = Carbon::now('Asia/Manila');
        $controlNumber = trim((string) ($visit->control_number ?? ''));

        foreach ($staffUserIds as $userId) {
            if ($this->hasUnreadIncomingNotice((int) $userId, $controlNumber, $typeId)) {
                continue;
            }

            DB::table('notification')->insert([
                'scan_id' => null,
                'recipient_user_id' => (int) $userId,
                'notif_type_id' => $typeId,
                'message' => $message,
                'sent_at' => $now,
                'read_at' => null,
            ]);
        }
    }

    protected function buildMessage(object $visit, string $context): string
    {
        $name = trim(trim((string) ($visit->first_name ?? '')).' '.trim((string) ($visit->last_name ?? '')));
        if ($name === '') {
            $name = 'A visitor';
        }

        $controlNumber = trim((string) ($visit->control_number ?? ''));
        $passNumber = trim((string) ($visit->pass_number ?? ''));
        $visitType = trim((string) ($visit->visit_type_name ?? 'Visitor'));

        $prefix = match ($context) {
            'proceeding' => 'Incoming visitor now heading to your office',
            'optional' => 'Incoming visitor may stop by your office (optional)',
            default => 'Incoming visitor expected at your office',
        };

        $parts = [$prefix.': '.$name];
        if ($visitType !== '') {
            $parts[] = $visitType;
        }
        if ($controlNumber !== '') {
            $parts[] = 'Control No. '.$controlNumber;
        }
        if ($passNumber !== '') {
            $parts[] = 'Pass '.$passNumber;
        }
        $parts[] = 'Please prepare to scan their QR pass.';

        return implode(' · ', $parts);
    }

    protected function hasUnreadIncomingNotice(int $userId, string $controlNumber, ?int $typeId): bool
    {
        if ($userId <= 0 || $controlNumber === '') {
            return false;
        }

        return $this->hasUnreadIncomingNoticeForUsers([$userId], $controlNumber, $typeId);
    }

    /**
     * @param  list<int>  $userIds
     */
    protected function hasUnreadIncomingNoticeForUsers(array $userIds, string $controlNumber, ?int $typeId): bool
    {
        if ($userIds === [] || $controlNumber === '') {
            return false;
        }

        $query = DB::table('notification')
            ->whereIn('recipient_user_id', $userIds)
            ->whereNull('read_at')
            ->where('message', 'like', '%Control No. '.$controlNumber.'%')
            ->where('sent_at', '>=', Carbon::now('Asia/Manila')->subHours(12));

        if ($typeId) {
            $query->where('notif_type_id', $typeId);
        }

        return $query->exists();
    }

    protected function countIncomingForControl(string $controlNumber, int $officeId): int
    {
        if ($controlNumber === '' || $officeId <= 0) {
            return 0;
        }

        $typeId = $this->resolveIncomingNotifTypeId();
        $staffIds = DB::table('office_staff')
            ->where('office_id', $officeId)
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->all();

        if ($staffIds === []) {
            return 0;
        }

        $query = DB::table('notification')
            ->whereIn('recipient_user_id', $staffIds)
            ->where('message', 'like', '%Control No. '.$controlNumber.'%')
            ->where('sent_at', '>=', Carbon::now('Asia/Manila')->subHours(12));

        if ($typeId) {
            $query->where('notif_type_id', $typeId);
        }

        return (int) $query->count();
    }

    protected function loadVisit(int $visitId): ?object
    {
        return DB::table('visit as v')
            ->join('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
            ->where('v.visit_id', $visitId)
            ->select([
                'v.visit_id',
                'v.control_number',
                'v.pass_number',
                'vr.first_name',
                'vr.last_name',
                'vt.visit_type_name',
            ])
            ->first();
    }

    protected function resolveIncomingNotifTypeId(): ?int
    {
        if (! Schema::hasTable('notif_type')) {
            return null;
        }

        foreach (['Incoming Visitor', 'Expected Visitor'] as $name) {
            $id = DB::table('notif_type')
                ->whereRaw('LOWER(TRIM(COALESCE(notif_type_name, \'\'))) = ?', [Str::lower($name)])
                ->value('notif_type_id');
            if ($id) {
                return (int) $id;
            }
        }

        return null;
    }
}
