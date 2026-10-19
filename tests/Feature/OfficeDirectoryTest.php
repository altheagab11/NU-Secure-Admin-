<?php

namespace Tests\Feature;

use App\Mail\UserMail;
use App\Models\Office;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OfficeDirectoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'directory_test', 'database.connections.directory_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
        foreach ([
            'CREATE TABLE users (user_id INTEGER PRIMARY KEY AUTOINCREMENT, first_name TEXT, last_name TEXT, email TEXT UNIQUE, password_hash TEXT, role_id INTEGER, status TEXT, created_at TEXT)',
            'CREATE TABLE office (office_id INTEGER PRIMARY KEY AUTOINCREMENT, office_name TEXT UNIQUE, floor TEXT, is_active BOOLEAN DEFAULT 1)',
            'CREATE TABLE office_staff (staff_id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, office_id INTEGER REFERENCES office(office_id), position TEXT)',
            'CREATE TABLE visitor (visitor_id INTEGER PRIMARY KEY, first_name TEXT, last_name TEXT)',
            'CREATE TABLE visit (visit_id INTEGER PRIMARY KEY, primary_office_id INTEGER REFERENCES office(office_id), visitor_id INTEGER, exit_time TEXT)',
            'CREATE TABLE office_expectation (expectation_id INTEGER PRIMARY KEY, visit_id INTEGER, office_id INTEGER REFERENCES office(office_id))',
            'CREATE TABLE office_scan (scan_id INTEGER PRIMARY KEY, visit_id INTEGER, office_id INTEGER REFERENCES office(office_id), remarks TEXT)',
            'CREATE TABLE alerts (alert_id INTEGER PRIMARY KEY, status TEXT)',
            'CREATE TABLE password_reset_tokens (email TEXT PRIMARY KEY, token TEXT, created_at TEXT)',
            'CREATE TABLE enrollee_step (step_id INTEGER PRIMARY KEY, office_id INTEGER, step_order INTEGER, is_active BOOLEAN)',
        ] as $sql) {
            DB::statement($sql);
        }
        (require database_path('migrations/2026_08_15_194500_create_activity_logs_table.php'))->up();
        (require database_path('migrations/2026_10_19_010000_add_room_number_to_office_table.php'))->up();
        DB::table('users')->insert([
            ['user_id' => 1, 'first_name' => 'Admin', 'last_name' => 'Demo', 'email' => 'admin@example.test', 'role_id' => 1, 'status' => 'Active'],
            ['user_id' => 2, 'first_name' => 'Health', 'last_name' => 'Staff', 'email' => 'staff@example.test', 'role_id' => 3, 'status' => 'Active'],
            ['user_id' => 3, 'first_name' => 'Guard', 'last_name' => 'Demo', 'email' => 'guard@example.test', 'role_id' => 2, 'status' => 'Active'],
            ['user_id' => 4, 'first_name' => 'Kiosk', 'last_name' => 'Demo', 'email' => 'kiosk@example.test', 'role_id' => 4, 'status' => 'Active'],
        ]);
        Office::insert([
            ['office_id' => 1, 'office_name' => 'Health Services Office', 'floor' => 'Ground Floor', 'is_active' => true],
            ['office_id' => 2, 'office_name' => 'Library Office', 'floor' => '2nd Floor', 'is_active' => true],
            ['office_id' => 3, 'office_name' => 'Archived Office', 'floor' => null, 'is_active' => false],
        ]);
        DB::table('office_staff')->insert(['user_id' => 2, 'office_id' => 1, 'position' => 'Staff']);
        DB::table('visit')->insert(['visit_id' => 1, 'primary_office_id' => 1, 'visitor_id' => 1]);
        DB::table('office_expectation')->insert(['expectation_id' => 1, 'visit_id' => 1, 'office_id' => 1]);
        DB::table('office_scan')->insert(['scan_id' => 1, 'visit_id' => 1, 'office_id' => 1, 'remarks' => 'Existing scan']);
        $this->actingAs(User::findOrFail(1));
        Mail::fake();
    }

    protected function tearDown(): void
    {
        DB::purge('directory_test');
        parent::tearDown();
    }

    private function payload(array $changes = []): array
    {
        return array_replace(['office_name' => 'Scholarship Office', 'room_number' => 'G-03', 'floor' => 'Ground Floor', 'is_active' => true, 'directory_form' => 'office'], $changes);
    }

    public function test_admin_creates_office_with_room_floor_and_audit_record_and_preserves_filter_url(): void
    {
        $url = '/admin/user/offices?directory_status=active&office=1&page=2';
        $this->from($url)->post(route('admin.offices.store'), $this->payload())->assertRedirect($url)->assertSessionHas('directory_success', 'Office added successfully!');
        $office = Office::where('office_name', 'Scholarship Office')->sole();
        $this->assertSame('G-03', $office->room_number);
        $this->assertSame('Ground Floor', $office->floor);
        $this->assertTrue($office->is_active);
        $this->assertDatabaseHas('activity_logs', ['action' => 'Office Created', 'user_id' => 1, 'entity_id' => $office->office_id]);
    }

    public function test_case_insensitive_trimmed_duplicates_and_required_or_invalid_fields_are_rejected(): void
    {
        foreach (['health services office', ' HEALTH SERVICES OFFICE '] as $name) {
            $this->postJson(route('admin.offices.store'), $this->payload(['office_name' => $name]))->assertUnprocessable()->assertJsonValidationErrors('office_name');
        }
        foreach ([['office_name' => ''], ['office_name' => str_repeat('x', 151)], ['room_number' => ''], ['room_number' => 'Room 2!'], ['room_number' => str_repeat('x', 31)], ['floor' => '']] as $changes) {
            $this->postJson(route('admin.offices.store'), $this->payload($changes))->assertUnprocessable()->assertJsonValidationErrors(array_keys($changes));
        }
        $this->assertDatabaseCount('office', 3);
    }

    public function test_edit_updates_location_and_name_without_changing_staff_visits_or_scans(): void
    {
        $this->put(route('admin.offices.update', 1), $this->payload(['office_name' => 'University Health Services', 'room_number' => '204A', 'floor' => '2nd Floor']))
            ->assertRedirect()->assertSessionHas('directory_success', 'Office updated successfully!');
        $this->assertDatabaseHas('office', ['office_id' => 1, 'office_name' => 'University Health Services', 'room_number' => '204A']);
        $this->assertDatabaseHas('office_staff', ['user_id' => 2, 'office_id' => 1]);
        $this->assertDatabaseHas('visit', ['visit_id' => 1, 'primary_office_id' => 1]);
        $this->assertDatabaseHas('office_scan', ['scan_id' => 1, 'remarks' => 'Existing scan']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'Office Updated', 'entity_id' => 1]);
        $this->putJson(route('admin.offices.update', 2), $this->payload(['office_name' => 'university health services']))->assertUnprocessable();
    }

    public function test_deactivate_and_reactivate_preserve_history_and_record_both_audits(): void
    {
        DB::table('visit')->update(['exit_time' => '2026-10-19 10:00:00']);
        DB::table('users')->where('user_id', 2)->update(['status' => 'recycle_bin']);
        $this->patch(route('admin.offices.status', 1), ['is_active' => false])->assertRedirect();
        $this->assertFalse(Office::find(1)->is_active);
        $this->assertDatabaseCount('office_scan', 1);
        $this->assertDatabaseCount('office_expectation', 1);
        $this->assertDatabaseCount('office_staff', 1);
        $this->patch(route('admin.offices.status', 1), ['is_active' => true])->assertRedirect();
        $this->assertTrue(Office::find(1)->is_active);
        $this->assertDatabaseHas('activity_logs', ['action' => 'Office Deactivated', 'user_id' => 1]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'Office Reactivated', 'user_id' => 1]);
    }

    public function test_deactivation_is_blocked_for_active_primary_route_visits_and_staff_including_edit_toggle(): void
    {
        $this->patchJson(route('admin.offices.status', 1), ['is_active' => false])->assertUnprocessable()->assertJsonValidationErrors('is_active');
        DB::table('visit')->update(['primary_office_id' => 2]);
        DB::table('users')->where('user_id', 2)->update(['status' => 'recycle_bin']);
        $this->patchJson(route('admin.offices.status', 1), ['is_active' => false])->assertUnprocessable();
        DB::table('visit')->update(['exit_time' => '2026-10-19 10:00:00']);
        DB::table('users')->where('user_id', 2)->update(['status' => 'Active']);
        $this->putJson(route('admin.offices.update', 1), $this->payload(['office_name' => 'Health Services Office', 'is_active' => false]))->assertUnprocessable();
        $this->assertTrue(Office::find(1)->is_active);
    }

    public function test_directory_and_account_search_filters_and_pagination_are_independent(): void
    {
        foreach (range(1, 12) as $id) {
            Office::create(['office_name' => sprintf('Test Office %02d', $id), 'is_active' => true]);
        }
        $response = $this->get('/admin/user/offices?directory_search=TEST&directory_status=active&directory_per_page=5&directory_page=2&search=STAFF&office=1&position=Staff');
        $response->assertOk()->assertSee('Office Management')->assertSee('Office Directory')->assertSee('Office User Accounts');
        $directory = $response->viewData('directoryOffices');
        $this->assertSame(12, $directory->total());
        $this->assertSame('Test Office 06', $directory->first()->office_name);
        $this->assertSame(1, $response->viewData('offices')->total());
        $this->assertStringContainsString('office=1', $directory->nextPageUrl());
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//*[@id="officeAccounts"]//*[@id="openAddOfficeBtn"]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="officeAccounts"]//form[contains(@class,"office-filters-form")]')->length);
        $this->assertSame(2, $xpath->query('//*[contains(@class,"table-pagination-bar")]')->length);
    }

    public function test_active_offices_appear_in_assignment_and_guard_destinations_with_saved_location(): void
    {
        $this->post(route('admin.offices.store'), $this->payload())->assertRedirect();
        $office = Office::where('office_name', 'Scholarship Office')->sole();
        $page = $this->get('/admin/user/offices')->assertOk();
        $this->assertContains($office->office_id, $page->viewData('officeOptions')->pluck('office_id'));
        $this->assertNotContains(3, $page->viewData('officeOptions')->pluck('office_id'));
        $this->actingAs(User::findOrFail(3));
        $destinations = $this->getJson('/guard/offices')->assertOk()->json('offices');
        $this->assertNotContains(3, array_column($destinations, 'office_id'));
        $saved = collect($destinations)->firstWhere('office_id', $office->office_id);
        $this->assertSame('G-03', $saved['room_number']);
        $this->assertSame('Ground Floor', $saved['floor']);
    }

    public function test_non_admin_roles_cannot_manage_office_directory(): void
    {
        foreach ([2, 3, 4] as $userId) {
            $this->actingAs(User::findOrFail($userId));
            $this->get('/admin/user/offices')->assertForbidden();
            $this->postJson(route('admin.offices.store'), $this->payload())->assertForbidden();
            $this->putJson(route('admin.offices.update', 2), $this->payload())->assertForbidden();
            $this->patchJson(route('admin.offices.status', 2), ['is_active' => false])->assertForbidden();
        }
    }

    public function test_registration_destinations_follow_admin_location_edits_and_blank_rooms_for_both_visitor_flows(): void
    {
        DB::table('enrollee_step')->insert(['step_id' => 1, 'office_id' => 1, 'step_order' => 1, 'is_active' => true]);
        $this->put(route('admin.offices.update', 1), $this->payload(['office_name' => 'Updated Health Office', 'floor' => '2nd Floor', 'room_number' => '201']))->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs(User::findOrFail(3));
        foreach (['normal', 'enrollee'] as $type) {
            $offices = $this->getJson('/guard/offices?register_type='.$type)->assertOk()->json('offices');
            $office = collect($offices)->firstWhere('office_id', 1);
            $this->assertSame('Updated Health Office', $office['office_name']);
            $this->assertSame('2nd Floor', $office['floor']);
            $this->assertSame('201', $office['room_number']);
        }
        $this->actingAs(User::findOrFail(1));
        $this->put(route('admin.offices.update', 1), $this->payload(['office_name' => 'Updated Health Office', 'floor' => '6th Floor', 'room_number' => null]))->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs(User::findOrFail(3));
        foreach (['normal', 'enrollee'] as $type) {
            $office = collect($this->getJson('/guard/offices?register_type='.$type)->assertOk()->json('offices'))->firstWhere('office_id', 1);
            $this->assertSame('6th Floor', $office['floor']);
            $this->assertEmpty($office['room_number']);
        }
    }

    public function test_existing_account_creation_edit_and_recycle_remain_available(): void
    {
        $this->post('/admin/user/offices', ['first_name' => 'Library', 'last_name' => 'Staff', 'email' => 'library@example.test', 'office_id' => 2, 'position' => 'Main'])->assertRedirect()->assertSessionHasNoErrors();
        Mail::assertSent(UserMail::class);
        $staff = User::where('email', 'library@example.test')->sole();
        $this->put('/admin/user/offices/'.$staff->user_id, ['first_name' => 'Updated', 'last_name' => 'Staff', 'office_id' => 2, 'position' => 'Staff'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['user_id' => $staff->user_id, 'first_name' => 'Updated']);
        $this->delete('/admin/user/offices/'.$staff->user_id)->assertRedirect();
        $this->assertDatabaseHas('users', ['user_id' => $staff->user_id, 'status' => 'recycle_bin']);
        $this->assertDatabaseHas('office', ['office_id' => 2, 'is_active' => true]);
        $this->post('/admin/user/offices/'.$staff->user_id.'/restore')->assertRedirect();
        $this->assertNotSame('recycle_bin', $staff->fresh()->status);
    }

    public function test_legacy_null_locations_can_be_edited_and_migration_rolls_back_without_changing_floor(): void
    {
        $this->put(route('admin.offices.update', 3), ['office_name' => 'Archived Office', 'room_number' => null, 'floor' => null, 'is_active' => false])->assertRedirect()->assertSessionHasNoErrors();
        (require database_path('migrations/2026_10_19_010000_add_room_number_to_office_table.php'))->down();
        $this->assertFalse(Schema::hasColumn('office', 'room_number'));
        $this->assertTrue(Schema::hasColumn('office', 'floor'));
        $this->assertDatabaseCount('office', 3);
    }

    public function test_page_keeps_directory_and_account_forms_distinct_and_accepts_unchanged_inactive_assignments(): void
    {
        DB::table('office_staff')->where('user_id', 2)->update(['office_id' => 3]);
        $this->put('/admin/user/offices/2', ['first_name' => 'Health', 'last_name' => 'Staff', 'office_id' => 3, 'position' => 'Staff'])->assertRedirect()->assertSessionHasNoErrors();
        $page = $this->get('/admin/user/offices')->assertOk();
        $dom = new \DOMDocument;
        @$dom->loadHTML($page->getContent());
        $xpath = new \DOMXPath($dom);
        foreach (['officeDirectoryModal', 'officeDirectoryForm', 'addOfficeModal', 'addOfficeForm', 'openAddOfficeBtn', 'directoryAddOffice'] as $id) {
            $this->assertSame(1, $xpath->query('//*[@id="'.$id.'"]')->length, $id);
        }
        $this->assertSame(1, $xpath->query('//*[@id="officeDirectory"]//table')->length);
        $this->assertSame(1, $xpath->query('//*[@id="officeAccounts"]//table')->length);
        if (getenv('NU_SECURE_OFFICE_PREVIEW') === '1') {
            file_put_contents(storage_path('app/office-management-preview.html'), $page->getContent());
        }
    }
}
