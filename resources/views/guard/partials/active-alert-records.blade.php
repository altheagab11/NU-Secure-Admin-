@if($kind === 'completed')
    @if(collect($rows)->isEmpty())
        <div class="alerts-empty-card"><i class="fas fa-user-check" aria-hidden="true"></i><h3>No completed visitors found</h3><p>Visitors eligible for exit processing will appear here.</p></div>
    @else
        <div class="completed-table" role="table" aria-label="Completed visitors">
            <div class="completed-row completed-table-head" role="row">
                @foreach(['Visitor', 'Office / Destination', 'Control Number', 'Completed At', 'Status', 'Action'] as $label)<div role="columnheader">{{ $label }}</div>@endforeach
            </div>
            @foreach($rows as $visitor)
                <div class="completed-row" role="row">
                    <div class="completed-name" role="cell" data-label="Visitor">@if(!empty($visitor['photo_url']))<img src="{{ $visitor['photo_url'] }}" alt="{{ $visitor['visitor_name'] }}" class="guard-visitor-photo" loading="lazy"><span class="alert-avatar" hidden>{{ $visitor['initials'] ?? 'NA' }}</span>@else<span class="alert-avatar">{{ $visitor['initials'] ?? 'NA' }}</span>@endif<strong>{{ $visitor['visitor_name'] }}</strong></div>
                    <div role="cell" data-label="Office / Destination">{{ $visitor['office_name'] }}</div>
                    <div role="cell" data-label="Control Number">{{ $visitor['control_number'] ?: '—' }}</div>
                    <div role="cell" data-label="Completed At">{{ $visitor['completed_at'] }}</div>
                    <div role="cell" data-label="Status"><span class="alert-status-badge ready">Ready for Exit</span></div>
                    <div role="cell" data-label="Action"><a href="{{ url('/guard/exit') }}" class="alert-action-btn">Process Exit</a></div>
                </div>
            @endforeach
        </div>
    @endif
@else
    @forelse($rows as $alert)
        @php($severity = strtolower($alert['severity'] ?? 'high'))
        <article class="guard-alert-record severity-{{ $severity }}">
            <div class="guard-alert-top">
                <span class="alert-avatar">{{ collect(explode(' ', $alert['visitor_name']))->filter()->take(2)->map(fn($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                <div class="guard-alert-identity"><div class="guard-alert-type"><span class="severity-pill severity-{{ $severity }}">{{ $alert['severity'] }}</span><strong>{{ $alert['alert_type'] }}</strong></div><p>Visitor: {{ $alert['visitor_name'] }}</p><p>Pass No: {{ $alert['pass_number'] }}</p></div>
                <div class="guard-alert-time"><span class="alert-status-badge unresolved">Unresolved</span><time>{{ $alert['time'] }}</time></div>
            </div>
            <div class="guard-alert-details">
                <div><span>Expected Office</span><strong><i class="fas fa-building" aria-hidden="true"></i> {{ $alert['expected_office'] }}</strong></div>
                <div><span>Scanned Office</span><strong><i class="fas fa-building" aria-hidden="true"></i> {{ $alert['scanned_office'] }}</strong></div>
                <div><span>Message</span><p>{{ $alert['message'] }}</p></div>
                <button type="button" class="alert-action-btn view-btn" data-alert-id="{{ $alert['alert_id'] }}">View Details</button>
            </div>
        </article>
    @empty
        <div class="alerts-empty-card"><i class="fas fa-shield-alt" aria-hidden="true"></i><h3>No unresolved alerts found</h3><p>No alerts match the selected filters.</p></div>
    @endforelse
@endif
