@php $activeNav = 'history'; @endphp
@extends('office.layouts.office-layout')

@section('title', 'Visit History')

@section('content')
<div class="office-card mb-3">
	<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
		<div>
			<h2>Visit History</h2>
			<p class="card-muted mb-0">Office scans recorded for {{ $office->office_name }}</p>
		</div>
		<a href="{{ route('office.visit-history.export', request()->query()) }}" class="btn btn-nu-outline btn-sm">
			<i class="bi bi-download me-1" aria-hidden="true"></i>Export CSV
		</a>
	</div>

	<form method="GET" action="{{ route('office.visit-history') }}" class="row g-2 align-items-end">
		<div class="col-md-3">
			<label for="search" class="form-label fw-semibold">Search</label>
			<input type="search" id="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Visitor, control no., purpose">
		</div>
		<div class="col-md-2">
			<label for="from" class="form-label fw-semibold">From</label>
			<input type="date" id="from" name="from" value="{{ $filters['from'] }}" class="form-control">
		</div>
		<div class="col-md-2">
			<label for="to" class="form-label fw-semibold">To</label>
			<input type="date" id="to" name="to" value="{{ $filters['to'] }}" class="form-control">
		</div>
		<div class="col-md-2">
			<label for="status" class="form-label fw-semibold">Status</label>
			<select id="status" name="status" class="form-select">
				<option value="">All</option>
				<option value="Valid" @selected($filters['status'] === 'Valid')>Valid</option>
				<option value="Invalid" @selected($filters['status'] === 'Invalid')>Invalid</option>
				<option value="Unauthorized" @selected($filters['status'] === 'Unauthorized')>Unauthorized</option>
			</select>
		</div>
		<div class="col-md-2">
			<label for="staff" class="form-label fw-semibold">Staff</label>
			<select id="staff" name="staff" class="form-select">
				<option value="">All staff</option>
				@foreach($staffOptions as $staff)
					<option value="{{ $staff->user_id }}" @selected((string) $filters['staff'] === (string) $staff->user_id)>{{ $staff->full_name }}</option>
				@endforeach
			</select>
		</div>
		<div class="col-md-1 d-grid">
			<button type="submit" class="btn btn-nu-primary">Filter</button>
		</div>
	</form>
</div>

<div class="office-card">
	@if($history->isEmpty())
		<div class="empty-state">
			<i class="bi bi-clock-history" aria-hidden="true"></i>
			<p class="mb-0">No visit history found for the selected filters.</p>
		</div>
	@else
		<div class="table-scroll">
			<table class="table-office">
				<thead>
					<tr>
						<th>Date / Time</th>
						<th>Control No.</th>
						<th>Visitor</th>
						<th>Purpose</th>
						<th>Office</th>
						<th>Staff</th>
						<th>Status</th>
						<th>Remarks</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach($history as $row)
						<tr>
							<td>{{ $row->scan_time ? \Carbon\Carbon::parse($row->scan_time)->timezone('Asia/Manila')->format('M j, Y g:i A') : '—' }}</td>
							<td>{{ $row->control_number ?: '—' }}</td>
							<td>{{ $row->visitor_name }}</td>
							<td>{{ \Illuminate\Support\Str::limit($row->purpose_reason ?: '—', 36) }}</td>
							<td>{{ $row->office_name }}</td>
							<td>{{ $row->staff_name ?: '—' }}</td>
							<td>
								@include('office.components.status-badge', [
									'tone' => strtolower((string) $row->validation_status),
									'label' => $row->validation_status ?: '—',
								])
							</td>
							<td>{{ \Illuminate\Support\Str::limit($row->remarks ?: '—', 40) }}</td>
							<td><a href="{{ route('office.visitors.show', $row->visit_id) }}" class="btn btn-sm btn-nu-outline">View</a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		@include('admin.partials.table-pagination-styles')
		@include('admin.partials.table-pagination', [
			'paginator' => $history,
			'perPageParam' => 'per_page',
			'ariaLabel' => 'Visit history pagination',
		])
	@endif
</div>
@endsection

@push('scripts')
@include('admin.partials.table-pagination-script')
<script>
(function () {
	const input = document.getElementById('search');
	if (!input) return;
	let timer = null;
	input.addEventListener('input', () => {
		clearTimeout(timer);
		timer = setTimeout(() => input.form?.requestSubmit(), 600);
	});
})();
</script>
@endpush
