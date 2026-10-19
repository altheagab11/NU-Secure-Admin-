<?php

namespace App\Http\Controllers;

use App\Services\OfficeScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuardOfficeAdjustmentController extends Controller
{
    public function store(Request $request, int $visitId, OfficeScanService $scanner)
    {
        abort_unless((int) $request->user()?->role_id === 2, 403);
        $validated = $request->validate([
            'office_id' => ['required', 'integer', 'exists:office,office_id'],
            'reason' => ['required', 'string', 'max:255'],
        ]);
        $visit = DB::table('visit')->where('visit_id', $visitId)->first();
        abort_unless($visit, 404);
        $office = DB::table('office')->where('office_id', $validated['office_id'])->where('is_active', true)->first();
        abort_unless($office, 422, 'Choose an active office.');
        $office->user_id = (int) $request->user()->user_id;
        // Guards adjust the route here; office staff record office arrival at their scanner.
        $result = $scanner->addOffice(json_encode(['qr_token' => $visit->qr_token, 'control_number' => $visit->control_number]),
            $office, $validated['reason'], checkIn: false);

        return response()->json($result, $result['http'] ?? 400);
    }
}
