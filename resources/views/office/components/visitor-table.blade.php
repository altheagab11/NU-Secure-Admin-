@php
	$rows = $rows ?? collect();
	$emptyMessage = $emptyMessage ?? 'No visitors are currently expected at your office.';
	$showActions = $showActions ?? true;
@endphp

@if($rows->isEmpty())
	<div class="empty-state">
		<i class="bi bi-inbox" aria-hidden="true"></i>
		<p class="mb-0">{{ $emptyMessage }}</p>
	</div>
@else
	<div class="table-scroll">
		<table class="table-office">
			<thead>
				<tr>
					<th>Control No.</th>
					<th>Visitor</th>
					<th>Purpose</th>
					<th>Previous Office</th>
					<th>Expected</th>
					<th>Route Status</th>
					@if($showActions)<th>Action</th>@endif
				</tr>
			</thead>
			<tbody>
				@foreach($rows as $row)
					<tr>
						<td>{{ $row->control_number ?: '—' }}</td>
						<td>{{ $row->visitor_name }}</td>
						<td>{{ \Illuminate\Support\Str::limit($row->purpose_reason ?: '—', 40) }}</td>
						<td>{{ $row->previous_office ?? '—' }}</td>
						<td>
							@if(!empty($row->expected_arrival))
								{{ \Carbon\Carbon::parse($row->expected_arrival)->timezone('Asia/Manila')->format('M j, g:i A') }}
							@else
								—
							@endif
						</td>
						<td>
							@include('office.components.status-badge', [
								'tone' => $row->badge ?? 'info',
								'label' => $row->route_status ?? 'Expected',
							])
						</td>
						@if($showActions)
							<td class="text-nowrap">
								<a href="{{ route('office.visitors.show', $row->visit_id) }}" class="btn btn-sm btn-nu-outline">View</a>
								@if(($row->route_status_key ?? '') === 'ready')
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
