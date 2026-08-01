<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnrolleeProgressController extends Controller
{
    /**
     * Public enrollee QR progress tracker (browser view after scanning enrollee QR).
     */
    public function show(string $token)
    {
        $token = trim(urldecode($token));

        if ($token === '') {
            abort(404);
        }

        $visit = $this->findEnrolleeVisitByToken($token);

        if (! $visit) {
            return response()->view('enrollee.not-found', [
                'message' => 'No active enrollee pass was found for this QR code.',
            ], 404);
        }

        $tracker = $this->buildTrackerPayload($visit);

        return view('enrollee.progress', $tracker);
    }

    /**
     * Lightweight JSON for auto-refresh polling.
     */
    public function status(string $token)
    {
        $token = trim(urldecode($token));
        $visit = $this->findEnrolleeVisitByToken($token);

        if (! $visit) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollee pass not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->buildTrackerPayload($visit),
        ]);
    }

    protected function findEnrolleeVisitByToken(string $token): ?object
    {
        $normalized = Str::lower(trim($token));

        return DB::table('visit as v')
            ->join('visitor as vr', 'vr.visitor_id', '=', 'v.visitor_id')
            ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
            ->leftJoin('enrollee as e', 'e.visitor_id', '=', 'vr.visitor_id')
            ->whereRaw('LOWER(TRIM(COALESCE(v.qr_token, \'\'))) = ?', [$normalized])
            ->where(function ($query) {
                $query->whereRaw("LOWER(TRIM(COALESCE(vt.visit_type_name, ''))) = ?", ['enrollee'])
                    ->orWhereNotNull('e.enrollee_id');
            })
            ->orderByDesc('v.visit_id')
            ->select([
                'v.visit_id',
                'v.qr_token',
                'v.entry_time',
                'v.exit_time',
                'v.purpose_reason',
                'vr.visitor_id',
                'vr.first_name',
                'vr.last_name',
                'vr.control_number',
                'vr.pass_number',
                'e.enrollee_id',
                'vt.visit_type_name',
            ])
            ->first();
    }

    protected function buildTrackerPayload(object $visit): array
    {
        $steps = $this->resolveRouteSteps((int) $visit->visit_id, $visit->enrollee_id ? (int) $visit->enrollee_id : null);
        $classified = $this->classifySteps($steps);

        $total = count($classified);
        $completed = collect($classified)->where('state', 'done')->count();
        $current = collect($classified)->firstWhere('state', 'current');
        $remaining = max(0, $total - $completed);
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $passCode = trim((string) ($visit->pass_number ?? ''));
        if ($passCode === '') {
            $passCode = trim((string) ($visit->control_number ?? ''));
        }
        if ($passCode === '') {
            $passCode = trim((string) ($visit->qr_token ?? 'UNKNOWN'));
        }

        $visitorName = trim(
            trim((string) ($visit->first_name ?? '')).' '.trim((string) ($visit->last_name ?? ''))
        );

        return [
            'pass_code' => $passCode,
            'visitor_name' => $visitorName !== '' ? $visitorName : 'Enrollee',
            'control_number' => trim((string) ($visit->control_number ?? '')),
            'qr_token' => trim((string) ($visit->qr_token ?? '')),
            'office_qr_payload' => json_encode([
                'control_number' => trim((string) ($visit->control_number ?? '')),
                'qr_token' => trim((string) ($visit->qr_token ?? '')),
            ], JSON_UNESCAPED_SLASHES),
            'visit_id' => (int) $visit->visit_id,
            'total_steps' => $total,
            'completed_steps' => $completed,
            'remaining_steps' => $remaining,
            'percent' => $percent,
            'current_step' => $current,
            'steps' => $classified,
            'is_complete' => $total > 0 && $completed >= $total,
            'poll_url' => route('enrollee.progress.status', ['token' => $visit->qr_token]),
        ];
    }

    /**
     * Prefer office_expectation (live scan progress). Fall back to enrollee_progress / enrollee_step.
     */
    protected function resolveRouteSteps(int $visitId, ?int $enrolleeId): array
    {
        $expectations = DB::table('office_expectation as oe')
            ->leftJoin('office as o', 'o.office_id', '=', 'oe.office_id')
            ->leftJoin('expectation_status as xs', 'xs.expectation_status_id', '=', 'oe.expectation_status_id')
            ->where('oe.visit_id', $visitId)
            ->select([
                'oe.expected_order',
                'oe.arrived_at',
                'oe.office_id',
                'o.office_name',
                'xs.status_name as status_name',
            ])
            ->orderBy('oe.expected_order')
            ->orderBy('oe.expectation_id')
            ->get();

        if ($expectations->isNotEmpty()) {
            return $expectations->map(function ($row, $index) {
                return [
                    'order' => $row->expected_order !== null ? (int) $row->expected_order : ($index + 1),
                    'title' => trim((string) ($row->office_name ?? '')) ?: 'Enrollment Step',
                    'subtitle' => $this->defaultSubtitleForOffice(
                        (string) ($row->office_name ?? ''),
                        $row->expected_order !== null ? (int) $row->expected_order : ($index + 1)
                    ),
                    'arrived_at' => $row->arrived_at,
                    'status_name' => trim((string) ($row->status_name ?? '')),
                ];
            })->values()->all();
        }

        if (! $enrolleeId) {
            return [];
        }

        $progressRows = DB::table('enrollee_progress as ep')
            ->join('enrollee_step as es', 'es.step_id', '=', 'ep.step_id')
            ->leftJoin('office as o', 'o.office_id', '=', 'es.office_id')
            ->leftJoin('enrollee_status as st', 'st.enrollee_status_id', '=', 'ep.step_status_id')
            ->where('ep.enrollee_id', $enrolleeId)
            ->select([
                'es.step_order',
                'o.office_name',
                'ep.completed_at',
                'st.status_name',
            ])
            ->orderBy('es.step_order')
            ->orderBy('es.step_id')
            ->get();

        return $progressRows->map(function ($row, $index) {
            return [
                'order' => $row->step_order !== null ? (int) $row->step_order : ($index + 1),
                'title' => trim((string) ($row->office_name ?? '')) ?: 'Enrollment Step',
                'subtitle' => $this->defaultSubtitleForOffice(
                    (string) ($row->office_name ?? ''),
                    $row->step_order !== null ? (int) $row->step_order : ($index + 1)
                ),
                'arrived_at' => $row->completed_at,
                'status_name' => trim((string) ($row->status_name ?? '')),
            ];
        })->values()->all();
    }

    protected function classifySteps(array $steps): array
    {
        $classified = [];
        $foundCurrent = false;

        foreach ($steps as $step) {
            $isDone = $this->isStepDone($step);

            if ($isDone) {
                $state = 'done';
            } elseif (! $foundCurrent) {
                $state = 'current';
                $foundCurrent = true;
            } else {
                $state = 'pending';
            }

            $classified[] = [
                'order' => (int) ($step['order'] ?? 0),
                'title' => (string) ($step['title'] ?? 'Enrollment Step'),
                'subtitle' => (string) ($step['subtitle'] ?? ''),
                'state' => $state,
                'badge' => $state === 'done' ? 'Done' : ($state === 'current' ? 'Current' : 'Pending'),
            ];
        }

        return $classified;
    }

    protected function isStepDone(array $step): bool
    {
        if (! empty($step['arrived_at'])) {
            return true;
        }

        $status = Str::lower(trim((string) ($step['status_name'] ?? '')));

        if ($status === '') {
            return false;
        }

        foreach (['completed', 'complete', 'done', 'arrived', 'validated', 'finished', 'success'] as $needle) {
            if (str_contains($status, $needle)) {
                return true;
            }
        }

        if (str_contains($status, 'skip')) {
            return true;
        }

        return false;
    }

    protected function defaultSubtitleForOffice(string $officeName, ?int $order = null): string
    {
        $name = Str::lower(trim($officeName));

        if ($name === '') {
            return 'Proceed to the assigned office and present your QR pass.';
        }

        // Final Admissions revisit (step 9) — welcome kit / final requirements.
        if ($order !== null && $order >= 9 && str_contains($name, 'admission')) {
            return 'Claiming of Welcome Kit and Submission of Final Requirements';
        }

        $known = [
            'health' => 'Submit Chest X-ray Report and Additional Health Requirements',
            'guidance' => 'Submit Guidance Interview Form and Initial Interview',
            'registrar' => 'Issuance of Assessment and Registration Documents',
            'treasury' => 'Cashier Payment / Debit Card / Credit Card',
            'student development' => 'Issuance of ID Lace and Required Forms',
            'bulldogs' => 'Fitting and Payment of Uniform',
            'information technology' => 'Printing and Issuance of NU Lipa ID Card',
            'admission' => 'Proceed to Admissions Office and present your QR pass for validation.',
        ];

        foreach ($known as $needle => $subtitle) {
            if (str_contains($name, $needle)) {
                return $subtitle;
            }
        }

        return 'Proceed to '.$officeName.' and present your QR pass for validation.';
    }
}
