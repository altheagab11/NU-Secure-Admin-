<div class="alerts-summary-grid">
    <div class="alerts-summary-card is-alert"><div class="summary-icon soft-red"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i></div><div class="summary-copy"><span class="summary-label">All Alerts (Unresolved)</span><h2 class="summary-number">{{ number_format($unresolvedAlertsCount ?? 0) }}</h2><p class="summary-text">Requires guard attention</p></div>@if(($unresolvedAlertsCount ?? 0) > 0)<span class="alert-status-badge unresolved">Action Needed</span>@endif</div>
    <div class="alerts-summary-card is-completed"><div class="summary-icon soft-green"><i class="fas fa-circle-check" aria-hidden="true"></i></div><div class="summary-copy"><span class="summary-label">Completed Visitors</span><h2 class="summary-number">{{ number_format($readyToExitCount ?? 0) }}</h2><p class="summary-text">Ready for exit processing</p></div>@if(($readyToExitCount ?? 0) > 0)<span class="alert-status-badge ready">Ready</span>@endif</div>
</div>
@foreach(['completed' => ['Completed Visitors', 'Visitors who have completed their business and are ready to exit.', 'soft-green-lite', 'check-circle'], 'alerts' => ['All Alerts (Unresolved)', 'Shows all unresolved alerts across all alert types.', 'soft-red', 'triangle-exclamation']] as $kind => $section)
    <section class="alerts-panel-card" aria-labelledby="{{ $kind }}SectionTitle">
        <div class="alerts-panel-header">
            <div class="alerts-panel-title-wrap"><div class="section-icon {{ $section[2] }}"><i class="fas fa-{{ $section[3] }}" aria-hidden="true"></i></div><div><h3 id="{{ $kind }}SectionTitle">{{ $section[0] }}</h3><p>{{ $section[1] }}</p></div></div>
            <form class="alerts-panel-actions" data-preview-filters="{{ $kind }}">
                @include('guard.partials.active-alert-filters', ['kind' => $kind])
            </form>
        </div>
        <div class="alerts-list" id="{{ $kind }}Preview" aria-live="polite">@include('guard.partials.active-alert-records', ['kind' => $kind, 'rows' => $kind === 'completed' ? collect($completedVisitors)->take(5) : collect($unresolvedAlerts)->take(5)])</div>
        <div id="{{ $kind }}Pagination" @if(($kind === 'completed' ? $readyToExitCount : $unresolvedAlertsCount) === 0) hidden @endif>
            @include('admin.partials.table-pagination', ['paginator' => $kind === 'completed' ? $completedPaginator : $alertPaginator, 'perPageParam' => $kind.'_per_page', 'ariaLabel' => $section[0].' pagination'])
        </div>
    </section>
@endforeach
