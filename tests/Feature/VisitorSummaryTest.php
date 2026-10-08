<?php

namespace Tests\Feature;

use App\Http\Controllers\VisitorMonitoringController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VisitorSummaryTest extends TestCase
{
    private array $originalEnvironment = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['SUPABASE_URL', 'SUPABASE_SERVICE_ROLE_KEY'] as $key) {
            $this->originalEnvironment[$key] = getenv($key);
        }
        Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', 'Asia/Manila'));
        putenv('SUPABASE_URL=https://summary.test');
        putenv('SUPABASE_SERVICE_ROLE_KEY=test-key');
        Schema::shouldReceive('hasTable')->andReturn(false);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        foreach ($this->originalEnvironment as $key => $value) {
            putenv($value === false ? $key : $key.'='.$value);
        }
        parent::tearDown();
    }

    private function fakeRecords(int $count): void
    {
        $visits = $scans = [];
        for ($id = 1; $id <= $count; $id++) {
            $visitor = ['first_name' => 'Visitor', 'last_name' => (string) $id, 'visitor_photo_with_id_url' => 'visitor-photos/test.jpg'];
            $visits[] = [
                'visit_id' => $id, 'entry_time' => sprintf('2026-10-09T%02d:00:00+08:00', $id),
                'visitor' => $visitor, 'office' => ['office_name' => 'Registrar'],
                'control_number' => 'CTRL-'.$id, 'visit_type' => ['visit_type_name' => 'Personal'],
            ];
            $scans[] = [
                'scan_id' => $id, 'visit_id' => $id, 'office_id' => 1,
                'scan_time' => sprintf('2026-10-09T%02d:10:00+08:00', $id),
                'office' => ['office_name' => 'Registrar'], 'validation_status' => ['status_name' => 'Matched'],
                'visit' => ['control_number' => 'CTRL-'.$id, 'visitor' => $visitor],
            ];
        }
        Http::fake(fn ($request) => Http::response(match (parse_url($request->url(), PHP_URL_PATH)) {
            '/rest/v1/visit' => $visits,
            '/rest/v1/office_scan' => $scans,
            default => [],
        }));
    }

    public static function previewCounts(): array
    {
        return [[0], [3], [8]];
    }

    #[DataProvider('previewCounts')]
    public function test_preview_counts_are_independent_of_pagination(int $count): void
    {
        $this->fakeRecords($count);
        $view = (new VisitorMonitoringController)->index(Request::create('/admin/visitors', 'GET', [
            'recent_page' => 2, 'recent_per_page' => 100, 'scans_page' => 2, 'scans_per_page' => 100,
            'page' => 2, 'per_page' => 10,
        ]));
        $this->assertSame(2, $view->getData()['rows']->currentPage());
        $this->assertSame(10, $view->getData()['rows']->perPage());
        foreach (['recentVisitors', 'correctOfficeScans'] as $key) {
            $records = $view->getData()[$key];
            $this->assertCount(min(5, $count), $records);
            $this->assertSame($count, $records->total());
            $this->assertSame(1, $records->currentPage());
            if ($count) {
                $this->assertSame('Visitor '.$count, $records->first()['visitor_name']);
            }
        }
    }

    public function test_modals_paginate_search_and_filter_independently(): void
    {
        $this->fakeRecords(8);
        foreach (['recent', 'scans'] as $kind) {
            $controller = new VisitorMonitoringController;
            $request = Request::create('/admin/visitors', 'GET', ['summary' => $kind, 'summary_page' => 2, 'search' => 'unrelated main filter']);
            $html = $controller->index($request)->getData(true)['html'];
            $this->assertStringContainsString('6 to 8 of 8', $html);
            $this->assertStringContainsString('Visitor 3', $html);
            $this->assertStringNotContainsString('Visitor 8</td>', $html);
            $request = Request::create('/admin/visitors', 'GET', ['summary' => $kind, 'summary_search' => 'CTRL-8', 'summary_office' => 'Registrar']);
            $html = $controller->index($request)->getData(true)['html'];
            $this->assertStringContainsString('1 to 1 of 1', $html);
            $this->assertStringContainsString('Visitor 8', $html);
            $request = Request::create('/admin/visitors', 'GET', ['summary' => $kind, 'summary_per_page' => 10]);
            $this->assertStringContainsString('1 to 8 of 8', $controller->index($request)->getData(true)['html']);
        }
    }

    public function test_modal_empty_states_and_recent_visit_type_filter(): void
    {
        $this->fakeRecords(3);
        $controller = new VisitorMonitoringController;
        $request = Request::create('/admin/visitors', 'GET', [
            'summary' => 'recent', 'summary_visit_type' => 'Personal',
        ]);
        $this->assertStringContainsString('1 to 3 of 3', $controller->index($request)->getData(true)['html']);
        $request = Request::create('/admin/visitors', 'GET', [
            'summary' => 'recent', 'summary_visit_type' => 'Unknown type',
        ]);
        $this->assertStringContainsString('No visitors found for today.', $controller->index($request)->getData(true)['html']);
    }

    public function test_both_modals_show_empty_states(): void
    {
        $this->fakeRecords(0);
        $controller = new VisitorMonitoringController;
        foreach (['recent', 'scans'] as $kind) {
            $request = Request::create('/admin/visitors', 'GET', ['summary' => $kind]);
            $html = $controller->index($request)->getData(true)['html'];
            $this->assertStringContainsString('0 to 0 of 0', $html);
            $this->assertStringContainsString('found for today.', $html);
        }
    }

    public function test_recent_modal_skips_photo_signing_and_correct_scan_loading(): void
    {
        $this->fakeRecords(3);
        (new VisitorMonitoringController)->index(Request::create('/admin/visitors', 'GET', ['summary' => 'recent']));

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/storage/v1/'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'select=scan_id%2Cscan_time%2Cvisit_id%2Coffice_id%2Cremarks%2Cvalidation_status'));
    }

    public function test_scans_modal_skips_main_visitor_loading(): void
    {
        $this->fakeRecords(3);
        (new VisitorMonitoringController)->index(Request::create('/admin/visitors', 'GET', ['summary' => 'scans']));

        Http::assertNotSent(fn ($request) => parse_url($request->url(), PHP_URL_PATH) === '/rest/v1/visit');
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/storage/v1/'));
    }
}
