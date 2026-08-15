<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Date-Range Reports</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<style>
		:root {
			font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
		}

		* { box-sizing: border-box; }

		body {
			margin: 0;
			background: #eef2ff;
			color: #0f172a;
			overflow-x: auto;
		}

		.layout {
			display: block;
			min-height: 100vh;
			min-width: 1180px;
		}

		.sidebar {
			width: 260px;
			min-height: 100vh;
			background: linear-gradient(180deg, #243c96 0%, #2d3fa3 45%, #3146b4 100%);
			color: #fff;
			padding: 18px 14px;
			box-shadow: 4px 0 20px rgba(0, 0, 0, 0.12);
			position: fixed;
			top: 0;
			left: 0;
			bottom: 0;
			height: 100vh;
			overflow-y: auto;
			z-index: 1000;
		}

		.sidebar-brand {
			gap: 12px;
			padding: 10px 10px 18px;
			margin-bottom: 10px;
			border-bottom: 1px solid rgba(255, 255, 255, 0.12);
		}

		.brand-icon {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			background: rgba(255, 255, 255, 0.14);
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 22px;
			flex-shrink: 0;
		}

		.brand-title {
			margin: 0;
			font-size: 0;
			line-height: 1;
			font-weight: 800;
			letter-spacing: -0.02em;
			display: flex;
			gap: 6px;
			align-items: baseline;
		}

		.brand-title span:first-child { color: #ffd84d; font-size: 28px; }
		.brand-title span:last-child { color: #ffffff; font-size: 26px; font-weight: 700; }
		.brand-subtitle { color: rgba(255, 255, 255, 0.78); font-size: 12px; display: block; margin-top: 2px; }

		.sidebar-section { margin-top: 18px; }
		.sidebar-label {
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 1px;
			color: rgba(255, 255, 255, 0.55);
			margin: 0 0 8px 10px;
			text-transform: uppercase;
		}

		.sidebar-link {
			width: 100%;
			display: flex;
			align-items: center;
			gap: 12px;
			color: #fff;
			text-decoration: none;
			padding: 12px 14px;
			border-radius: 12px;
			margin-bottom: 6px;
			position: relative;
			transition: all 0.25s ease;
			font-weight: 500;
			border: none;
			background: transparent;
		}

		.sidebar-link:hover {
			background: rgba(255, 255, 255, 0.10);
			color: #fff;
			transform: translateX(4px);
		}

		.sidebar-link.active {
			background: linear-gradient(90deg, #4f62ff, #6678ff);
			color: #fff;
			box-shadow: 0 8px 20px rgba(46, 78, 255, 0.28);
		}

		.sidebar-link.active::before {
			content: "";
			position: absolute;
			left: -14px;
			top: 8px;
			bottom: 8px;
			width: 4px;
			border-radius: 10px;
			background: #ffd84d;
		}

		.sidebar-icon { width: 20px; text-align: center; font-size: 18px; flex-shrink: 0; }
		.sidebar-text { flex: 1; text-align: left; }
		.sidebar-badge {
			background: #ff4d4f;
			color: #fff;
			font-size: 11px;
			font-weight: 700;
			padding: 3px 8px;
			border-radius: 50px;
			min-width: 22px;
			text-align: center;
		}

		.sidebar-toggle { justify-content: space-between; cursor: pointer; }
		.dropdown-arrow { transition: transform 0.25s ease; font-size: 13px; }
		.sidebar-dropdown.open .dropdown-arrow,
		.sidebar-toggle[aria-expanded="true"] .dropdown-arrow { transform: rotate(180deg); }

		.submenu {
			display: none;
			margin: 6px 0 8px 14px;
			padding-left: 14px;
			border-left: 1px solid rgba(255, 255, 255, 0.15);
		}

		.sidebar-dropdown.open .submenu { display: block; }

		.submenu-link {
			display: flex;
			align-items: center;
			gap: 10px;
			color: rgba(255, 255, 255, 0.88);
			text-decoration: none;
			padding: 10px 12px;
			border-radius: 10px;
			margin-bottom: 5px;
			font-size: 14px;
			transition: all 0.2s ease;
		}

		.submenu-link:hover {
			background: rgba(255, 255, 255, 0.10);
			color: #fff;
			transform: translateX(3px);
		}

		.submenu-link.active {
			background: rgba(255, 255, 255, 0.16);
			color: #ffd84d;
			font-weight: 600;
		}

		.sidebar-footer {
			padding-top: 16px;
			margin-top: 20px;
			border-top: 1px solid rgba(255, 255, 255, 0.12);
		}

		.admin-card {
			display: flex;
			align-items: center;
			gap: 12px;
			background: rgba(255, 255, 255, 0.08);
			border-radius: 14px;
			padding: 12px;
			margin-bottom: 12px;
		}

		.admin-avatar {
			width: 42px;
			height: 42px;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.15);
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 22px;
			flex-shrink: 0;
		}

		.admin-info h6 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
		.admin-info small { color: rgba(255, 255, 255, 0.72); }

		.logout-btn {
			width: 100%;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			background: #fff;
			color: #ff3b30;
			text-decoration: none;
			padding: 11px 14px;
			border-radius: 12px;
			font-weight: 700;
			transition: all 0.25s ease;
		}

		.logout-btn:hover {
			background: #ffe9e9;
			color: #ff3b30;
			transform: translateY(-1px);
		}

		.main {
			flex: 1;
			background: #f7f8ff;
			padding: 24px 32px;
			margin-left: 260px;
			min-height: 100vh;
		}

		.page-title {
			margin: 0;
			font-size: 28px;
			font-weight: 700;
			color: #0f172a;
		}

		.page-subtitle {
			margin: 4px 0 0;
			color: #64748b;
			font-size: 14px;
		}

		.filters-card,
		.table-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		}

		.filters-card {
			padding: 20px;
			margin-top: 16px;
		}

		.filters-row {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr)) auto auto;
			gap: 10px;
			align-items: end;
		}

		.generate-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
			padding: 20px;
			margin-top: 16px;
		}

		.generate-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 14px;
			align-items: end;
		}

		.summary-box {
			margin-top: 16px;
			padding: 14px 16px;
			border-radius: 10px;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			display: none;
		}

		.summary-box.visible { display: block; }

		.summary-box h6 {
			margin: 0 0 8px;
			font-weight: 700;
			color: #0f172a;
		}

		.summary-box p {
			margin: 0 0 4px;
			color: #334155;
			font-size: 14px;
		}

		.validation-msg {
			min-height: 18px;
			margin-top: 6px;
			font-size: 12px;
			color: #b91c1c;
		}

		.generate-actions {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			margin-top: 16px;
		}

		.filters-label {
			margin: 0 0 8px;
			font-size: 18px;
			font-weight: 700;
			color: #111827;
		}

		.filter-input {
			height: 38px;
			border: 1px solid #d6dde8;
			outline: none;
			border-radius: 8px;
			padding: 0 12px;
			width: 100%;
			background: #fff;
		}

		.filter-input:focus {
			border-color: #4f62ff;
			box-shadow: 0 0 0 3px rgba(79, 98, 255, 0.12);
		}

		.field-label {
			display: block;
			font-size: 12px;
			font-weight: 600;
			color: #64748b;
			margin-bottom: 6px;
		}

		.table-card {
			margin-top: 16px;
			overflow: hidden;
		}

		.table-card .table {
			margin: 0;
			vertical-align: middle;
		}

		.table thead th {
			background: #f8fafc;
			font-size: 12px;
			text-transform: uppercase;
			letter-spacing: 0.04em;
			color: #475569;
			border-bottom: 1px solid #e2e8f0;
			white-space: nowrap;
		}

		.status-badge {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 4px 10px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 700;
		}

		.status-completed { background: #dcfce7; color: #166534; }
		.status-failed { background: #fee2e2; color: #991b1b; }
		.status-generating { background: #dbeafe; color: #1e40af; }
		.status-pending { background: #f1f5f9; color: #475569; }

		.action-btns {
			display: flex;
			flex-wrap: wrap;
			gap: 6px;
		}

		.empty-state {
			text-align: center;
			padding: 48px 20px;
			color: #64748b;
		}

		.empty-state i {
			font-size: 42px;
			color: #94a3b8;
			display: block;
			margin-bottom: 12px;
		}

		.toolbar {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			gap: 16px;
			flex-wrap: wrap;
		}

			@media (max-width: 1200px) {
			.layout { min-width: 0; }
			.filters-row,
			.generate-grid {
				grid-template-columns: 1fr 1fr;
			}
		}

		@media (max-width: 992px) {
			.sidebar {
				position: relative;
				width: 100%;
				height: auto;
				min-height: 0;
			}

			.main {
				margin-left: 0;
				padding: 16px;
			}

			.layout { min-width: 0; }

			.filters-row,
			.generate-grid {
				grid-template-columns: 1fr;
			}
		}

		@include('admin.partials.table-pagination-styles')
		@include('admin.partials.admin-topbar-styles')
	</style>
</head>
<body>
	<div class="layout">
		<aside class="sidebar d-flex flex-column justify-content-between">
			<div>
				<div class="sidebar-brand d-flex align-items-center">
					<div class="brand-icon">
						<i class="bi bi-person-badge-fill"></i>
					</div>
					<div>
						<h4 class="brand-title mb-0"><span>VMS</span> <span>Admin</span></h4>
						<small class="brand-subtitle">Visitor Monitoring System</small>
					</div>
				</div>

				<div class="sidebar-section">
					<p class="sidebar-label">MAIN</p>
					<a href="/admin/dashboard" class="sidebar-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-grid-1x2-fill"></i></span>
						<span class="sidebar-text">Dashboard</span>
					</a>
				</div>

				<div class="sidebar-section">
					<p class="sidebar-label">MONITORING</p>
					@php
						$sidebarUnresolvedAlertsCount = (int) \Illuminate\Support\Facades\DB::table('alerts')
							->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", ['unresolved'])
							->count();
					@endphp
					<a href="/admin/visitor" class="sidebar-link {{ request()->is('admin/visitor*') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-people-fill"></i></span>
						<span class="sidebar-text">Visitor Monitoring</span>
					</a>
					<a href="/admin/alerts" class="sidebar-link {{ request()->is('admin/alerts*') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-exclamation-triangle-fill"></i></span>
						<span class="sidebar-text">Alerts</span>
						<span class="sidebar-badge">{{ $sidebarUnresolvedAlertsCount }}</span>
					</a>
					<a href="{{ route('admin.daily-reports') }}" class="sidebar-link {{ request()->is('admin/daily-reports*') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-file-earmark-excel-fill"></i></span>
						<span class="sidebar-text">Daily Reports</span>
					</a>
					<a href="{{ route('admin.date-range-reports') }}" class="sidebar-link {{ request()->is('admin/date-range-reports*') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-calendar-range-fill"></i></span>
						<span class="sidebar-text">Date-Range Reports</span>
					</a>
				</div>

				@php
					$isUserMgmtOpen = request()->is('admin/user/guards*') || request()->is('admin/user/offices*');
				@endphp
				<div class="sidebar-section">
					<p class="sidebar-label">MANAGEMENT</p>
					<div class="sidebar-dropdown {{ $isUserMgmtOpen ? 'open' : '' }}" id="userMenuGroup">
						<button class="sidebar-link sidebar-toggle {{ $isUserMgmtOpen ? 'active' : '' }}"
							type="button"
							id="userMenuToggle"
							aria-expanded="{{ $isUserMgmtOpen ? 'true' : 'false' }}">
							<span class="d-flex align-items-center gap-2">
								<span class="sidebar-icon"><i class="bi bi-person-lines-fill"></i></span>
								<span class="sidebar-text">User Management</span>
							</span>
							<span class="dropdown-arrow"><i class="bi bi-chevron-down"></i></span>
						</button>
						<div class="submenu" id="userSubmenu">
							<a href="/admin/user/guards" class="submenu-link {{ request()->is('admin/user/guards*') ? 'active' : '' }}">
								<i class="bi bi-shield-fill-check"></i>
								<span>Guards</span>
							</a>
							<a href="/admin/user/offices" class="submenu-link {{ request()->is('admin/user/offices*') ? 'active' : '' }}">
								<i class="bi bi-building"></i>
								<span>Offices</span>
							</a>
						</div>
					</div>
					@include('admin.partials.sidebar-activity-logs-link')
				</div>
			</div>

			<div class="sidebar-footer">
				<div class="admin-card">
					<div class="admin-avatar">
						<i class="bi bi-person-circle"></i>
					</div>
					@php
						$sidebarAuthUser = auth()->user();
						$sidebarDisplayName = trim(((string) ($sidebarAuthUser->first_name ?? '')).' '.((string) ($sidebarAuthUser->last_name ?? '')));
						$sidebarDisplayName = $sidebarDisplayName !== ''
							? $sidebarDisplayName
							: ((string) ($sidebarAuthUser->name ?? $sidebarAuthUser->email ?? 'User'));
						$sidebarRoleLabel = ((int) ($sidebarAuthUser->role_id ?? 0) === 4) ? 'Guard' : 'System Administrator';
					@endphp
					<div class="admin-info">
						<h6 class="mb-0">{{ $sidebarDisplayName }}</h6>
						<small>{{ $sidebarRoleLabel }}</small>
					</div>
				</div>

			</div>
		</aside>

		<main class="main">
			@include('admin.partials.admin-topbar', ['title' => 'Date-Range Reports'])
			<div class="toolbar">
				<div>
					<p class="page-subtitle mb-0">Generate and download multi-day visitor Excel reports for complete calendar days.</p>
				</div>
				<a href="{{ route('admin.daily-reports') }}" class="btn btn-outline-primary">
					<i class="bi bi-file-earmark-excel me-1"></i> Daily Reports
				</a>
			</div>

			@if (session('success'))
				<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
					{{ session('success') }}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			@endif

			@if (session('error'))
				<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
					{{ session('error') }}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			@endif

			@if ($errors->any())
				<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
					<ul class="mb-0 ps-3">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			@endif

			<div class="generate-card">
				<p class="filters-label">Generate Date-Range Report</p>
				<form method="POST" action="{{ route('admin.date-range-reports.generate') }}" id="dateRangeReportForm" novalidate>
					@csrf
					<div class="generate-grid">
						<div>
							<label class="field-label" for="start_date">Start Date</label>
							<input
								type="date"
								id="start_date"
								name="start_date"
								class="filter-input"
								max="{{ $maxDate }}"
								value="{{ old('start_date') }}"
								required
							>
							<div class="validation-msg" id="startDateError" aria-live="polite"></div>
						</div>
						<div>
							<label class="field-label" for="end_date">End Date</label>
							<input
								type="date"
								id="end_date"
								name="end_date"
								class="filter-input"
								max="{{ $maxDate }}"
								value="{{ old('end_date') }}"
								required
							>
							<div class="validation-msg" id="endDateError" aria-live="polite"></div>
						</div>
					</div>

					<div class="summary-box" id="rangeSummary" aria-live="polite">
						<h6>Confirmation Summary</h6>
						<p><strong>Report Type:</strong> Date-Range Visitor Report</p>
						<p><strong>Start Date:</strong> <span id="summaryStart">â€”</span></p>
						<p><strong>End Date:</strong> <span id="summaryEnd">â€”</span></p>
						<p><strong>Coverage:</strong> Complete calendar days (12:00:00 AM to 11:59:59 PM)</p>
						<p class="mb-0"><strong>Selected Range:</strong> <span id="summaryDays">â€”</span></p>
					</div>

					<div class="generate-actions">
						<button type="submit" class="btn btn-primary" id="generateDateRangeSubmit" disabled>
							<span class="generate-label"><i class="bi bi-download me-1"></i> Generate and Download</span>
							<span class="generate-loading d-none">
								<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
								Generating...
							</span>
						</button>
						<button type="button" class="btn btn-outline-secondary" id="resetDateRangeForm">Cancel / Reset</button>
					</div>
					<p class="small text-muted mt-3 mb-0">
						Select at least two calendar days and at most {{ $maxRangeDays }} days. Future dates are not allowed. All visitor records from 12:00 AM to 11:59:59 PM are included for each selected date.
					</p>
				</form>
			</div>

			<div class="filters-card">
				<p class="filters-label">Search History</p>
				<form method="GET" action="{{ route('admin.date-range-reports') }}">
					<div class="filters-row">
						<div>
							<label class="field-label" for="date_from">Start From</label>
							<input type="date" id="date_from" name="date_from" class="filter-input" max="{{ $maxDate }}" value="{{ $filters['date_from'] }}">
						</div>
						<div>
							<label class="field-label" for="date_to">End To</label>
							<input type="date" id="date_to" name="date_to" class="filter-input" max="{{ $maxDate }}" value="{{ $filters['date_to'] }}">
						</div>
						<button type="submit" class="btn btn-primary">
							<i class="bi bi-search me-1"></i> Search
						</button>
						<a href="{{ route('admin.date-range-reports') }}" class="btn btn-outline-secondary">Reset</a>
					</div>
				</form>
			</div>

			<div class="table-card">
				@if ($reports->isEmpty())
					<div class="empty-state">
						<i class="bi bi-calendar-range"></i>
						<h5 class="mb-1">No date-range reports yet</h5>
						<p class="mb-0">Select a start and end date above to generate your first multi-day Excel report.</p>
					</div>
				@else
					<div class="table-responsive">
						<table class="table table-hover align-middle mb-0">
							<thead>
								<tr>
									<th>Start Date</th>
									<th>End Date</th>
									<th>File Name</th>
									<th>Visitor Records</th>
									<th>Status</th>
									<th>Generated At</th>
									<th>Generated By</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($reports as $report)
									@php
										$generator = $report->generator;
										$generatorName = $generator
											? trim(((string) ($generator->first_name ?? '')).' '.((string) ($generator->last_name ?? '')))
											: '';
										if ($generatorName === '' && $generator) {
											$generatorName = (string) ($generator->email ?? '');
										}
										if ($generatorName === '') {
											$generatorName = $report->generated_by ? 'User #'.$report->generated_by : 'Admin';
										}
										$statusClass = match ($report->generation_status) {
											'completed' => 'status-completed',
											'failed' => 'status-failed',
											'generating' => 'status-generating',
											default => 'status-pending',
										};
									@endphp
									<tr>
										<td>{{ optional($report->report_date)->format('M d, Y') }}</td>
										<td>{{ optional($report->date_range_end)->format('M d, Y') ?: 'â€”' }}</td>
										<td><code>{{ $report->file_name }}</code></td>
										<td>{{ number_format((int) $report->record_count) }}</td>
										<td>
											<span class="status-badge {{ $statusClass }}">
												{{ ucfirst($report->generation_status) }}
											</span>
											@if ($report->generation_status === 'failed' && $report->error_message)
												<div class="small text-danger mt-1">{{ \Illuminate\Support\Str::limit($report->error_message, 80) }}</div>
											@endif
										</td>
										<td>
											{{ $report->generated_at ? $report->generated_at->timezone('Asia/Manila')->format('M d, Y h:i A') : 'â€”' }}
										</td>
										<td>{{ $generatorName }}</td>
										<td>
											@if ($report->isDownloadable())
												<a href="{{ route('admin.date-range-reports.download', $report->id) }}" class="btn btn-sm btn-success">
													<i class="bi bi-download"></i> Download
												</a>
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" disabled>
													<i class="bi bi-download"></i> Download
												</button>
											@endif
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>

					@include('admin.partials.table-pagination', [
						'paginator' => $reports,
						'perPageParam' => 'per_page',
						'ariaLabel' => 'Date-range reports pagination',
					])
				@endif
			</div>
		</main>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	<script>
		@include('admin.partials.table-pagination-script')

		(function () {
			const toggle = document.getElementById('userMenuToggle');
			const group = document.getElementById('userMenuGroup');
			if (toggle && group) {
				toggle.addEventListener('click', function () {
					const open = group.classList.toggle('open');
					toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
				});
			}

			const maxRangeDays = {{ (int) $maxRangeDays }};
			const maxDate = @json($maxDate);
			const form = document.getElementById('dateRangeReportForm');
			const startInput = document.getElementById('start_date');
			const endInput = document.getElementById('end_date');
			const submitBtn = document.getElementById('generateDateRangeSubmit');
			const resetBtn = document.getElementById('resetDateRangeForm');
			const summary = document.getElementById('rangeSummary');
			const startError = document.getElementById('startDateError');
			const endError = document.getElementById('endDateError');

			function parseLocalDate(value) {
				if (!value) return null;
				const parts = value.split('-').map(Number);
				if (parts.length !== 3 || parts.some((n) => Number.isNaN(n))) return null;
				return new Date(parts[0], parts[1] - 1, parts[2]);
			}

			function formatLongDate(date) {
				return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
			}

			function inclusiveDays(start, end) {
				const ms = end.getTime() - start.getTime();
				return Math.floor(ms / 86400000) + 1;
			}

			function validateRange(showMessages) {
				const startValue = startInput.value;
				const endValue = endInput.value;
				const today = parseLocalDate(maxDate);
				const start = parseLocalDate(startValue);
				const end = parseLocalDate(endValue);
				let startMsg = '';
				let endMsg = '';
				let valid = true;

				if (!startValue) {
					valid = false;
					if (showMessages) startMsg = 'Please select a start date.';
				} else if (start && today && start > today) {
					valid = false;
					startMsg = 'Future dates are not allowed.';
				}

				if (!endValue) {
					valid = false;
					if (showMessages) endMsg = 'Please select an end date.';
				} else if (end && today && end > today) {
					valid = false;
					endMsg = 'Future dates are not allowed.';
				}

				if (start && end && end < start) {
					valid = false;
					endMsg = 'The end date must be on or after the start date.';
				}

				if (start && end && end >= start) {
					const days = inclusiveDays(start, end);
					if (days < 2) {
						valid = false;
						endMsg = 'The selected range must cover at least two days.';
					} else if (days > maxRangeDays) {
						valid = false;
						endMsg = 'The selected date range cannot exceed ' + maxRangeDays + ' days.';
					}
				}

				startError.textContent = startMsg;
				endError.textContent = endMsg;

				if (start && end && end >= start && inclusiveDays(start, end) >= 2 && inclusiveDays(start, end) <= maxRangeDays && (!today || (start <= today && end <= today))) {
					summary.classList.add('visible');
					document.getElementById('summaryStart').textContent = formatLongDate(start);
					document.getElementById('summaryEnd').textContent = formatLongDate(end);
					document.getElementById('summaryDays').textContent = inclusiveDays(start, end) + ' calendar day(s)';
				} else {
					summary.classList.remove('visible');
				}

				submitBtn.disabled = !valid || !startValue || !endValue;
				return valid;
			}

			['change', 'input'].forEach((evt) => {
				startInput.addEventListener(evt, () => validateRange(true));
				endInput.addEventListener(evt, () => validateRange(true));
			});

			resetBtn.addEventListener('click', function () {
				form.reset();
				startError.textContent = '';
				endError.textContent = '';
				summary.classList.remove('visible');
				submitBtn.disabled = true;
				submitBtn.querySelector('.generate-label')?.classList.remove('d-none');
				submitBtn.querySelector('.generate-loading')?.classList.add('d-none');
			});

			form.addEventListener('submit', function (event) {
				if (!validateRange(true)) {
					event.preventDefault();
					return;
				}

				submitBtn.disabled = true;
				submitBtn.querySelector('.generate-label')?.classList.add('d-none');
				submitBtn.querySelector('.generate-loading')?.classList.remove('d-none');

				// Re-enable after download starts so the Admin can generate again.
				window.setTimeout(function () {
					submitBtn.disabled = false;
					submitBtn.querySelector('.generate-label')?.classList.remove('d-none');
					submitBtn.querySelector('.generate-loading')?.classList.add('d-none');
					validateRange(false);
				}, 2500);
			});

			validateRange(false);
		})();
	</script>
</body>
</html>
