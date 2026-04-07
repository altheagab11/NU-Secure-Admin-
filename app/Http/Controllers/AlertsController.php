<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class AlertsController extends Controller
{
    /**
     * Display a listing of alerts fetched from Supabase (PostgREST).
     *
     * Note: This implementation assumes Supabase REST relationships are named
     * `visitor`, `visit`, `office_scan`, and `resolved_by` and that `office`
     * is nested under `visit` and `office_scan` as shown in the mapping image.
     * If your relationship names differ, adjust the $select string accordingly.
     */
    public function index(Request $request)
    {
    $supabaseUrl = env('SUPABASE_URL');
    // prefer SERVICE_ROLE key if present , fall back to SUPABASE_KEY
    $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_KEY');

        // Safe defaults for the view
        $alerts = [];
        $total = 0;
        $resolvedCount = 0;
        $unresolvedCount = 0;
        $criticalCount = 0;

        if ($supabaseUrl && $supabaseKey) {
            // Try to fetch alerts with related data. Adjust this select if your
            // PostgREST relationships have different names.
            $select = 'alert_id,created_at,alert_type,severity,status,resolved_at,resolution_notes,' .
                // visitor fields
                'visitor(first_name,last_name,pass_number,control_number,contact_no),' .
                // visit fields + visit_type + primary office mapping
                'visit(visit_id,purpose_reason,entry_time,exit_time,duration_minutes,primary_office_id,visit_type(visit_type_name),office(office_name)),' .
                // office_scan fields + office + scanned_by user + validation status
                'office_scan(scan_id,scan_time,remarks,office(office_name),users(first_name,last_name),validation_status(status_name)),' .
                // resolved_by user
                'resolved_by(first_name,last_name)';

            try {
                $response = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get(rtrim($supabaseUrl, '/') . '/rest/v1/alerts', [
                    'select' => $select,
                    'order' => 'created_at.desc',
                ]);

                if ($response->ok()) {
                    $alerts = $response->json();

                    $total = count($alerts);
                    $resolvedCount = count(array_filter($alerts, function ($a) {
                        return (isset($a['status']) && strtolower($a['status']) === 'resolved');
                    }));
                    $unresolvedCount = $total - $resolvedCount;
                    $criticalCount = count(array_filter($alerts, function ($a) {
                        return (isset($a['severity']) && strtolower($a['severity']) === 'critical');
                    }));
                } else {
                    // Log non-200 response to help debugging (will appear in storage/logs)
                    logger()->warning('Supabase alerts fetch returned non-OK status', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                // Network or other HTTP client error — keep defaults and log
                logger()->error('Supabase fetch failed: ' . $e->getMessage());
            }
        }

        return view('admin.alert', [
            'alerts' => $alerts,
            'total' => $total,
            'resolvedCount' => $resolvedCount,
            'unresolvedCount' => $unresolvedCount,
            'criticalCount' => $criticalCount,
        ]);
    }

    /**
     * Resolve an alert and persist resolution details to Supabase.
     */
    public function resolve(Request $request, $alertId)
    {
        $validated = $request->validate([
            'resolution_notes' => ['required', 'string', 'max:2000'],
        ]);

        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_KEY');

        if (!$supabaseUrl || !$supabaseKey) {
            return response()->json([
                'message' => 'Supabase configuration is missing.',
            ], 500);
        }

        $authUser = Auth::user();
        $resolvedById = $authUser->user_id ?? $authUser->id ?? session('user_id') ?? null;
    // if (!$resolvedById) {
    //         return response()->json([
    //             'message' => 'Unable to resolve alert: no logged-in user found.',
    //         ], 422);
    //     }
        $resolvedAt = now()->toIso8601String();

        try {
            $response = Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'Prefer' => 'return=representation',
            ])->withQueryParameters([
                'alert_id' => 'eq.' . $alertId,
                'select' => 'alert_id,status,resolved_at,resolution_notes,resolved_by(first_name,last_name)',
            ])->patch(rtrim($supabaseUrl, '/') . '/rest/v1/alerts', [
                'status' => 'Resolved',
                'resolved_at' => $resolvedAt,
                'resolved_by' => $resolvedById,
                'resolution_notes' => $validated['resolution_notes'],
            ]);

            if (!$response->ok()) {
                logger()->warning('Supabase resolve update failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'alert_id' => $alertId,
                ]);

                return response()->json([
                    'message' => 'Failed to save resolution to Supabase.',
                ], 500);
            }

            $updated = $response->json();
            $updatedAlert = is_array($updated) && array_key_exists(0, $updated) ? $updated[0] : $updated;

            return response()->json([
                'message' => 'Alert resolved successfully.',
                'alert' => $updatedAlert,
            ]);
        } catch (\Exception $e) {
            logger()->error('Supabase resolve request failed: ' . $e->getMessage(), [
                'alert_id' => $alertId,
            ]);

            return response()->json([
                'message' => 'Unable to resolve alert at the moment.',
            ], 500);
        }
    }
}
