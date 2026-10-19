<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\OfficeVisitorQueryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OfficeAdjustmentTest extends TestCase
{
    private const QR = 'QR-office-adjustment-test';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'office_adjustment_test', 'database.connections.office_adjustment_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        foreach ([
            'CREATE TABLE users (user_id INTEGER PRIMARY KEY, role_id INTEGER, first_name TEXT, last_name TEXT, email TEXT, status TEXT, created_at TEXT)',
            'CREATE TABLE office (office_id INTEGER PRIMARY KEY, office_name TEXT, floor TEXT, is_active BOOLEAN)',
            'CREATE TABLE office_staff (staff_id INTEGER PRIMARY KEY, user_id INTEGER, office_id INTEGER, position TEXT)',
            'CREATE TABLE address (address_id INTEGER PRIMARY KEY, house_no TEXT, street TEXT, barangay TEXT, city_municipality TEXT, province TEXT)',
            'CREATE TABLE visitor (visitor_id INTEGER PRIMARY KEY, first_name TEXT, last_name TEXT, contact_no TEXT, visitor_photo_with_id_url TEXT, address_id INTEGER)',
            'CREATE TABLE visit_type (visit_type_id INTEGER PRIMARY KEY, visit_type_name TEXT)',
            'CREATE TABLE exit_status (exit_status_id INTEGER PRIMARY KEY, exit_status_name TEXT)',
            'CREATE TABLE visit (visit_id INTEGER PRIMARY KEY, visitor_id INTEGER, guard_user_id INTEGER, primary_office_id INTEGER, visit_type_id INTEGER, exit_status_id INTEGER, control_number TEXT, pass_number TEXT, qr_token TEXT, purpose_reason TEXT, entry_time TEXT, exit_time TEXT, duration_minutes INTEGER, destination_text TEXT)',
            'CREATE TABLE expectation_status (expectation_status_id INTEGER PRIMARY KEY, status_name TEXT)',
            'CREATE TABLE office_expectation (expectation_id INTEGER PRIMARY KEY AUTOINCREMENT, visit_id INTEGER, office_id INTEGER, expected_order INTEGER, expectation_status_id INTEGER, created_at TEXT, arrived_at TEXT)',
            'CREATE TABLE validation_status (validation_status_id INTEGER PRIMARY KEY, status_name TEXT)',
            'CREATE TABLE office_scan (scan_id INTEGER PRIMARY KEY AUTOINCREMENT, visit_id INTEGER, office_id INTEGER, scanned_by_user_id INTEGER, scan_time TEXT, validation_status_id INTEGER, remarks TEXT)',
            'CREATE TABLE notif_type (notif_type_id INTEGER PRIMARY KEY, notif_type_name TEXT)',
            'CREATE TABLE notification (notif_id INTEGER PRIMARY KEY AUTOINCREMENT, scan_id INTEGER, recipient_user_id INTEGER, notif_type_id INTEGER, message TEXT, sent_at TEXT, read_at TEXT)',
            'CREATE TABLE alerts (alert_id INTEGER PRIMARY KEY AUTOINCREMENT, visit_id INTEGER, visitor_id INTEGER, scan_id INTEGER, alert_type TEXT, severity TEXT, message TEXT, status TEXT, created_at TEXT, resolved_at TEXT, resolved_by INTEGER, resolution_notes TEXT)',
            'CREATE TABLE enrollee (enrollee_id INTEGER PRIMARY KEY, visit_id INTEGER, visitor_id INTEGER)',
        ] as $sql) {
            DB::statement($sql);
        }
        (require database_path('migrations/2026_08_15_194500_create_activity_logs_table.php'))->up();
        (require database_path('migrations/2026_10_19_000000_add_office_adjustment_details_to_office_expectation.php'))->up();
        DB::table('office')->insert([
            ['office_id' => 1, 'office_name' => 'Health Services Office', 'is_active' => true],
            ['office_id' => 2, 'office_name' => 'Admissions Office', 'is_active' => true],
            ['office_id' => 3, 'office_name' => 'Registrar', 'is_active' => true],
        ]);
        DB::table('users')->insert([
            ['user_id' => 10, 'role_id' => 3, 'first_name' => 'Staff', 'last_name' => 'Admissions', 'status' => 'Active'],
            ['user_id' => 11, 'role_id' => 3, 'first_name' => 'Staff', 'last_name' => 'Health', 'status' => 'Active'],
            ['user_id' => 20, 'role_id' => 2, 'first_name' => 'Guard', 'last_name' => 'Test', 'status' => 'Active'],
            ['user_id' => 30, 'role_id' => 1, 'first_name' => 'Admin', 'last_name' => 'Test', 'status' => 'Active'],
            ['user_id' => 40, 'role_id' => 4, 'first_name' => 'Kiosk', 'last_name' => 'Test', 'status' => 'Active'],
        ]);
        DB::table('office_staff')->insert([
            ['staff_id' => 1, 'user_id' => 10, 'office_id' => 2],
            ['staff_id' => 2, 'user_id' => 11, 'office_id' => 1],
        ]);
        DB::table('visitor')->insert(['visitor_id' => 1, 'first_name' => 'Test', 'last_name' => 'Visitor']);
        DB::table('visit_type')->insert([['visit_type_id' => 1, 'visit_type_name' => 'Visitor'], ['visit_type_id' => 2, 'visit_type_name' => 'Enrollee']]);
        DB::table('exit_status')->insert([['exit_status_id' => 1, 'exit_status_name' => 'Active'], ['exit_status_id' => 2, 'exit_status_name' => 'Completed']]);
        DB::table('visit')->insert(['visit_id' => 1, 'visitor_id' => 1, 'primary_office_id' => 1, 'visit_type_id' => 1, 'exit_status_id' => 1,
            'qr_token' => self::QR, 'control_number' => 'CTRL-1', 'pass_number' => 'PASS-1', 'purpose_reason' => 'Consultation', 'entry_time' => now('Asia/Manila')->toDateTimeString()]);
        DB::table('expectation_status')->insert([['expectation_status_id' => 1, 'status_name' => 'Pending'], ['expectation_status_id' => 2, 'status_name' => 'Arrived']]);
        DB::table('validation_status')->insert([['validation_status_id' => 1, 'status_name' => 'Valid'], ['validation_status_id' => 3, 'status_name' => 'Unauthorized']]);
        DB::table('notif_type')->insert([['notif_type_id' => 1, 'notif_type_name' => 'Wrong Office'], ['notif_type_id' => 2, 'notif_type_name' => 'Incoming Visitor']]);
        DB::table('office_expectation')->insert(['visit_id' => 1, 'office_id' => 1, 'expected_order' => 1, 'expectation_status_id' => 1, 'created_at' => now()]);
        $this->actingAs(User::findOrFail(10));
    }

    protected function tearDown(): void
    {
        DB::purge('office_adjustment_test');
        parent::tearDown();
    }

    private function wrongScan(string $method = 'hardware'): int
    {
        return (int) $this->postJson(route('office.scanner.check-in'), ['qr_payload' => self::QR, 'scan_method' => $method])
            ->assertUnprocessable()->assertJsonPath('code', 'WRONG_OFFICE')
            ->assertJsonPath('data.can_adjust_office', true)->json('data.wrong_office_scan_id');
    }

    private function addPayload(int $scanId, bool $checkIn = true): array
    {
        return ['qr_payload' => self::QR, 'scan_id' => $scanId, 'reason' => 'Additional consultation', 'automatic_check_in' => $checkIn, 'scan_method' => 'hardware'];
    }

    public function test_correct_office_scans_still_check_in_and_have_no_adjustment_actions(): void
    {
        $this->actingAs(User::findOrFail(11));
        $this->postJson(route('office.scanner.check-in'), ['qr_payload' => self::QR, 'scan_method' => 'camera'])
            ->assertOk()->assertJsonPath('data.authorized', true)->assertJsonPath('data.checked_in', true)->assertJsonMissingPath('data.can_adjust_office');
        $this->assertDatabaseCount('office_scan', 1);
        $this->postJson(route('office.scanner.check-in'), ['qr_payload' => self::QR])->assertStatus(422);
        $this->assertDatabaseCount('office_scan', 1);
    }

    public function test_add_office_and_check_in_preserves_original_route_qr_and_security_records(): void
    {
        $original = (array) DB::table('office_expectation')->first();
        $scanId = $this->wrongScan();
        $this->postJson(route('office.scanner.add-office'), $this->addPayload($scanId))->assertOk()
            ->assertJsonPath('data.checked_in', true)->assertJsonPath('data.can_adjust_office', false)
            ->assertJsonPath('data.staff_office.office_id', 2);
        $this->assertSame($original, (array) DB::table('office_expectation')->where('office_id', 1)->first());
        $this->assertDatabaseHas('office_expectation', ['office_id' => 2, 'expected_order' => 2, 'added_by_user_id' => 10, 'addition_reason' => 'Additional consultation']);
        $this->assertNotNull(DB::table('office_expectation')->where('office_id', 2)->value('arrived_at'));
        $this->assertDatabaseHas('visit', ['visit_id' => 1, 'qr_token' => self::QR, 'primary_office_id' => 1]);
        $this->assertDatabaseHas('office_scan', ['scan_id' => $scanId, 'validation_status_id' => 3]);
        $this->assertDatabaseHas('alerts', ['scan_id' => $scanId, 'status' => 'Unresolved']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'Office Added', 'user_id' => 10, 'module' => 'Office Adjustments']);
        $this->assertDatabaseCount('office_scan', 2);
        $this->postJson(route('office.scanner.add-office'), $this->addPayload($scanId))->assertConflict()->assertJsonPath('code', 'OFFICE_ALREADY_REGISTERED');
        $this->assertDatabaseCount('office_scan', 2);
        $this->assertSame(1, ActivityLog::where('action', 'Office Added')->count());
    }

    public function test_add_without_check_in_is_expected_and_can_check_in_later_using_same_qr(): void
    {
        $scanId = $this->wrongScan('camera');
        $this->postJson(route('office.scanner.add-office'), $this->addPayload($scanId, false))->assertOk()->assertJsonPath('data.checked_in', false);
        $this->assertDatabaseCount('office_scan', 1);
        $this->assertNull(DB::table('office_expectation')->where('office_id', 2)->value('arrived_at'));
        $expected = app(OfficeVisitorQueryService::class)->expectedVisitorsPreview(2, null);
        $this->assertSame('ready', $expected->first()->route_status_key);
        $this->postJson(route('office.scanner.check-in'), ['qr_payload' => self::QR, 'scan_method' => 'hardware'])->assertOk()->assertJsonPath('data.checked_in', true);
        $this->assertDatabaseCount('office_scan', 2);
    }

    public function test_wrong_office_remarks_reuse_scan_and_are_idempotent_without_check_in_or_route_changes(): void
    {
        $scanId = $this->wrongScan();
        $before = (array) DB::table('office_expectation')->first();
        $payload = ['qr_payload' => self::QR, 'scan_id' => $scanId, 'remarks' => 'Visitor directed to Health Services Office.'];
        foreach (range(1, 2) as $attempt) {
            $this->postJson(route('office.scanner.wrong-office'), $payload)->assertOk()->assertJsonPath('data.authorized', false);
        }
        $this->assertSame($before, (array) DB::table('office_expectation')->first());
        $this->assertDatabaseCount('office_scan', 1);
        $scan = DB::table('office_scan')->first();
        $this->assertSame(3, $scan->validation_status_id);
        $this->assertStringContainsString('Staff remarks: '.$payload['remarks'], $scan->remarks);
        $this->assertDatabaseHas('alerts', ['scan_id' => $scanId, 'status' => 'Unresolved']);
        $log = ActivityLog::where('action', 'Wrong Office Recorded')->sole();
        $this->assertSame(10, $log->user_id);
        $this->assertNotNull($log->created_at);
        $this->assertSame($payload['remarks'], $log->new_values['reason']);
    }

    public function test_empty_whitespace_and_overlong_reason_or_remarks_are_rejected(): void
    {
        $scanId = $this->wrongScan();
        foreach (['', '   ', str_repeat('x', 256)] as $reason) {
            $this->postJson(route('office.scanner.add-office'), array_replace($this->addPayload($scanId), ['reason' => $reason]))
                ->assertUnprocessable()->assertJsonValidationErrors('reason');
        }
        foreach (['', '   ', str_repeat('x', 501)] as $remarks) {
            $this->postJson(route('office.scanner.wrong-office'), ['qr_payload' => self::QR, 'scan_id' => $scanId, 'remarks' => $remarks])
                ->assertUnprocessable()->assertJsonValidationErrors('remarks');
        }
        $this->assertDatabaseCount('office_expectation', 1);
        $this->assertDatabaseCount('office_scan', 1);
    }

    public function test_checked_out_cancelled_and_completed_visits_cannot_be_adjusted(): void
    {
        $scanId = $this->wrongScan();
        foreach ([['exit_time' => now()->toDateTimeString()], ['exit_time' => null, 'exit_status_id' => 2]] as $changes) {
            DB::table('visit')->update($changes);
            $this->postJson(route('office.scanner.add-office'), $this->addPayload($scanId))->assertUnprocessable();
            $this->postJson(route('office.scanner.wrong-office'), ['qr_payload' => self::QR, 'scan_id' => $scanId, 'remarks' => 'Wrong destination'])->assertUnprocessable();
        }
        $this->assertDatabaseCount('office_expectation', 1);
        $this->assertDatabaseCount('office_scan', 1);
    }

    public function test_permissions_reject_other_roles_offices_and_foreign_scans(): void
    {
        $scanId = $this->wrongScan();
        foreach ([20, 30, 40] as $userId) {
            $this->actingAs(User::findOrFail($userId));
            $this->postJson(route('office.scanner.add-office'), $this->addPayload($scanId))->assertForbidden();
            $this->postJson(route('office.scanner.wrong-office'), ['qr_payload' => self::QR, 'scan_id' => $scanId, 'remarks' => 'Wrong'])->assertForbidden();
        }
        $this->actingAs(User::findOrFail(10));
        $this->postJson(route('office.scanner.add-office'), $this->addPayload($scanId) + ['office_id' => 3])->assertForbidden();
        DB::table('office_scan')->where('scan_id', $scanId)->update(['office_id' => 3]);
        $this->postJson(route('office.scanner.add-office'), $this->addPayload($scanId))->assertUnprocessable();
        $this->postJson(route('office.scanner.wrong-office'), ['qr_payload' => self::QR, 'scan_id' => $scanId, 'remarks' => 'Wrong'])->assertUnprocessable();
        $this->assertDatabaseCount('office_expectation', 1);
    }

    public function test_repeated_camera_and_hardware_scans_reuse_recent_failed_scan_and_alert(): void
    {
        $first = $this->wrongScan();
        $this->assertSame($first, $this->wrongScan());
        $this->assertDatabaseCount('office_scan', 1);
        $this->assertDatabaseCount('alerts', 1);
    }

    public function test_added_enrollee_office_is_independent_of_original_sequential_steps(): void
    {
        DB::table('visit')->update(['visit_type_id' => 2]);
        $scanId = $this->wrongScan();
        $this->postJson(route('office.scanner.add-office'), $this->addPayload($scanId, false))->assertOk();
        $queries = app(OfficeVisitorQueryService::class);
        $this->assertSame('ready', $queries->expectedVisitorsPreview(2, null)->first()->route_status_key);
        $this->assertCount(1, $queries->liveMonitoring(2)['waiting']);
        $this->postJson(route('office.scanner.check-in'), ['qr_payload' => self::QR])->assertOk();
        $this->assertNull(DB::table('office_expectation')->where('office_id', 1)->value('arrived_at'));
        $this->assertSame(1, DB::table('office_expectation')->where('office_id', 1)->value('expected_order'));
    }

    public function test_existing_enrollee_office_blocked_by_previous_step_cannot_use_adjustment_actions(): void
    {
        DB::table('visit')->update(['visit_type_id' => 2]);
        DB::table('office_expectation')->insert(['visit_id' => 1, 'office_id' => 2, 'expected_order' => 2, 'expectation_status_id' => 1]);
        $this->postJson(route('office.scanner.check-in'), ['qr_payload' => self::QR])->assertUnprocessable()
            ->assertJsonPath('code', 'PREVIOUS_INCOMPLETE')->assertJsonMissingPath('data.can_adjust_office');
    }

    public function test_guard_can_add_without_check_in_and_review_audit_history_in_existing_drawer(): void
    {
        $this->actingAs(User::findOrFail(20));
        $url = route('guard.visits.add-office', ['visitId' => 1]);
        $this->postJson($url, ['office_id' => 2, 'reason' => 'Guard authorized extra stop'])->assertOk()->assertJsonPath('data.checked_in', false);
        $this->assertDatabaseHas('office_expectation', ['office_id' => 2, 'added_by_user_id' => 20]);
        $this->assertDatabaseCount('office_scan', 0);
        $this->getJson('/guard/dashboard/visits/1/details')->assertOk()
            ->assertJsonPath('office_adjustments.0.performed_by', 'Guard Test')
            ->assertJsonPath('office_adjustments.0.reason', 'Guard authorized extra stop');
        $this->actingAs(User::findOrFail(10));
        $this->postJson($url, ['office_id' => 3, 'reason' => 'Not a guard'])->assertForbidden();
    }

    public function test_check_in_failure_rolls_back_the_office_addition(): void
    {
        $scanId = $this->wrongScan();
        DB::statement("CREATE TRIGGER reject_check_in BEFORE INSERT ON office_scan WHEN NEW.validation_status_id = 1 BEGIN SELECT RAISE(FAIL, 'Simulated check-in failure'); END");
        $this->postJson(route('office.scanner.add-office'), $this->addPayload($scanId))->assertUnprocessable();
        $this->assertDatabaseCount('office_expectation', 1);
        $this->assertDatabaseCount('office_scan', 1);
        $this->assertSame(0, ActivityLog::where('action', 'Office Added')->count());
    }

    public function test_migration_rolls_back_without_changing_existing_remarks(): void
    {
        (require database_path('migrations/2026_10_19_000000_add_office_adjustment_details_to_office_expectation.php'))->down();
        $this->assertFalse(Schema::hasColumn('office_expectation', 'addition_reason'));
        $this->assertTrue(Schema::hasColumn('office_scan', 'remarks'));
    }
}
