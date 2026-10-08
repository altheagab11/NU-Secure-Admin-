<div class="summary-modal-table">
    <table>
        <thead><tr>
            <th scope="col">Visitor</th><th scope="col">Control Number</th><th scope="col">{{ $kind === 'recent' ? 'Destination' : 'Office' }}</th>
            @if($kind === 'recent')<th scope="col">Visit Type</th>@endif
            <th scope="col">{{ $kind === 'recent' ? 'Time' : 'Scan Time' }}</th><th scope="col">Status</th>
        </tr></thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record['visitor_name'] }}</td><td>{{ $record['control_number'] }}</td><td>{{ $record['destination'] }}</td>
                    @if($kind === 'recent')<td>{{ $record['visit_type'] }}</td>@endif
                    <td>{{ $record['time_label'] }}</td>
                    <td><span class="{{ $kind === 'recent' ? 'status-pill '.$record['status_class'] : 'correct-pill' }}">{{ $kind === 'recent' ? $record['status'] : $record['result'] }}</span></td>
                </tr>
            @empty
                <tr><td colspan="{{ $kind === 'recent' ? 6 : 5 }}" class="empty-row">No {{ $kind === 'recent' ? 'visitors' : 'correct office scans' }} found for today.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.table-pagination', [
    'paginator' => $records,
    'perPageParam' => 'summary_per_page',
    'ariaLabel' => 'Summary records pagination',
])
