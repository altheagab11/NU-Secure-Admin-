@php $activeNav = 'dashboard'; @endphp
@extends('office.layouts.office-layout')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
	<div>
		<div class="d-flex align-items-center gap-2 flex-wrap">
			<h2 class="mb-0" style="font-size:1.15rem;font-weight:800;">{{ $office->office_name }}</h2>
			@include('office.components.status-badge', ['tone' => $officeStatus === 'Open' ? 'success' : 'muted', 'label' => $officeStatus])
		</div>
		<p class="card-muted mb-0 mt-1">{{ $staffName }} · {{ $staffRole }}</p>
	</div>
	<a href="{{ route('office.scanner') }}" class="btn btn-nu-primary">
		<i class="bi bi-qr-code-scan me-1" aria-hidden="true"></i>Open Scanner
	</a>
</div>

<div class="stat-grid mb-3" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
	@include('office.components.statistic-card', ['label' => 'Pending Scans', 'value' => $stats['pending_office_scans'], 'icon' => 'bi-hourglass-split', 'statKey' => 'pending_office_scans'])
	@include('office.components.statistic-card', ['label' => 'Expected Visitors', 'value' => $stats['expected_visitors'], 'icon' => 'bi-person-check', 'statKey' => 'expected_visitors'])
	@include('office.components.statistic-card', ['label' => "Today's Scans", 'value' => $stats['todays_visitors'], 'icon' => 'bi-check2-circle', 'statKey' => 'todays_visitors'])
</div>

<div class="row g-3 mb-3">
	<div class="col-lg-5">
		<div class="office-card h-100">
			<h2><i class="bi bi-qr-code-scan me-2 text-primary" aria-hidden="true"></i>Quick Scan</h2>
			<p class="card-muted">Scan the visitor QR when their visit at this office is done.</p>
			<div class="rounded-4 d-flex flex-column align-items-center justify-content-center mb-3" style="min-height: 160px; background: linear-gradient(160deg, #0B57B7, #064A9F); color: #fff;">
				<i class="bi bi-qr-code" style="font-size: 2.6rem; color: #ffd84d;" aria-hidden="true"></i>
				<div class="fw-bold mt-2">Ready to Scan</div>
			</div>
			<div class="d-grid gap-2">
				<a href="{{ route('office.scanner') }}" class="btn btn-nu-primary"><i class="bi bi-camera-video me-1" aria-hidden="true"></i>Start QR Scanner</a>
				<button type="button" class="btn btn-nu-outline" data-open-manual-scan><i class="bi bi-keyboard me-1" aria-hidden="true"></i>Enter QR Manually</button>
			</div>
		</div>
	</div>
	<div class="col-lg-7">
		<div class="office-card h-100">
			<div class="d-flex justify-content-between align-items-center mb-2">
				<div>
					<h2 class="mb-0">Ready to Scan</h2>
					<p class="card-muted mb-0">Visitors whose next stop is {{ $office->office_name }}</p>
				</div>
				<span class="badge-status badge-info" id="livePulse">Live</span>
			</div>
			<div id="liveWaiting">
				@forelse($live['waiting'] as $item)
					<div class="d-flex justify-content-between align-items-center border-bottom py-2 gap-2">
						<div class="min-w-0">
							<div class="fw-semibold text-truncate">{{ $item['visitor_name'] }}</div>
							<div class="small text-muted text-truncate">{{ $item['control_number'] ?: '—' }} · from {{ $item['previous_office'] }}</div>
						</div>
						<a href="{{ route('office.scanner') }}?visit={{ $item['visit_id'] }}" class="btn btn-sm btn-nu-primary flex-shrink-0">Scan</a>
					</div>
				@empty
					<div class="empty-state py-4">
						<i class="bi bi-inbox" aria-hidden="true"></i>
						<p class="mb-0">No visitors waiting to be scanned.</p>
					</div>
				@endforelse
			</div>
			@if($live['latest_scan'])
				<div class="small text-muted mt-3 pt-2 border-top">
					Latest scan:
					<strong id="latestScanStatus">
						{{ $live['latest_scan']->status_name ?? '—' }}
						@if(!empty($live['latest_scan']->scan_time))
							· {{ \Carbon\Carbon::parse($live['latest_scan']->scan_time)->timezone('Asia/Manila')->format('g:i A') }}
						@endif
					</strong>
				</div>
			@endif
		</div>
	</div>
</div>

