@php $activeNav = 'expected'; @endphp
@extends('office.layouts.office-layout')

@section('title', 'Expected Visitors')

@section('content')
<div class="office-card">
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

	<form method="GET" action="{{ route('office.expected-visitors') }}" class="expected-filters mb-3">
		<div class="expected-filters__fields">
			<div class="expected-filters__field expected-filters__field--search">
				<label for="search" class="visually-hidden">Search</label>
				<input type="search" id="search" name="search" value="{{ $filters['search'] }}" class="form-control form-control-sm" placeholder="Search name, control no., purpose" autocomplete="off">
			</div>
			<div class="expected-filters__field">
				<label for="date" class="visually-hidden">Date</label>
				<input type="date" id="date" name="date" value="{{ $filters['date'] }}" class="form-control form-control-sm" title="Date">
			</div>
			<div class="expected-filters__field">
				<label for="status" class="visually-hidden">Status</label>
				<select id="status" name="status" class="form-select form-select-sm" title="Status">
					<option value="">All statuses</option>
					<option value="ready" @selected($filters['status'] === 'ready')>Ready for Office Check-in</option>
					<option value="waiting" @selected($filters['status'] === 'waiting')>Waiting for Previous Office</option>
					<option value="expected" @selected($filters['status'] === 'expected')>Expected</option>
					<option value="checked_in" @selected($filters['status'] === 'checked_in')>Checked In</option>
				</select>
			</div>
			<div class="expected-filters__field">
				<label for="previous_office" class="visually-hidden">Previous office</label>
				<select id="previous_office" name="previous_office" class="form-select form-select-sm" title="Previous office">
					<option value="">All offices</option>
					@foreach($offices as $o)
						<option value="{{ $o->office_id }}" @selected((string) $filters['previous_office'] === (string) $o->office_id)>{{ $o->office_name }}</option>
					@endforeach
				</select>
			</div>
			<button type="submit" class="btn btn-nu-primary btn-sm">Apply</button>
		</div>
	</form>

	@include('office.components.visitor-table', [
		'rows' => $visitors,
		'emptyMessage' => 'No visitors are currently expected at your office.',
	])

	@include('admin.partials.table-pagination', [
		'paginator' => $visitors,
		'perPageParam' => 'per_page',
		'ariaLabel' => 'Expected visitors pagination',
	])
</div>
@endsection

@push('styles')
<style nonce="{{ $cspNonce }}">
	@include('admin.partials.table-pagination-styles')

	.office-card .table-pagination-bar {
		margin-top: 12px;
	}

	.expected-filters__fields {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: .5rem;
	}
	.expected-filters__field {
		min-width: 9rem;
		flex: 1 1 9rem;
	}
	.expected-filters__field--search {
		flex: 2 1 14rem;
		min-width: 12rem;
	}
	.expected-filters .btn {
		flex: 0 0 auto;
		white-space: nowrap;
	}

	.expected-visitor-cell {
		display: flex;
		align-items: center;
		gap: 10px;
		min-width: 0;
	}
	.expected-avatar {
		width: 34px;
		height: 34px;
		border-radius: 10px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: #eef2ff;
		color: #273b9e;
		font-size: 11px;
		font-weight: 800;
		flex-shrink: 0;
	}
	.expected-visitor-name {
		font-weight: 700;
		color: #0f172a;
		line-height: 1.25;
	}
	.expected-control {
		display: inline-block;
		padding: 3px 8px;
		border-radius: 6px;
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		color: #334155;
		font-size: 12px;
		font-weight: 600;
	}
	.expected-purpose {
		color: #64748b;
		font-size: 13px;
	}
	.expected-table th:first-child,
	.expected-table td:first-child {
		min-width: 180px;
	}
</style>
@endpush

@push('scripts')
<script nonce="{{ $cspNonce }}">
	@include('admin.partials.table-pagination-script')
</script>
<script nonce="{{ $cspNonce }}">
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
