@forelse($rows as $row)
    @php
        $name = trim((string) data_get($row, 'visitor_name', 'Visitor')) ?: 'Visitor';
        $initials = collect(preg_split('/\s+/', $name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
        $photo = data_get($row, 'photo_url');
        $status = $kind === 'ready' ? 'Ready' : ($kind === 'scans' ? data_get($row, 'validation_status', '—') : data_get($row, 'route_status', 'Expected'));
        $tone = $kind === 'ready' ? 'ready' : ($kind === 'scans' ? $status : data_get($row, 'badge', 'info'));
    @endphp
    <article class="dashboard-record">
        <div class="dashboard-record-main">
            @if($photo)
                <img src="{{ $photo }}" alt="{{ $name }}" class="dashboard-visitor-photo" loading="lazy">
                <span class="dashboard-avatar d-none" aria-hidden="true">{{ $initials }}</span>
            @else
                <span class="dashboard-avatar" aria-hidden="true">{{ $initials }}</span>
            @endif
            <div class="dashboard-record-copy">
                <div class="dashboard-record-name">{{ $name }}</div>
                <div class="dashboard-record-meta">
                    {{ data_get($row, 'control_number') ?: '—' }}
                    @if($kind === 'ready') · From {{ data_get($row, 'previous_office') ?: 'Main Lobby' }} @endif
                    @if($kind === 'scans') · {{ data_get($row, 'scan_time_label') ?: '—' }} @endif
                    @if($kind === 'expected') · {{ \Illuminate\Support\Str::limit(data_get($row, 'purpose_reason') ?: '—', 35) }} @endif
                </div>
            </div>
        </div>
        <div class="dashboard-record-actions">
            @include('office.components.status-badge', ['tone' => $tone, 'label' => $status])
            @if($kind === 'ready' || ($kind === 'expected' && data_get($row, 'route_status_key') === 'ready'))
                <a href="{{ route('office.scanner', ['visit' => data_get($row, 'visit_id')]) }}" class="btn btn-sm btn-nu-primary">Scan</a>
            @endif
            @if($kind !== 'ready')<a href="{{ route('office.visitors.show', data_get($row, 'visit_id')) }}" class="record-detail-link" aria-label="View {{ $name }}">View</a>@endif
        </div>
    </article>
@empty
    <div class="empty-state dashboard-empty">
        <i class="bi {{ $kind === 'scans' ? 'bi-qr-code-scan' : 'bi-people' }}" aria-hidden="true"></i>
        <p class="mb-1">{{ $kind === 'scans' ? 'No scans yet today.' : ($kind === 'ready' ? 'No visitors waiting to be scanned.' : 'No visitors are currently expected at your office.') }}</p>
        @if($kind === 'scans')<p class="card-muted mb-0">QR scans made at this office will appear here.</p>@endif
    </div>
@endforelse
