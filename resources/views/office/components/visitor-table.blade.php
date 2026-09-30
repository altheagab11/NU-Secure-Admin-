@php
	$rows = $rows ?? collect();
	$emptyMessage = $emptyMessage ?? 'No visitors are currently expected at your office.';
	$showActions = $showActions ?? true;
@endphp

@if($rows->isEmpty())
	<div class="empty-state">
		<i class="bi bi-people" aria-hidden="true"></i>
		<p class="mb-0">{{ $emptyMessage }}</p>
	</div>
@else
	<div class="table-scroll expected-table-wrap">
		<table class="table-office expected-table">
			<thead>
				<tr>
					<th>Visitor</th>
					<th>Control No.</th>
					<th>Purpose</th>
					<th>Previous Office</th>
					<th>Expected</th>
					<th>Status</th>
					@if($showActions)<th class="text-end">Action</th>@endif
				</tr>
			</thead>
			<tbody>
				@foreach($rows as $row)
					@php
						$name = trim((string) ($row->visitor_name ?? 'Visitor'));
						$initials = collect(preg_split('/\s+/', $name) ?: [])
							->filter()
							->take(2)
							->map(fn ($part) => strtoupper(substr($part, 0, 1)))
							->implode('');
						if ($initials === '') {
							$initials = 'V';
						}
						$statusKey = (string) ($row->route_status_key ?? '');
						$statusLabel = (string) ($row->route_status ?? 'Expected');
						if ($statusKey === 'ready') {
							$statusLabel = 'Ready to scan';
						} elseif ($statusKey === 'waiting') {
							$statusLabel = 'Waiting';
						} elseif ($statusKey === 'checked_in') {
							$statusLabel = 'Checked in';
						}
					@endphp
					<tr>
						<td>
							<div class="expected-visitor-cell">
								<span class="expected-avatar" aria-hidden="true">{{ $initials }}</span>
								<span class="expected-visitor-name">{{ $name }}</span>
							</div>
						</td>
						<td><code class="expected-control">{{ $row->control_number ?: '—' }}</code></td>
						<td>
							<span class="expected-purpose" title="{{ $row->purpose_reason ?: '' }}">
								{{ \Illuminate\Support\Str::limit(trim((string) ($row->purpose_reason ?? '')) !== '' ? $row->purpose_reason : '—', 32) }}
							</span>
						</td>
						<td>{{ $row->previous_office ?? '—' }}</td>
						<td class="text-nowrap">
							@if(!empty($row->expected_arrival))
								{{ \Carbon\Carbon::parse($row->expected_arrival)->timezone('Asia/Manila')->format('M j, g:i A') }}
							@else
								—
							@endif
						</td>
						<td>
							@include('office.components.status-badge', [
								'tone' => $row->badge ?? 'info',
								'label' => $statusLabel,
							])
						</td>
						@if($showActions)
							<td class="text-end text-nowrap">
								<a href="{{ route('office.visitors.show', $row->visit_id) }}" class="btn btn-sm btn-nu-outline">View</a>
								@if($statusKey === 'ready')
									<a href="{{ route('office.scanner') }}?visit={{ $row->visit_id }}" class="btn btn-sm btn-nu-primary">Scan</a>
								@endif
							</td>
						@endif
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endif
