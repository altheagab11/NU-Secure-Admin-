<?php

namespace Tests\Feature;

use App\Services\OfficeScanService;
use App\Services\OfficeVisitorQueryService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class OfficeDashboardLoadingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'dashboard_loading', 'database.connections.dashboard_loading' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        foreach ([
            'CREATE TABLE visitor (visitor_id INTEGER, first_name TEXT, last_name TEXT, visitor_photo_with_id_url TEXT)',
            'CREATE TABLE visit_type (visit_type_id INTEGER, visit_type_name TEXT)',
            'CREATE TABLE visit (visit_id INTEGER, visitor_id INTEGER, visit_type_id INTEGER, control_number TEXT, pass_number TEXT, purpose_reason TEXT, entry_time TEXT, exit_time TEXT, destination_text TEXT)',
            'CREATE TABLE office (office_id INTEGER, office_name TEXT)',
            'CREATE TABLE expectation_status (expectation_status_id INTEGER, status_name TEXT)',
            'CREATE TABLE office_expectation (expectation_id INTEGER, visit_id INTEGER, office_id INTEGER, expected_order INTEGER, expectation_status_id INTEGER, arrived_at TEXT, created_at TEXT)',
            'CREATE TABLE validation_status (validation_status_id INTEGER, status_name TEXT)',
            'CREATE TABLE office_scan (office_id INTEGER, validation_status_id INTEGER, scan_time TEXT, remarks TEXT)',
        ] as $sql) {
            DB::statement($sql);
        }
        DB::table('office')->insert([
            ['office_id' => 7, 'office_name' => 'Admissions'],
            ['office_id' => 8, 'office_name' => 'Registrar'],
        ]);
        DB::table('visit_type')->insert(['visit_type_id' => 1, 'visit_type_name' => 'Enrollee']);
        foreach (range(1, 13) as $id) {
            DB::table('visitor')->insert(['visitor_id' => $id, 'first_name' => 'Visitor', 'last_name' => (string) $id, 'visitor_photo_with_id_url' => 'photo-'.$id]);
            DB::table('visit')->insert([
                'visit_id' => $id, 'visitor_id' => $id, 'visit_type_id' => 1,
                'control_number' => 'C-'.$id, 'purpose_reason' => 'Enrollment', 'entry_time' => '2026-10-09 08:00:00',
            ]);
            DB::table('office_expectation')->insert([
                'expectation_id' => $id * 2, 'visit_id' => $id, 'office_id' => 8, 'expected_order' => 1,
                'arrived_at' => $id === 3 ? null : '2026-10-09 08:10:00',
            ]);
            if ($id !== 13) {
                DB::table('office_expectation')->insert([
                    'expectation_id' => $id * 2 + 1, 'visit_id' => $id, 'office_id' => 7, 'expected_order' => 2,
                ]);
            }
        }
    }

    protected function tearDown(): void
    {
        DB::purge('dashboard_loading');
        parent::tearDown();
    }

    public function test_ready_loading_defers_photos_until_pagination_without_changing_eligibility(): void
    {
        $scanner = Mockery::mock(OfficeScanService::class)->makePartial();
        $scanner->shouldReceive('resolveVisitorPhotoUrl')->once()->with('photo-1')->andReturn('https://example.test/1.jpg');
        $scanner->shouldReceive('resolveVisitorPhotoUrl')->once()->with('photo-2')->andReturn('https://example.test/2.jpg');
        $service = new OfficeVisitorQueryService($scanner);
        $rows = $service->liveMonitoring(7, false)['waiting'];
        $this->assertCount(11, $rows);
        $this->assertNotContains(3, array_column($rows, 'visit_id'));
        $this->assertNotContains(13, array_column($rows, 'visit_id'));
        $this->assertSame('Registrar', $rows[0]['previous_office']);
        $visible = $service->resolveWaitingPhotos(array_slice($rows, 0, 2));
        $this->assertSame('https://example.test/1.jpg', $visible[0]['photo_url']);
        $this->assertArrayNotHasKey('photo_path', $visible[0]);
    }

    public function test_expected_loading_batches_routes_and_preserves_waiting_status(): void
    {
        $service = new OfficeVisitorQueryService(new OfficeScanService);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $rows = $service->expectedVisitorsPreview(7, null);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertCount(12, $rows);
        $this->assertCount(2, $queries);
        $this->assertSame('waiting', $rows->firstWhere('visit_id', 3)->route_status_key);
        $this->assertSame('ready', $rows->firstWhere('visit_id', 1)->route_status_key);
        $this->assertSame('Registrar', $rows->firstWhere('visit_id', 1)->previous_office);
    }
}
