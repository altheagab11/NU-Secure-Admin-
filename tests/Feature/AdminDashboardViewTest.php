<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class AdminDashboardViewTest extends TestCase
{
    private function renderDashboard(array $overrides = []): string
    {
        $alerts = Mockery::mock();
        $alerts->shouldReceive('whereRaw')->andReturnSelf();
        $alerts->shouldReceive('count')->andReturn(3);
        DB::shouldReceive('table')->with('alerts')->andReturn($alerts);

        return view('admin.dashboard', array_merge([
            'cspNonce' => 'dashboard-test-nonce',
            'totalVisitorsToday' => 12,
            'currentlyInside' => 4,
            'activeOffices' => 2,
            'averageDuration' => '18m',
            'criticalAlerts' => 1,
            'highAlerts' => 2,
            'mediumAlerts' => 3,
            'lowAlerts' => 0,
            'totalAlertsToday' => 6,
            'unresolvedAlerts' => 3,
            'mostCommonAlert' => 'Incomplete Route',
            'successfulLoginsToday' => 17,
            'failedLoginsToday' => 2,
            'blockedLoginsToday' => 1,
            'liveVisitors' => collect([['name' => 'Test Visitor', 'status' => 'Inside', 'location' => 'Registrar', 'time_in' => '09:00 AM']]),
            'recentAlerts' => collect([['alert_id' => 42, 'time' => '09:10 AM', 'visitor' => 'Test Visitor', 'type' => 'Incomplete Route', 'severity' => 'Medium', 'status' => 'Unresolved']]),
            'peakVisitorHourValue' => '9:00 AM – 10:00 AM',
            'topOfficeTodayValue' => 'Registrar',
            'longestAvgDurationValue' => 'Registrar · 18m',
            'peakVisitorHourInsight' => 'Peak visitor hour is 9:00 AM to 10:00 AM.',
            'topOfficeTodayInsight' => 'Registrar receives the most visitors today.',
            'unresolvedAlertsInsight' => '3 unresolved alerts need immediate attention.',
            'longestAvgDurationInsight' => 'Registrar has the longest average visit duration (18m).',
        ], $overrides))->render();
    }

    public function test_dashboard_renders_data_and_preserves_filter_and_detail_navigation(): void
    {
        $html = $this->renderDashboard(['selectedDateFilter' => 'week', 'selectedStatusFilter' => 'Inside', 'statusOptions' => ['Inside', 'Exited']]);
        $this->assertStringContainsString('value="week" selected', $html);
        $this->assertStringContainsString('value="Inside" selected', $html);
        $this->assertStringContainsString('Filters applied', $html);
        $this->assertStringContainsString('action="/admin/dashboard"', $html);
        $this->assertStringContainsString('href="/admin/dashboard"', $html);
        $this->assertStringContainsString('href="/admin/visitor"', $html);
        $this->assertStringContainsString('alerts?alert_id=42', $html);
        $this->assertStringContainsString('value="3" max="6"', $html);
        $this->assertStringContainsString('50% of matching alerts', $html);
        $this->assertStringContainsString('>17</h2>', $html);
        $this->assertStringNotContainsString('from yesterday', $html);
        $this->assertLessThan(strpos($html, 'Alerts Summary'), strpos($html, 'dashboard-filter-bar mb-3'));

        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(0, $xpath->query('//main//*[contains(@class,"table-pagination-bar")]')->length);
        $this->assertSame(4, $xpath->query('//article[contains(@class,"insight-tile")]')->length);
        $this->assertSame(4, $xpath->query('//canvas')->length);
        $this->assertSame(2, $xpath->query('//main//table')->length);
        $this->assertSame(1, $xpath->query('//main//section[@aria-label="Dashboard filters"]')->length);
        foreach (['visitorTrendChart', 'visitorStatusChart', 'visitorHourChart', 'visitorOfficeChart'] as $id) {
            $this->assertSame(1, $xpath->query('//main//canvas[@id="'.$id.'"]')->length);
        }
    }

    public function test_empty_data_renders_without_fabricated_counts_or_division_by_zero(): void
    {
        $html = $this->renderDashboard([
            'criticalAlerts' => 0, 'highAlerts' => 0, 'mediumAlerts' => 0, 'totalAlertsToday' => 0,
            'liveVisitors' => collect(), 'recentAlerts' => collect(),
        ]);
        $this->assertStringContainsString('value="0" max="1"', $html);
        $this->assertStringContainsString('0% of matching alerts', $html);
        $this->assertStringContainsString('No visitors match the current filters.', $html);
        $this->assertStringContainsString('No recent alerts found.', $html);
        $this->assertSame(4, substr_count($html, 'No visitor data for this chart.'));
        $this->assertStringNotContainsString('Filters applied', $html);
    }
}
