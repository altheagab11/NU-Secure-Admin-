<?php

namespace Tests\Feature;

use App\Http\Controllers\GuardAlertController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GuardActiveAlertsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'guard_alert_test', 'database.connections.guard_alert_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', fn (...$parts) => implode('', $parts));
        view()->share('cspNonce', 'test-nonce');
        foreach ([
            'CREATE TABLE alerts (alert_id INTEGER, visitor_id INTEGER, visit_id INTEGER, scan_id INTEGER, alert_type TEXT, severity TEXT, message TEXT, status TEXT, created_at TEXT, resolved_at TEXT, resolution_notes TEXT)',
            'CREATE TABLE visitor (visitor_id INTEGER, first_name TEXT, last_name TEXT, contact_no TEXT, visitor_photo_with_id_url TEXT)',
            'CREATE TABLE visit (visit_id INTEGER, visitor_id INTEGER, primary_office_id INTEGER, visit_type_id INTEGER, exit_status_id INTEGER, pass_number TEXT, control_number TEXT, purpose_reason TEXT, entry_time TEXT, exit_time TEXT, duration_minutes INTEGER, destination_text TEXT)',
            'CREATE TABLE office (office_id INTEGER, office_name TEXT)',
            'CREATE TABLE office_scan (scan_id INTEGER, office_id INTEGER, scanned_by_user_id INTEGER, scan_time TEXT, remarks TEXT)',
            'CREATE TABLE users (user_id INTEGER, first_name TEXT, last_name TEXT)',
            'CREATE TABLE visit_type (visit_type_id INTEGER, visit_type_name TEXT)',
            'CREATE TABLE exit_status (exit_status_id INTEGER, exit_status_name TEXT)',
            'CREATE TABLE expectation_status (expectation_status_id INTEGER, status_name TEXT)',
            'CREATE TABLE office_expectation (expectation_id INTEGER, visit_id INTEGER, office_id INTEGER, expected_order INTEGER, expectation_status_id INTEGER, arrived_at TEXT)',
        ] as $sql) {
            DB::statement($sql);
        }
        DB::table('office')->insert(['office_id' => 1, 'office_name' => 'Admissions Office']);
        DB::table('exit_status')->insert(['exit_status_id' => 1, 'exit_status_name' => 'Completed']);
        DB::table('expectation_status')->insert(['expectation_status_id' => 1, 'status_name' => 'Completed']);
        foreach (range(1, 28) as $id) {
            DB::table('visitor')->insert(['visitor_id' => $id, 'first_name' => 'Visitor', 'last_name' => (string) $id]);
            DB::table('visit')->insert(['visit_id' => $id, 'visitor_id' => $id, 'primary_office_id' => 1, 'exit_status_id' => 1, 'control_number' => 'CTRL-'.$id, 'entry_time' => '2026-10-09 08:00:00']);
            DB::table('office_expectation')->insert(['expectation_id' => $id, 'visit_id' => $id, 'office_id' => 1, 'expected_order' => 1, 'expectation_status_id' => 1, 'arrived_at' => sprintf('2026-10-09 09:%02d:00', $id)]);
            DB::table('alerts')->insert(['alert_id' => $id, 'visitor_id' => $id, 'visit_id' => $id, 'alert_type' => $id % 2 ? 'Incomplete Route' : 'Unauthorized Office Scan', 'severity' => $id % 2 ? 'High' : 'Medium', 'message' => 'Route requires attention', 'status' => $id === 28 ? 'Resolved' : 'Unresolved', 'created_at' => sprintf('2026-10-09 09:%02d:00', $id)]);
        }
        // Exited visitors must never reappear in either completed preview or modal.
        DB::table('visit')->where('visit_id', 28)->update(['exit_time' => '2026-10-09 10:00:00']);
    }

    protected function tearDown(): void
    {
        DB::purge('guard_alert_test');
        Paginator::currentPageResolver(fn () => 1);
        parent::tearDown();
    }

    private function response(array $filters)
    {
        Paginator::currentPageResolver(fn () => $filters['page'] ?? 1);

        return (new GuardAlertController)->index(Request::create('/guard/alert', 'GET', $filters))->getData(true);
    }

    public function test_previews_have_five_records_and_actual_counts_and_completion_time(): void
    {
        $view = (new GuardAlertController)->index(Request::create('/guard/alert'));
        $data = $view->getData();
        $this->assertSame(27, $data['unresolvedAlertsCount']);
        $this->assertSame(27, $data['readyToExitCount']);
        $this->assertCount(5, $data['completedVisitors']);
        $this->assertCount(5, $data['unresolvedAlerts']);
        $this->assertSame('Oct 09, 9:27 AM', $data['completedVisitors'][0]['completed_at']);
        $html = $view->render();
        $this->assertStringNotContainsString('data-guard-view-all', $html);
        $this->assertStringNotContainsString('id="guardListModal"', $html);
        $this->assertStringContainsString('id="completedPagination"', $html);
        $this->assertStringContainsString('id="alertsPagination"', $html);
        $this->assertStringContainsString('1 to 5 of 27', $html);
        $this->assertStringContainsString('Ready for Exit', $html);
    }

    public function test_completed_modal_reaches_all_records_and_filters_before_pagination(): void
    {
        $payload = $this->response(['list' => 'completed', 'page' => 6]);
        $this->assertSame(27, $payload['meta']['total']);
        $this->assertSame(26, $payload['meta']['from']);
        $this->assertSame(27, $payload['meta']['to']);
        $this->assertStringContainsString('Process Exit', $payload['html']);
        $filtered = $this->response(['list' => 'completed', 'search' => 'Visitor 12', 'status' => 'Completed']);
        $this->assertSame(1, $filtered['meta']['total']);
        $this->assertStringContainsString('CTRL-12', $filtered['html']);
        $this->assertSame(0, $this->response(['list' => 'completed', 'status' => 'Ready to Exit'])['meta']['total']);
    }

    public function test_alert_modal_preserves_details_and_filters_only_unresolved_records(): void
    {
        $payload = $this->response(['list' => 'alerts', 'severity' => 'Medium', 'type' => 'Unauthorized Office Scan', 'per_page' => 5, 'page' => 3]);
        $this->assertSame(13, $payload['meta']['total']);
        $this->assertCount(3, $payload['alerts']);
        $this->assertSame(6, $payload['alerts'][0]['alert_id']);
        $this->assertStringContainsString('data-alert-id="6"', $payload['html']);
        $this->assertStringContainsString('Expected Office', $payload['html']);
        $this->assertSame(0, $this->response(['list' => 'alerts', 'search' => 'Visitor 28'])['meta']['total']);
    }

    public function test_empty_states_and_list_reads_do_not_process_exits_or_resolve_alerts(): void
    {
        $payload = $this->response(['list' => 'completed', 'per_page' => 10]);
        $this->assertSame(10, substr_count($payload['html'], 'Process Exit'));
        $this->assertNull(DB::table('visit')->where('visit_id', 1)->value('exit_time'));
        $this->response(['list' => 'alerts']);
        $this->assertSame('Unresolved', DB::table('alerts')->where('alert_id', 1)->value('status'));
        DB::table('alerts')->update(['status' => 'Resolved']);
        DB::table('visit')->update(['exit_time' => '2026-10-09 10:00:00']);
        $emptyAlerts = $this->response(['list' => 'alerts']);
        $emptyCompleted = $this->response(['list' => 'completed']);
        $this->assertSame(0, $emptyAlerts['meta']['total']);
        $this->assertStringContainsString('No unresolved alerts found', $emptyAlerts['html']);
        $this->assertSame(0, $emptyCompleted['meta']['total']);
        $this->assertStringContainsString('No completed visitors found', $emptyCompleted['html']);
    }
}
