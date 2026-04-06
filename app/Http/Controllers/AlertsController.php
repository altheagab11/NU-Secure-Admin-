<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
    // prefer SERVICE_ROLE key if present (as in your .env screenshot), fall back to SUPABASE_KEY
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
            $select = 'alert_id,created_at,alert_type,severity,status,' .
                // visitor fields
                'visitor(first_name,last_name,pass_number,control_number),' .
                // visit -> primary_office -> office_name
                'visit(primary_office_id,office(office_name)),' .
                // office_scan -> office -> office_name
                'office_scan(office(office_name)),' .
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
}
