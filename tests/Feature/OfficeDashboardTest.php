<?php

namespace Tests\Feature;

use App\Http\Controllers\Office\OfficeDashboardController;
use App\Services\OfficeVisitorQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class OfficeDashboardTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function request(array $params = []): Request
    {
        $request = Request::create('/office/dashboard/live', 'GET', $params);
        $request->attributes->set('office_context', (object) [
            'office_id' => 7, 'user_id' => 12, 'office_name' => 'Admissions Office',
            'office_is_active' => true, 'first_name' => 'Admissions', 'last_name' => 'Staff', 'position' => 'Staff',
        ]);

        return $request;
    }

    private function expectedRows()
    {
        return collect(range(1, 12))->map(fn ($id) => (object) [
            'visit_id' => $id, 'visitor_name' => 'Visitor '.$id, 'control_number' => 'CTRL-'.$id,
            'entry_time' => sprintf('2026-10-09 %02d:00:00', $id),
            'expected_arrival' => sprintf('2026-10-09 %02d:00:00', $id),
            'purpose_reason' => 'A long purpose that should remain complete in the full record modal',
            'previous_office' => 'Registrar', 'route_status_key' => $id % 2 ? 'waiting' : 'ready',
            'route_status' => $id % 2 ? 'Waiting' : 'Ready to scan', 'badge' => $id % 2 ? 'warning' : 'info',
        ]);
    }

    public function test_previews_ignore_old_pagination_and_render_five_records_with_scanner_controls(): void
    {
        $service = Mockery::mock(OfficeVisitorQueryService::class);
        $service->shouldReceive('dashboardStats')->with(7)->andReturn(['pending_office_scans' => 12, 'expected_visitors' => 12, 'todays_visitors' => 0]);
        $service->shouldReceive('recentActivity')->with(7, 5, true)->andReturn(collect());
        $service->shouldReceive('expectedVisitorsPreview')->with(7, null)->andReturn($this->expectedRows());
        $service->shouldReceive('liveMonitoring')->with(7)->andReturn(['waiting' => collect(range(1, 12))->map(fn ($id) => ['visit_id' => $id, 'visitor_name' => 'Eligible '.$id, 'control_number' => 'C-'.$id, 'previous_office' => 'Main Lobby', 'previous_arrived_at' => sprintf('2026-10-09 %02d:00:00', $id)])->all()]);
        $service->shouldReceive('unreadNotifications')->with(12, 10)->andReturn(collect());
        $view = (new OfficeDashboardController($service))->index($this->request(['ready_page' => 2, 'ready_per_page' => 100, 'expected_page' => 2]));
        $data = $view->getData();
        $this->assertCount(5, $data['liveWaiting']->items());
        $this->assertSame(12, $data['liveWaiting']->items()[0]['visit_id']);
        $this->assertSame(12, $data['expectedPreview']->items()[0]->visit_id);
        $view->with('cspNonce', 'office-dashboard-test');
        $html = $view->render();
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(5, $xpath->query('//*[@id="liveWaiting"]/article')->length);
        $this->assertSame(5, $xpath->query('//*[@id="expectedVisitorsWrap"]/article')->length);
        $this->assertSame(0, $xpath->query('//*[contains(@class,"office-dashboard")]//*[contains(@class,"table-pagination-bar")]')->length);
        $this->assertSame(2, $xpath->query('//button[@data-dashboard-list]')->length);
        $this->assertSame(route('office.expected-visitors'), $xpath->query('//section[@aria-labelledby="expectedHeading"]//a[contains(@class, "btn")]')->item(0)->getAttribute('href'));
        $this->assertSame(1, $xpath->query('//button[@data-open-manual-scan]')->length);
        $this->assertStringContainsString('No scans yet today.', $html);
        $this->assertStringContainsString('QR scans made at this office will appear here.', $html);
        $this->assertStringContainsString('office/scanner?visit=12', $html);
    }

    public function test_ready_modal_uses_only_eligible_rows_and_searches_before_paginating(): void
    {
        $service = Mockery::mock(OfficeVisitorQueryService::class);
        $service->shouldReceive('resolveWaitingPhotos')->once()->with(Mockery::on(fn ($rows) => array_column($rows, 'visit_id') === [3, 1]))->andReturnUsing(fn ($rows) => $rows);
        $service->shouldReceive('liveMonitoring')->once()->with(7, false)->andReturn(['waiting' => collect(range(1, 13))->map(fn ($id) => [
            'visit_id' => $id, 'visitor_name' => 'Eligible '.$id, 'control_number' => 'C-'.$id,
            'purpose' => 'Meeting', 'previous_office' => $id % 2 ? 'Main Lobby' : 'Registrar',
            'previous_arrived_at' => sprintf('2026-10-09 %02d:00:00', $id), 'status' => 'Ready for Office Check-in',
        ])->all()]);
        $response = (new OfficeDashboardController($service))->liveData($this->request([
            'dashboard_list' => 'ready', 'search' => 'Eligible', 'previous_office' => 'Main Lobby', 'per_page' => 5, 'page' => 2, 'office_id' => 99,
        ]))->getData(true);
        $this->assertSame(7, $response['meta']['total']);
        $this->assertCount(2, $response['data']);
        $this->assertSame([3, 1], array_column($response['data'], 'visit_id'));
        $this->assertSame(['Ready for Office Check-in'], $response['filters']['statuses']);
    }

    public function test_expected_modal_retains_full_purpose_and_limits_scan_actions_to_ready(): void
    {
        $service = Mockery::mock(OfficeVisitorQueryService::class);
        $service->shouldReceive('expectedVisitorsPreview')->once()->with(7, null)->andReturn($this->expectedRows());
        $payload = (new OfficeDashboardController($service))->liveData($this->request(['dashboard_list' => 'expected', 'status' => 'waiting', 'per_page' => 5]))->getData(true);
        $this->assertSame(6, $payload['meta']['total']);
        $this->assertCount(5, $payload['data']);
        $this->assertSame('A long purpose that should remain complete in the full record modal', $payload['data'][0]['purpose']);
        $this->assertNull($payload['data'][0]['scan_url']);
        $this->assertSame('Registrar', $payload['data'][0]['previous_office']);
        $this->assertNotSame('—', $payload['data'][0]['expected_label']);
    }

    public function test_scans_modal_uses_full_history_and_forces_today_instead_of_client_dates(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', 'Asia/Manila'));
        $service = Mockery::mock(OfficeVisitorQueryService::class);
        $service->shouldReceive('visitHistoryPaginated')->once()->with(Mockery::on(fn ($request) => $request->query('from') === '2026-10-09' && $request->query('to') === '2026-10-09' && $request->query('search') === 'Visitor'), 7, 10)
            ->andReturn(new LengthAwarePaginator(collect([(object) ['visit_id' => 42, 'visitor_name' => 'Visitor', 'control_number' => 'C-42', 'scan_time' => '2026-10-09T01:00:00Z', 'validation_status' => 'Valid', 'purpose_reason' => 'Meeting']]), 600, 10, 1));
        $payload = (new OfficeDashboardController($service))->liveData($this->request(['dashboard_list' => 'scans', 'search' => 'Visitor', 'from' => '1999-01-01', 'to' => '2099-01-01']))->getData(true);
        $this->assertSame(600, $payload['meta']['total']);
        $this->assertSame('9:00 AM', $payload['data'][0]['time_label']);
        $this->assertSame('Valid', $payload['data'][0]['validation_status']);
    }

    public function test_empty_ready_modal_has_no_records_and_requires_office_staff_middleware(): void
    {
        $service = Mockery::mock(OfficeVisitorQueryService::class);
        $service->shouldReceive('liveMonitoring')->with(7, false)->andReturn(['waiting' => []]);
        $service->shouldReceive('resolveWaitingPhotos')->once()->with([])->andReturn([]);
        $payload = (new OfficeDashboardController($service))->liveData($this->request(['dashboard_list' => 'ready']))->getData(true);
        $this->assertSame(0, $payload['meta']['total']);
        $this->assertSame([], $payload['data']);
        $route = Route::getRoutes()->getByName('office.dashboard.live');
        $this->assertContains('office.staff', $route->gatherMiddleware());
        $this->getJson(route('office.dashboard.live'))->assertUnauthorized();
    }

    public function test_existing_live_response_remains_compatible_with_independent_pagination(): void
    {
        $service = Mockery::mock(OfficeVisitorQueryService::class);
        $service->shouldReceive('recentActivity')->with(7, 500, true)->andReturn(collect());
        $service->shouldReceive('expectedVisitorsPreview')->with(7, null)->andReturn($this->expectedRows());
        $service->shouldReceive('liveMonitoring')->with(7)->andReturn(['waiting' => []]);
        $service->shouldReceive('dashboardStats')->with(7)->andReturn(['pending_office_scans' => 0, 'expected_visitors' => 12, 'todays_visitors' => 0]);
        $payload = (new OfficeDashboardController($service))->liveData($this->request(['expected_page' => 2, 'expected_per_page' => 5]))->getData(true);
        $this->assertTrue($payload['success']);
        $this->assertSame(12, $payload['stats']['expected_visitors']);
        $this->assertSame(2, $payload['expected_visitors']['meta']['current_page']);
        $this->assertSame(1, $payload['recent_activity']['meta']['current_page']);
        $this->assertCount(5, $payload['expected_visitors']['data']);
        $this->assertLessThan(strlen($this->expectedRows()[0]->purpose_reason), strlen($payload['expected_visitors']['data'][0]['purpose']));
    }
}