<div class="row g-3">
	<div class="col-xl-7">
		<div class="office-card">
			<div class="d-flex justify-content-between align-items-center mb-2">
				<div>
					<h2>Today's Recent Scans</h2>
					<p class="card-muted mb-0">Latest QR scans today at this office</p>
				</div>
				<a href="{{ route('office.visit-history') }}" class="btn btn-sm btn-nu-outline">History</a>
			</div>
			<div id="recentActivityWrap">
			@if($recentActivity->isEmpty())
				<div class="empty-state">
					<i class="bi bi-clock-history" aria-hidden="true"></i>
					<p class="mb-0">No scans yet today.</p>
					<p class="card-muted mb-0">QR scans made at this office will appear here.</p>
				</div>
			@else
				<div class="table-scroll">
					<table class="table-office">
						<thead>
							<tr>
								<th>Visitor</th>
								<th>Control No.</th>
								<th>Purpose</th>
								<th>Time</th>
								<th>Status</th>
								<th></th>
							</tr>
						</thead>
						<tbody id="recentActivityBody">
							@foreach($recentActivity as $row)
								<tr>
									<td>{{ $row->visitor_name }}</td>
									<td>{{ $row->control_number ?: '—' }}</td>
									<td>{{ \Illuminate\Support\Str::limit($row->purpose_reason ?: '—', 28) }}</td>
									<td>{{ $row->scan_time_label ?? ($row->scan_time ? \Carbon\Carbon::parse($row->scan_time)->timezone('Asia/Manila')->format('g:i A') : '—') }}</td>
									<td>
										@include('office.components.status-badge', [
											'tone' => strtolower((string) $row->validation_status),
											'label' => $row->validation_status ?: '—',
										])
									</td>
									<td><a href="{{ route('office.visitors.show', $row->visit_id) }}" class="btn btn-sm btn-nu-outline">View</a></td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@endif
			</div>
			<div id="scansPagination">
				@include('admin.partials.table-pagination', [
					'paginator' => $recentActivity,
					'perPageParam' => 'scans_per_page',
					'ariaLabel' => "Today's recent scans pagination",
				])
			</div>
		</div>
	</div>
	<div class="col-xl-5">
		<div class="office-card">
			<div class="d-flex justify-content-between align-items-center mb-2">
				<div>
					<h2>Expected Visitors</h2>
					<p class="card-muted mb-0">Coming to your office</p>
				</div>
				<a href="{{ route('office.expected-visitors') }}" class="btn btn-sm btn-nu-outline">View All</a>
			</div>
			<div id="expectedVisitorsWrap">
				@include('office.components.visitor-table', ['rows' => $expectedPreview, 'showActions' => true])
			</div>
			<div id="expectedPagination">
				@include('admin.partials.table-pagination', [
					'paginator' => $expectedPreview,
					'perPageParam' => 'expected_per_page',
					'ariaLabel' => 'Expected visitors pagination',
				])
			</div>
		</div>
	</div>
</div>

@include('office.components.scan-result-modal')
@endsection

@push('styles')
<style>
	@include('admin.partials.table-pagination-styles')

	.office-card .table-pagination-bar {
		margin-top: 12px;
	}
</style>
@endpush

@push('scripts')
@include('office.partials.scan-scripts')
<script>
OfficeScan.init({
	onSuccess: function () {
		setTimeout(() => window.location.reload(), 700);
	}
});

