@php $activeNav = 'dashboard'; @endphp
@extends('office.layouts.office-layout')

@section('title', 'Dashboard')

@section('dashboardHeader')
<div class="d-flex align-items-center flex-wrap gap-2">
    <h1 class="page-heading">{{ $office->office_name }}</h1>
    @include('office.components.status-badge', ['tone' => $officeStatus === 'Open' ? 'success' : 'muted', 'label' => $officeStatus])
</div>
<p class="page-sub">{{ $staffName }} · {{ $staffRole }}</p>
<p class="dashboard-date">Dashboard · {{ $currentDate }}</p>
@endsection

@section('content')
<div class="office-dashboard">
    <div class="dashboard-stats">
        @foreach([
            ['Pending Scans', 'pending_office_scans', 'hourglass-split', 'amber'],
            ['Expected Visitors', 'expected_visitors', 'person-fill', 'blue'],
            ["Today's Scans", 'todays_visitors', 'check-circle-fill', 'green'],
        ] as [$label, $key, $icon, $tone])
            <div class="office-card dashboard-stat">
                <span class="dashboard-icon tone-{{ $tone }}" aria-hidden="true"><i class="bi bi-{{ $icon }}"></i></span>
                <div><div class="stat-label">{{ $label }}</div><div class="stat-value" data-stat="{{ $key }}">{{ number_format((int) $stats[$key]) }}</div></div>
            </div>
        @endforeach
        <div class="office-card dashboard-scanner-action">
            <a href="{{ route('office.scanner') }}" class="btn btn-nu-primary"><i class="bi bi-qr-code-scan" aria-hidden="true"></i> Open Scanner</a>
        </div>
    </div>

    <div class="dashboard-columns">
        <div class="dashboard-column">
            <section class="office-card quick-scan-card" aria-labelledby="quickScanHeading">
            <h2 id="quickScanHeading"><i class="bi bi-qr-code-scan me-2 text-primary" aria-hidden="true"></i>Quick Scan</h2>
            <p class="card-muted">Scan the visitor QR when their visit at this office is done.</p>
            <div class="scan-promo-panel">
                <i class="bi bi-qr-code scan-promo-icon" aria-hidden="true"></i>
                <strong>Ready to Scan</strong>
            </div>
            <div class="scanner-buttons">
                <a href="{{ route('office.scanner') }}" class="btn btn-nu-primary"><i class="bi bi-camera-video me-2" aria-hidden="true"></i>Start QR Scanner</a>
                <button type="button" class="btn btn-nu-outline" data-open-manual-scan><i class="bi bi-keyboard me-2" aria-hidden="true"></i>Enter QR Manually</button>
            </div>
        </section>
            <section class="office-card" aria-labelledby="recentHeading">
            <div class="dashboard-card-heading"><div><h2 id="recentHeading">Today's Recent Scans</h2><p class="card-muted mb-0">Latest QR scans today at this office</p></div><button type="button" class="btn btn-sm btn-nu-outline" data-dashboard-list="scans">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
            <div id="recentActivityWrap" class="dashboard-records">
                @include('office.partials.dashboard-records', ['kind' => 'scans', 'rows' => collect($recentActivity->items())->take(5)])
            </div>
        </section>
        </div>
        <div class="dashboard-column">
            <section class="office-card" aria-labelledby="readyHeading">
            <div class="dashboard-card-heading">
                <div><h2 id="readyHeading">Ready to Scan</h2><p class="card-muted mb-0">Visitors whose next stop is {{ $office->office_name }}</p></div>
                <div class="dashboard-heading-actions"><span class="badge-status badge-success" id="livePulse">Live</span><button type="button" class="btn btn-sm btn-nu-outline" data-dashboard-list="ready">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
            </div>
            <div id="liveWaiting" class="dashboard-records">
                @include('office.partials.dashboard-records', ['kind' => 'ready', 'rows' => collect($liveWaiting->items())->take(5)])
            </div>
            @php
                $latestScanLabel = !empty($live['latest_scan']) ? 'Latest scan: '.($live['latest_scan']->status_name ?? '—') : '';
                if (!empty($live['latest_scan']->scan_time)) {
                    $latestScanLabel .= ' · '.\Carbon\Carbon::parse($live['latest_scan']->scan_time)->timezone('Asia/Manila')->format('g:i A');
                }
            @endphp
            <p class="latest-scan-note" id="latestScanStatus">{{ $latestScanLabel }}</p>
        </section>
            <section class="office-card" aria-labelledby="expectedHeading">
            <div class="dashboard-card-heading"><div><h2 id="expectedHeading">Expected Visitors</h2><p class="card-muted mb-0">Coming to your office</p></div><a href="{{ route('office.expected-visitors') }}" class="btn btn-sm btn-nu-outline">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
            <div id="expectedVisitorsWrap" class="dashboard-records">
                @include('office.partials.dashboard-records', ['kind' => 'expected', 'rows' => collect($expectedPreview->items())->take(5)])
            </div>
        </section>
        </div>
    </div>
</div>

@include('office.partials.dashboard-list-modal')
@include('office.components.scan-result-modal')
@endsection

@push('styles')
<style nonce="{{ $cspNonce }}">
    @include('admin.partials.table-pagination-styles')
    @include('office.partials.dashboard-styles')
</style>
@endpush

@push('scripts')
@include('office.partials.scan-scripts')
<script nonce="{{ $cspNonce }}">
OfficeScan.init({ onSuccess: function () { setTimeout(() => window.location.reload(), 700); } });
(function () {
    const liveUrl = @json(route('office.dashboard.live'));
    const scannerUrl = @json(route('office.scanner'));
    const visitorUrl = @json(url('/office/visitors'));
    @include('office.partials.dashboard-scripts')
})();
</script>
@endpush
