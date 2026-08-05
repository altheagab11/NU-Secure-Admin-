@php $activeNav = 'expected'; @endphp
@extends('office.layouts.office-layout')

@section('title', 'Expected Visitors')

@section('content')
<div class="office-card mb-3">
	<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
		<div>
			<h2>Expected Visitors</h2>
			<p class="card-muted mb-0">Visitors whose route includes {{ $office->office_name }}</p>
		</div>
		<div class="d-flex gap-2">
			<a href="{{ route('office.expected-visitors') }}" class="btn btn-nu-outline btn-sm"><i class="bi bi-arrow-clockwise me-1" aria-hidden="true"></i>Refresh</a>
			<a href="{{ route('office.scanner') }}" class="btn btn-nu-primary btn-sm"><i class="bi bi-qr-code-scan me-1" aria-hidden="true"></i>Open Scanner</a>
		</div>
	</div>

	<form method="GET" action="{{ route('office.expected-visitors') }}" class="row g-2 align-items-end">
		<div class="col-md-3">
			<label for="search" class="form-label fw-semibold">Search</label>
			<input type="search" id="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Name, control no., purpose" autocomplete="off">
		</div>
		<div class="col-md-2">
			<label for="date" class="form-label fw-semibold">Date</label>
			<input type="date" id="date" name="date" value="{{ $filters['date'] }}" class="form-control">
		</div>
		<div class="col-md-2">
			<label for="status" class="form-label fw-semibold">Status</label>
			<select id="status" name="status" class="form-select">
				<option value="">All</option>
				<option value="ready" @selected($filters['status'] === 'ready')>Ready for Office Check-in</option>
				<option value="waiting" @selected($filters['status'] === 'waiting')>Waiting for Previous Office</option>
				<option value="expected" @selected($filters['status'] === 'expected')>Expected</option>
				<option value="checked_in" @selected($filters['status'] === 'checked_in')>Checked In</option>
			</select>
		</div>
		<div class="col-md-3">
			<label for="previous_office" class="form-label fw-semibold">Previous office</label>
			<select id="previous_office" name="previous_office" class="form-select">
				<option value="">All offices</option>
				@foreach($offices as $o)
					<option value="{{ $o->office_id }}" @selected((string) $filters['previous_office'] === (string) $o->office_id)>{{ $o->office_name }}</option>
				@endforeach
			</select>
		</div>
		<div class="col-md-2 d-grid">
			<button type="submit" class="btn btn-nu-primary">Apply Filters</button>
		</div>
	</form>
</div>

<div class="office-card">
	@include('office.components.visitor-table', [
		'rows' => $visitors,
		'emptyMessage' => 'No visitors are currently expected at your office.',
	])

	@if(method_exists($visitors, 'links'))
		@include('admin.partials.table-pagination', [
			'paginator' => $visitors,
			'perPageParam' => 'per_page',
			'ariaLabel' => 'Expected visitors pagination',
		])
	@endif
</div>
@endsection

@push('styles')
<style>
	@include('admin.partials.table-pagination-styles')
</style>
@endpush

@push('scripts')
<script>
	@include('admin.partials.table-pagination-script')
</script>
@include('partials.live-auto-refresh', ['liveRefreshIntervalMs' => 20000])
<script>
(function () {
	const input = document.getElementById('search');
	if (!input) return;
	let timer = null;
	input.addEventListener('input', () => {
		clearTimeout(timer);
		timer = setTimeout(() => {
			input.form?.requestSubmit();
		}, 600);
	});
})();
</script>
@endpush