(function () {
	const liveUrl = @json(route('office.dashboard.live'));
	const recentWrap = document.getElementById('recentActivityWrap');
	const expectedWrap = document.getElementById('expectedVisitorsWrap');
	const scansPagination = document.getElementById('scansPagination');
	const expectedPagination = document.getElementById('expectedPagination');

	const badgeClass = function (status) {
		const key = String(status || '').toLowerCase();
		const map = {
			valid: 'badge-success',
			invalid: 'badge-danger',
			unauthorized: 'badge-danger',
			success: 'badge-success',
			danger: 'badge-danger',
			warning: 'badge-warning',
			info: 'badge-info',
			muted: 'badge-muted',
		};
		return map[key] || 'badge-info';
	};
	const escapeHtml = function (value) {
		return String(value ?? '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	};
	const setNavState = function (link, disabled, href) {
		if (!link) return;
		link.classList.toggle('is-disabled', disabled);
		if (disabled) {
			link.setAttribute('aria-disabled', 'true');
			link.setAttribute('tabindex', '-1');
			link.setAttribute('href', '#');
		} else {
			link.removeAttribute('aria-disabled');
			link.removeAttribute('tabindex');
			if (href) link.setAttribute('href', href);
		}
	};
	const updatePagination = function (root, meta) {
		if (!root || !meta) return;
		const range = root.querySelector('.table-pagination-range');
		const pageLabel = root.querySelector('.table-pagination-page');
		const pageSize = root.querySelector('.table-page-size');
		const links = root.querySelectorAll('.table-pagination-nav');
		if (range) {
			range.textContent = (meta.from || 0) + ' to ' + (meta.to || 0) + ' of ' + (meta.total || 0);
		}
		if (pageLabel) {
			pageLabel.innerHTML = 'Page <strong>' + (meta.current_page || 1) + '</strong> of ' + (meta.last_page || 1);
		}
		if (pageSize && String(pageSize.value) !== String(meta.per_page)) {
			pageSize.value = String(meta.per_page);
		}
		const onFirst = (meta.current_page || 1) <= 1;
		const onLast = (meta.current_page || 1) >= (meta.last_page || 1);
		setNavState(links[0], onFirst, meta.first_url);
		setNavState(links[1], onFirst, meta.prev_url);
		setNavState(links[2], onLast, meta.next_url);
		setNavState(links[3], onLast, meta.last_url);
	};
	const renderRecentActivity = function (rows) {
		if (!recentWrap) return;
		if (!Array.isArray(rows) || rows.length === 0) {
			recentWrap.innerHTML = `
				<div class="empty-state">
					<i class="bi bi-clock-history" aria-hidden="true"></i>
					<p class="mb-0">No scans yet today.</p>
					<p class="card-muted mb-0">QR scans made at this office will appear here.</p>
				</div>
			`;
			return;
		}
		recentWrap.innerHTML = `
			<div class="table-scroll">
				<table class="table-office">
					<thead>
						<tr>
							<th>Visitor</th>
							<th>Control No.</th>
							<th>Purpose</th>
							<th>Time</th>
							<th>Status</th>
							<th></th>
						</tr>
					</thead>
					<tbody id="recentActivityBody">
						${rows.map((row) => `
							<tr>
								<td>${escapeHtml(row.visitor_name)}</td>
								<td>${escapeHtml(row.control_number)}</td>
								<td>${escapeHtml(row.purpose)}</td>
								<td>${escapeHtml(row.time_label)}</td>
								<td><span class="badge-status ${badgeClass(row.validation_status)}">${escapeHtml(row.validation_status)}</span></td>
								<td><a href="${escapeHtml(row.view_url)}" class="btn btn-sm btn-nu-outline">View</a></td>
							</tr>
						`).join('')}
					</tbody>
				</table>
			</div>
		`;
	};
	const renderExpectedVisitors = function (rows) {
		if (!expectedWrap) return;
		if (!Array.isArray(rows) || rows.length === 0) {
			expectedWrap.innerHTML = `
				<div class="empty-state">
					<i class="bi bi-inbox" aria-hidden="true"></i>
					<p class="mb-0">No visitors are currently expected at your office.</p>
				</div>
			`;
			return;
		}
		expectedWrap.innerHTML = `
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
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						${rows.map((row) => `
							<tr>
								<td>${escapeHtml(row.control_number)}</td>
								<td>${escapeHtml(row.visitor_name)}</td>
								<td>${escapeHtml(row.purpose)}</td>
								<td>${escapeHtml(row.previous_office)}</td>
								<td>${escapeHtml(row.expected_label)}</td>
								<td><span class="badge-status ${badgeClass(row.badge)}">${escapeHtml(row.route_status)}</span></td>
								<td class="text-nowrap">
									<a href="${escapeHtml(row.view_url)}" class="btn btn-sm btn-nu-outline">View</a>
									${row.scan_url ? `<a href="${escapeHtml(row.scan_url)}" class="btn btn-sm btn-nu-primary">Scan</a>` : ''}
								</td>
							</tr>
						`).join('')}
					</tbody>
				</table>
			</div>
		`;
	};

	setInterval(async () => {
		if (document.hidden) return;
		if (document.querySelector('#scanResultModal.show, #manualPayloadModal.show')) return;
		try {
			const res = await fetch(liveUrl + window.location.search, {
				headers: { Accept: 'application/json' },
				credentials: 'same-origin',
				cache: 'no-store',
			});
			if (!res.ok) return;
			const payload = await res.json();
			if (!payload.success || !payload.stats) return;
			Object.keys(payload.stats).forEach((key) => {
				const el = document.querySelector('[data-stat="' + key + '"]');
				if (el) el.textContent = Number(payload.stats[key] || 0).toLocaleString();
			});
			if (payload.recent_activity) {
				renderRecentActivity(payload.recent_activity.data || []);
				updatePagination(scansPagination, payload.recent_activity.meta);
			}
			if (payload.expected_visitors) {
				renderExpectedVisitors(payload.expected_visitors.data || []);
				updatePagination(expectedPagination, payload.expected_visitors.meta);
			}
			const pulse = document.getElementById('livePulse');
			if (pulse) {
				pulse.textContent = 'Updated';
				setTimeout(() => { pulse.textContent = 'Live'; }, 1200);
			}
		} catch (e) {}
	}, 20000);
})();
</script>
<script>
	@include('admin.partials.table-pagination-script')
</script>
@endpush
