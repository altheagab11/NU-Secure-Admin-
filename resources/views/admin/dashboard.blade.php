<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin Dashboard</title>
	@include('partials.favicons')
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<style nonce="{{ $cspNonce }}">
		:root {
			font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
			--sidebar-bg: #39459a;
			--sidebar-bg-light: #4b5cd1;
			--text-white: #f4f6ff;
			--text-yellow: #ffe632;
			--muted: #d8defe;
			--line: rgba(255, 255, 255, 0.18);
		}

		* {
			box-sizing: border-box;
		}

		body {
			margin: 0;
			background: #eef2ff;
			color: #0f172a;
			overflow-x: clip;
		}

		.layout {
			display: block;
			min-height: 100vh;
			min-width: 0;
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

		.sidebar::-webkit-scrollbar {
			width: 6px;
		}

		.sidebar::-webkit-scrollbar-thumb {
			background: rgba(255, 255, 255, 0.18);
			border-radius: 10px;
		}

		.sidebar-brand {
			gap: 12px;
			padding: 10px 10px 18px;
			margin-bottom: 10px;
			border-bottom: 1px solid rgba(255, 255, 255, 0.12);
		}

		.brand-icon {
			width: 52px;
			height: 52px;
			border-radius: 0;
			background: transparent;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
			overflow: visible;
			padding: 0;
			box-shadow: none;
			border: 0;
		}

		.brand-icon img {
			width: 52px;
			height: 52px;
			object-fit: contain;
			display: block;
			background: transparent;
			filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.25));
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

		.brand-title span:first-child {
			color: #ffd84d;
			font-size: 28px;
		}

		.brand-title span:last-child {
			color: #ffffff;
			font-size: 26px;
			font-weight: 700;
		}

		.brand-subtitle {
			color: rgba(255, 255, 255, 0.78);
			font-size: 12px;
			display: block;
			margin-top: 2px;
		}

		.sidebar-section {
			margin-top: 18px;
		}

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

		.sidebar-icon {
			width: 20px;
			text-align: center;
			font-size: 18px;
			flex-shrink: 0;
		}

		.sidebar-text {
			flex: 1;
			text-align: left;
		}

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

		.sidebar-toggle {
			justify-content: space-between;
			cursor: pointer;
		}

		.dropdown-arrow {
			transition: transform 0.25s ease;
			font-size: 13px;
		}

		.sidebar-dropdown.open .dropdown-arrow,
		.sidebar-toggle[aria-expanded="true"] .dropdown-arrow {
			transform: rotate(180deg);
		}

		.submenu {
			display: none;
			margin: 6px 0 8px 14px;
			padding-left: 14px;
			border-left: 1px solid rgba(255, 255, 255, 0.15);
		}

		.sidebar-dropdown.open .submenu {
			display: block;
		}

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

		.admin-info h6 {
			font-size: 15px;
			font-weight: 700;
			color: #fff;
		}

		.admin-info small {
			color: rgba(255, 255, 255, 0.72);
		}

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
			padding: 20px 22px 24px;
			margin-left: 260px;
			min-height: 100vh;
			overflow-y: auto;
		}

		@include('admin.partials.admin-topbar-styles')
		@include('admin.partials.admin-responsive-styles')
		@include('admin.partials.dashboard-styles')
	</style>
</head>
<body>
	<div class="layout">
		<aside class="sidebar d-flex flex-column justify-content-between">
			<div>
				<div class="sidebar-brand d-flex align-items-center">
					<div class="brand-icon" aria-hidden="true">
						@include('partials.brand-logo')
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
					@include('admin.partials.sidebar-guard-duty-link')
					<a href="/admin/daily-reports" class="sidebar-link {{ request()->is('admin/daily-reports*') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-file-earmark-excel-fill"></i></span>
						<span class="sidebar-text">Daily Reports</span>
					</a>
					<a href="/admin/date-range-reports" class="sidebar-link {{ request()->is('admin/date-range-reports*') ? 'active' : '' }}">
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
					@include('admin.partials.sidebar-login-attempts-link')
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

		<main class="main dashboard-main">
			<div class="container-fluid pt-0 pb-4">
				@include('admin.partials.admin-topbar', ['title' => 'Dashboard'])

				<div class="dashboard-meta d-flex justify-content-end align-items-center gap-3 flex-wrap">
					<a href="{{ route('admin.guard-duty') }}" class="text-decoration-none">
						<div class="card shadow-sm border-0 rounded-4">
							<div class="card-body py-2 px-3 d-flex align-items-center gap-3">
								<div class="text-muted small mb-0">Guards On Duty</div>
								<h5 class="fw-bold mb-0">{{ $guardsOnDutyCount ?? 0 }}</h5>
							</div>
						</div>
					</a>
					<div class="text-muted small">Last updated: just now</div>
				</div>

				<div class="row g-3 mb-3">
					<div class="col-md-6 col-xl-3">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body metric-body"><span class="metric-icon tone-blue" aria-hidden="true"><i class="bi bi-people-fill"></i></span><div><div class="text-muted small mb-1">Total Visitors Today</div><h2 class="fw-bold mb-0">{{ $totalVisitorsToday ?? 0 }}</h2></div>
							</div>
						</div>
					</div>

					<div class="col-md-6 col-xl-3">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body metric-body"><span class="metric-icon tone-green" aria-hidden="true"><i class="bi bi-person-check-fill"></i></span><div><div class="text-muted small mb-1">Currently Inside</div><h2 class="fw-bold mb-0">{{ $currentlyInside ?? 0 }}</h2></div>
							</div>
						</div>
					</div>

					<div class="col-md-6 col-xl-3">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body metric-body"><span class="metric-icon tone-purple" aria-hidden="true"><i class="bi bi-buildings-fill"></i></span><div><div class="text-muted small mb-1">Active Offices</div><h2 class="fw-bold mb-0">{{ $activeOffices ?? 0 }}</h2></div>
							</div>
						</div>
					</div>

					<div class="col-md-6 col-xl-3">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body metric-body"><span class="metric-icon tone-amber" aria-hidden="true"><i class="bi bi-clock-fill"></i></span><div><div class="text-muted small mb-1">Average Duration</div><h2 class="fw-bold mb-0">{{ $averageDuration ?? '0m' }}</h2></div>
							</div>
						</div>
					</div>
				</div>

				<div class="row g-3 mb-3">
					<div class="col-md-4">
						<a href="{{ route('admin.login-attempts') }}" class="text-decoration-none">
							<div class="card shadow-sm border-0 rounded-4 h-100">
								<div class="card-body metric-body"><span class="metric-icon tone-green" aria-hidden="true"><i class="bi bi-shield-check"></i></span><div><div class="text-muted small mb-1">Successful Logins Today</div><h2 class="fw-bold mb-0 text-success">{{ $successfulLoginsToday ?? 0 }}</h2></div>
								</div>
							</div>
						</a>
					</div>
					<div class="col-md-4">
						<a href="{{ route('admin.login-attempts') }}" class="text-decoration-none">
							<div class="card shadow-sm border-0 rounded-4 h-100">
								<div class="card-body metric-body"><span class="metric-icon tone-red" aria-hidden="true"><i class="bi bi-shield-x"></i></span><div><div class="text-muted small mb-1">Failed Login Attempts Today</div><h2 class="fw-bold mb-0 text-danger">{{ $failedLoginsToday ?? 0 }}</h2></div>
								</div>
							</div>
						</a>
					</div>
					<div class="col-md-4">
						<a href="{{ route('admin.login-attempts') }}" class="text-decoration-none">
							<div class="card shadow-sm border-0 rounded-4 h-100">
								<div class="card-body metric-body"><span class="metric-icon tone-amber" aria-hidden="true"><i class="bi bi-shield-lock-fill"></i></span><div><div class="text-muted small mb-1">Blocked Attempts Today</div><h2 class="fw-bold mb-0 text-warning">{{ $blockedLoginsToday ?? 0 }}</h2></div>
								</div>
							</div>
						</a>
					</div>
				</div>

<section class="dashboard-filter-bar mb-3" aria-label="Dashboard filters"><form method="GET" action="/admin/dashboard" class="dashboard-filter-form"><div class="filter-heading"><span class="metric-icon tone-blue" aria-hidden="true"><i class="bi bi-funnel-fill"></i></span><strong>Filters</strong></div>
									<div class="filter-field">
										<label class="form-label" for="dashboard-date_filter">Date Range</label>
										<select id="dashboard-date_filter" name="date_filter" class="form-select">
											<option value="" {{ ($selectedDateFilter ?? '') === '' ? 'selected' : '' }}>All</option>
											<option value="today" {{ ($selectedDateFilter ?? '') === 'today' ? 'selected' : '' }}>Today</option>
											<option value="week" {{ ($selectedDateFilter ?? '') === 'week' ? 'selected' : '' }}>This Week</option>
											<option value="month" {{ ($selectedDateFilter ?? '') === 'month' ? 'selected' : '' }}>This Month</option>
										</select>
									</div>

									<div class="filter-field">
										<label class="form-label" for="dashboard-office">Office</label>
										<select id="dashboard-office" name="office" class="form-select">
											<option value="">All Offices</option>
											@foreach(($officeOptions ?? []) as $officeOption)
												<option value="{{ $officeOption->office_id }}" {{ ((int) ($selectedOfficeFilter ?? 0) === (int) $officeOption->office_id) ? 'selected' : '' }}>
													{{ $officeOption->office_name }}
												</option>
											@endforeach
										</select>
									</div>

									<div class="filter-field">
										<label class="form-label" for="dashboard-visitor_type">Visitor Type</label>
										<select id="dashboard-visitor_type" name="visitor_type" class="form-select">
											<option value="">All Types</option>
											@foreach(($visitTypeOptions ?? []) as $visitTypeOption)
												<option value="{{ $visitTypeOption->visit_type_id }}" {{ ((int) ($selectedVisitorTypeFilter ?? 0) === (int) $visitTypeOption->visit_type_id) ? 'selected' : '' }}>
													{{ $visitTypeOption->visit_type_name }}
												</option>
											@endforeach
										</select>
									</div>

									<div class="filter-field">
										<label class="form-label" for="dashboard-status">Status</label>
										<select id="dashboard-status" name="status" class="form-select">
											<option value="">All Status</option>
											@foreach(($statusOptions ?? []) as $statusOption)
												<option value="{{ $statusOption }}" {{ strtolower((string) ($selectedStatusFilter ?? '')) === strtolower((string) $statusOption) ? 'selected' : '' }}>
													{{ $statusOption }}
												</option>
											@endforeach
										</select>
									</div>

									<div class="filter-actions">
										<a href="/admin/dashboard" class="btn btn-outline-secondary">Reset</a>
										<button type="submit" class="btn btn-primary">Apply Filters</button>
									</div>
								</form>
@if(($selectedDateFilter ?? '') !== '' || ($selectedOfficeFilter ?? 0) > 0 || ($selectedVisitorTypeFilter ?? 0) > 0 || ($selectedStatusFilter ?? '') !== '')
<div class="active-filter-note"><i class="bi bi-check-circle" aria-hidden="true"></i> Filters applied to visitor and alert data. Login security statistics remain today's totals.</div>
@endif</section>
				<div class="row g-4 mb-4">
					<div class="col-12">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center mb-3">
									<h5 class="fw-semibold mb-0">Alerts Summary</h5>
									<a href="/admin/alerts" class="btn btn-sm btn-outline-primary rounded-3">
										View All Alerts
									</a>
								</div>

								<div class="severity-grid">
@foreach([
 ['Critical', $criticalAlerts ?? 0, 'red', 'exclamation-octagon-fill'],
 ['High', $highAlerts ?? 0, 'amber', 'exclamation-triangle-fill'],
 ['Medium', $mediumAlerts ?? 0, 'blue', 'info-circle-fill'],
 ['Low', $lowAlerts ?? 0, 'gray', 'info-circle-fill'],
] as [$severity, $count, $tone, $icon])
@php($share = ($totalAlertsToday ?? 0) > 0 ? round($count / $totalAlertsToday * 100, 1) : 0)
<a href="{{ url('/admin/alerts') . '?severity=' . $severity }}" class="severity-card tone-{{ $tone }}">
<div class="metric-body"><span class="metric-icon" aria-hidden="true"><i class="bi bi-{{ $icon }}"></i></span><div><div class="small text-muted">{{ $severity }} Alerts</div><div class="severity-count">{{ $count }}</div></div></div>
<div class="severity-share">{{ $share }}% of matching alerts</div>
<progress class="severity-progress" value="{{ $count }}" max="{{ max(1, $totalAlertsToday ?? 0) }}" aria-label="{{ $severity }} share of matching alerts">{{ $share }}%</progress>
</a>
@endforeach
</div>
<div class="row mt-1 g-3">
									<div class="col-md-4">
										<div class="mini-summary-box metric-body"><span class="metric-icon tone-purple" aria-hidden="true"><i class="bi bi-shield-fill-check"></i></span><div><div class="small text-muted">Total Alerts Today</div><div class="fw-bold fs-4">{{ $totalAlertsToday ?? 0 }}</div></div></div>
									</div>
									<div class="col-md-4">
										<div class="mini-summary-box metric-body"><span class="metric-icon tone-red" aria-hidden="true"><i class="bi bi-exclamation-triangle-fill"></i></span><div><div class="small text-muted">Unresolved Alerts</div><div class="fw-bold fs-4 text-danger">{{ $unresolvedAlerts ?? 0 }}</div></div></div>
									</div>
									<div class="col-md-4">
										<div class="mini-summary-box metric-body"><span class="metric-icon tone-blue" aria-hidden="true"><i class="bi bi-file-earmark-text-fill"></i></span><div><div class="small text-muted">Most Common Alert</div><div class="fw-bold fs-6">{{ $mostCommonAlert ?? 'N/A' }}</div></div></div>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>

				<div class="row g-4 mb-4">
					<div class="col-xl-6">
						<div class="dash-chart-card">
							<div class="card-body">
								<h4 class="dash-chart-title">7-Day Visitor Trend</h4>
								<div class="dash-chart-canvas is-tall">
									<canvas id="visitorTrendChart" role="img" aria-label="7-Day Visitor Trend">7-Day Visitor Trend</canvas>
@if(array_sum($visitorTrendData ?? []) === 0)<p class="chart-empty">No visitor data for this chart.</p>@endif
								</div>
							</div>
						</div>
					</div>

					<div class="col-xl-6">
						<div class="dash-chart-card">
							<div class="card-body">
								<h4 class="dash-chart-title">Visitors by Status</h4>
								<div class="dash-chart-canvas is-tall">
									<canvas id="visitorStatusChart" role="img" aria-label="Visitors by Status">Visitors by Status</canvas>
@if(array_sum($visitorStatusData ?? []) === 0)<p class="chart-empty">No visitor data for this chart.</p>@endif
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row g-4 mb-4">
					<div class="col-xl-6">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center mb-3">
									<h5 class="fw-semibold mb-0">Real-Time Visitor List</h5>
									<div class="d-flex align-items-center gap-2"><span class="badge bg-success">Live</span><a href="/admin/visitor" class="btn btn-sm btn-outline-primary">View All</a></div>
								</div>

								<div class="table-responsive">
									<table class="table align-middle">
										<thead class="table-light">
											<tr>
												<th>Visitor</th>
												<th>Status</th>
												<th>Location</th>
												<th>Time In</th>
											</tr>
										</thead>
										<tbody>
											@forelse($liveVisitors as $visitor)
												<tr>
													<td class="fw-medium">{{ $visitor['name'] }}</td>
													<td>
														@if($visitor['status'] === 'Inside')
															<span class="badge bg-success">Inside</span>
														@elseif($visitor['status'] === 'Exited')
															<span class="badge bg-secondary">Exited</span>
														@else
															<span class="badge bg-secondary">{{ $visitor['status'] }}</span>
														@endif
													</td>
													<td>{{ $visitor['location'] }}</td>
													<td>{{ $visitor['time_in'] }}</td>
												</tr>
											@empty
												<tr>
													<td colspan="4" class="text-center text-muted">No visitors match the current filters.</td>
												</tr>
											@endforelse
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>

					<div class="col-xl-6">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center mb-3">
									<h5 class="fw-semibold mb-0">Recent Alerts</h5>
									<a href="/admin/alerts" class="btn btn-sm btn-outline-primary">View All</a>
								</div>

								<div class="table-responsive">
									<table class="table align-middle">
										<thead class="table-light">
											<tr>
												<th>Time</th>
												<th>Visitor</th>
												<th>Alert Type</th>
												<th>Severity</th>
												<th>Status</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
											@forelse($recentAlerts as $alert)
												<tr>
													<td>{{ $alert['time'] }}</td>
													<td class="fw-medium">{{ $alert['visitor'] }}</td>
													<td>{{ $alert['type'] }}</td>
													<td>
														@if($alert['severity'] === 'Critical')
															<span class="badge bg-danger">Critical</span>
														@elseif($alert['severity'] === 'High')
															<span class="badge bg-warning text-dark">High</span>
														@elseif($alert['severity'] === 'Medium')
															<span class="badge bg-info text-dark">Medium</span>
														@else
															<span class="badge bg-secondary">Low</span>
														@endif
													</td>
													<td>
														@if($alert['status'] === 'Resolved')
															<span class="badge bg-success">Resolved</span>
														@else
															<span class="badge bg-danger">Unresolved</span>
														@endif
													</td>
													<td>
														<a href="{{ url('/admin/alerts') . '?alert_id=' . ($alert['alert_id'] ?? 0) }}" class="btn btn-sm btn-outline-dark">View</a>
													</td>
												</tr>
											@empty
												<tr>
													<td colspan="6" class="text-center text-muted">No recent alerts found.</td>
												</tr>
											@endforelse
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row g-4 mb-4">
					<div class="col-xl-6">
						<div class="dash-chart-card">
							<div class="card-body">
								<h4 class="dash-chart-title">Visitors by Hour</h4>
								<div class="dash-chart-canvas">
									<canvas id="visitorHourChart" role="img" aria-label="Visitors by Hour">Visitors by Hour</canvas>
@if(array_sum($visitorHourData ?? []) === 0)<p class="chart-empty">No visitor data for this chart.</p>@endif
								</div>
							</div>
						</div>
					</div>

					<div class="col-xl-6">
						<div class="dash-chart-card">
							<div class="card-body">
								<h4 class="dash-chart-title">Visitors by Office</h4>
								<div class="dash-chart-canvas">
									<canvas id="visitorOfficeChart" role="img" aria-label="Visitors by Office">Visitors by Office</canvas>
@if(array_sum($visitorOfficeData ?? []) === 0)<p class="chart-empty">No visitor data for this chart.</p>@endif
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12">
						<div class="card shadow-sm border-0 rounded-4">
							<div class="card-body">
								<h4 class="dash-chart-title">Key Insights</h4>
								<div class="dashboard-insights">
                                    <article class="insight-tile">
                                        <span class="metric-icon tone-blue" aria-hidden="true"><i class="bi bi-clock-fill"></i></span>
                                        <div>
                                            <h5>Peak Visitor Hour</h5>
                                            <div class="insight-value">{{ $peakVisitorHourValue ?? 'No entries yet' }}</div>
                                            <p title="{{ $peakVisitorHourInsight ?? '' }}">Entry activity today</p>
                                        </div>
                                    </article>
                                    <article class="insight-tile">
                                        <span class="metric-icon tone-purple" aria-hidden="true"><i class="bi bi-buildings-fill"></i></span>
                                        <div>
                                            <h5>Busiest Office</h5>
                                            <div class="insight-value">{{ $topOfficeTodayValue ?? 'No office visits yet' }}</div>
                                            <p title="{{ $topOfficeTodayInsight ?? '' }}">Office visits today</p>
                                        </div>
                                    </article>
                                    <article class="insight-tile">
                                        <span class="metric-icon tone-red" aria-hidden="true"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                        <div>
                                            <h5>Unresolved Alerts</h5>
                                            <div class="insight-value">{{ $unresolvedAlerts ?? '0' }}</div>
                                            <p title="{{ $unresolvedAlertsInsight ?? '' }}">Alerts needing review</p>
                                        </div>
                                    </article>
                                    <article class="insight-tile">
                                        <span class="metric-icon tone-blue" aria-hidden="true"><i class="bi bi-people-fill"></i></span>
                                        <div>
                                            <h5>Longest Average Visit</h5>
                                            <div class="insight-value">{{ $longestAvgDurationValue ?? 'No completed visits yet' }}</div>
                                            <p title="{{ $longestAvgDurationInsight ?? '' }}">Completed visit durations</p>
                                        </div>
                                    </article>
                                </div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</main>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script nonce="{{ $cspNonce }}">


		const userMenuGroup = document.getElementById('userMenuGroup');
		const userMenuToggle = document.getElementById('userMenuToggle');

		if (userMenuGroup && userMenuToggle) {
			userMenuToggle.addEventListener('click', () => {
				const isOpen = userMenuGroup.classList.toggle('open');
				userMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		}

		const trendLabels = @json($visitorTrendLabels ?? []);
		const trendData = @json($visitorTrendData ?? []);
		const statusLabels = @json($visitorStatusLabels ?? ['Currently Inside', 'Exited']);
		const statusData = @json($visitorStatusData ?? [0, 0]);
		const hourLabels = @json($visitorHourLabels ?? []);
		const hourData = @json($visitorHourData ?? []);
		const officeLabels = @json($visitorOfficeLabels ?? []);
		const officeData = @json($visitorOfficeData ?? []);

		const chartColors = {
			primary: '#273b9e',
			primarySoft: 'rgba(39, 59, 158, 0.14)',
			primaryMuted: '#7c89d4',
			inside: '#243c96',
			exited: '#c7d2fe',
			grid: '#eef2f7',
			tick: '#64748b',
			tooltipBg: '#0f172a',
		};

		const maxInt = (values, fallback = 1) => Math.max(fallback, ...values.map((v) => Number(v) || 0));

		const integerTicks = {
			precision: 0,
			maxTicksLimit: 6,
			color: chartColors.tick,
			font: { size: 11, weight: '500' },
			callback: (value) => (Number.isInteger(value) ? value : null),
		};

		const axisGrid = {
			color: chartColors.grid,
			drawBorder: false,
		};

		const sharedTooltip = {
			backgroundColor: chartColors.tooltipBg,
			titleFont: { size: 12, weight: '600' },
			bodyFont: { size: 12 },
			padding: 10,
			cornerRadius: 8,
			displayColors: false,
		};

		const trendCtx = document.getElementById('visitorTrendChart')?.getContext('2d');
		if (trendCtx) {
			const trendGradient = trendCtx.createLinearGradient(0, 0, 0, 280);
			trendGradient.addColorStop(0, 'rgba(39, 59, 158, 0.28)');
			trendGradient.addColorStop(1, 'rgba(39, 59, 158, 0.02)');

			new Chart(trendCtx, {
				type: 'line',
				data: {
					labels: trendLabels,
					datasets: [{
						label: 'Visitors',
						data: trendData,
						borderColor: chartColors.primary,
						backgroundColor: trendGradient,
						borderWidth: 2.5,
						tension: 0.35,
						fill: true,
						pointRadius: 4,
						pointHoverRadius: 6,
						pointBackgroundColor: '#fff',
						pointBorderColor: chartColors.primary,
						pointBorderWidth: 2,
						pointHoverBackgroundColor: chartColors.primary,
						pointHoverBorderColor: '#fff',
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					interaction: { mode: 'index', intersect: false },
					plugins: {
						legend: { display: false },
						tooltip: {
							...sharedTooltip,
							callbacks: {
								label: (ctx) => `${ctx.parsed.y} visitor${ctx.parsed.y === 1 ? '' : 's'}`,
							},
						},
					},
					scales: {
						x: {
							grid: { display: false, drawBorder: false },
							ticks: { color: chartColors.tick, font: { size: 11 } },
						},
						y: {
							beginAtZero: true,
							suggestedMax: maxInt(trendData),
							grid: axisGrid,
							border: { display: false },
							ticks: integerTicks,
						},
					},
				},
			});
		}

		const statusCtx = document.getElementById('visitorStatusChart')?.getContext('2d');
		if (statusCtx) {
			const statusTotal = statusData.reduce((sum, value) => sum + (Number(value) || 0), 0);

			new Chart(statusCtx, {
				type: 'doughnut',
				data: {
					labels: statusLabels,
					datasets: [{
						data: statusData,
						backgroundColor: [chartColors.inside, chartColors.exited],
						hoverBackgroundColor: ['#1e327f', '#a5b4fc'],
						borderWidth: 3,
						borderColor: '#fff',
						hoverOffset: 4,
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					cutout: '68%',
                    onResize(chart, size) { chart.options.plugins.legend.position = size.width < 430 ? 'bottom' : 'right'; },
					plugins: {
						legend: {
							position: window.innerWidth < 600 ? 'bottom' : 'right',
							labels: {
								boxWidth: 12,
								boxHeight: 12,
								borderRadius: 3,
								useBorderRadius: true,
								padding: 16,
								color: '#334155',
								font: { size: 12, weight: '600' },
								generateLabels: (chart) => {
									const data = chart.data;
									const dataset = data.datasets[0] || {};
									return (data.labels || []).map((label, index) => {
										const value = Number((dataset.data || [])[index] || 0);
										const pct = statusTotal > 0 ? Math.round((value / statusTotal) * 100) : 0;
										return {
											text: `${label}  ${value} (${pct}%)`,
											fillStyle: (dataset.backgroundColor || [])[index],
											strokeStyle: '#fff',
											lineWidth: 0,
											hidden: !chart.getDataVisibility(index),
											index,
										};
									});
								},
							},
						},
						tooltip: {
							...sharedTooltip,
							displayColors: true,
							callbacks: {
								label: (ctx) => {
									const value = Number(ctx.parsed || 0);
									const pct = statusTotal > 0 ? Math.round((value / statusTotal) * 100) : 0;
									return ` ${ctx.label}: ${value} (${pct}%)`;
								},
							},
						},
					},
				},
				plugins: [{
					id: 'statusCenterText',
					afterDraw(chart) {
						const { ctx: c, chartArea } = chart;
						if (!chartArea) return;
						const cx = (chartArea.left + chartArea.right) / 2;
						const cy = (chartArea.top + chartArea.bottom) / 2;
						c.save();
						c.textAlign = 'center';
						c.textBaseline = 'middle';
						c.fillStyle = '#0f172a';
						c.font = '700 22px Inter, system-ui, sans-serif';
						c.fillText(String(statusTotal), cx, cy - 8);
						c.fillStyle = '#64748b';
						c.font = '600 11px Inter, system-ui, sans-serif';
						c.fillText('Total', cx, cy + 12);
						c.restore();
					},
				}],
			});
		}

		const hourCtx = document.getElementById('visitorHourChart')?.getContext('2d');
		if (hourCtx) {
			new Chart(hourCtx, {
				type: 'bar',
				data: {
					labels: hourLabels,
					datasets: [{
						label: 'Visitors',
						data: hourData,
						backgroundColor: chartColors.primary,
						hoverBackgroundColor: chartColors.primaryMuted,
						borderRadius: 6,
						borderSkipped: false,
						maxBarThickness: 18,
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					interaction: { mode: 'index', intersect: false },
					plugins: {
						legend: { display: false },
						tooltip: {
							...sharedTooltip,
							callbacks: {
								title: (items) => items[0]?.label || '',
								label: (ctx) => `${ctx.parsed.y} visitor${ctx.parsed.y === 1 ? '' : 's'}`,
							},
						},
					},
					scales: {
						x: {
							grid: { display: false, drawBorder: false },
							ticks: {
								color: chartColors.tick,
								font: { size: 10 },
								maxRotation: 0,
								minRotation: 0,
								autoSkip: true,
								maxTicksLimit: 12,
							},
						},
						y: {
							beginAtZero: true,
							suggestedMax: maxInt(hourData),
							grid: axisGrid,
							border: { display: false },
							ticks: integerTicks,
						},
					},
				},
			});
		}

		const officeCtx = document.getElementById('visitorOfficeChart')?.getContext('2d');
		if (officeCtx) {
            officeCtx.canvas.parentElement.style.height = `${Math.max(220, officeLabels.length * 34 + 40)}px`;
			new Chart(officeCtx, {
				type: 'bar',
				data: {
					labels: officeLabels,
					datasets: [{
						label: 'Visitors',
						data: officeData,
						backgroundColor: chartColors.primary,
						hoverBackgroundColor: chartColors.primaryMuted,
						borderRadius: 8,
						borderSkipped: false,
						maxBarThickness: 26,
					}]
				},
				options: {
					indexAxis: 'y',
					responsive: true,
					maintainAspectRatio: false,
					layout: { padding: { right: 30 } },
					plugins: {
						legend: { display: false },
						tooltip: {
							...sharedTooltip,
							callbacks: {
								label: (ctx) => `${ctx.parsed.x} visitor${ctx.parsed.x === 1 ? '' : 's'}`,
							},
						},
					},
					scales: {
						x: {
							beginAtZero: true,
							suggestedMax: maxInt(officeData),
							grid: axisGrid,
							border: { display: false },
							ticks: integerTicks,
						},
						y: {
							grid: { display: false, drawBorder: false },
							ticks: {
								color: '#334155',
								font: { size: 11, weight: '600' },
                            autoSkip: false,
                            callback(value) { const label = this.getLabelForValue(value); return label.length > 28 ? label.slice(0, 27) + '…' : label; },
							},
						},
					},
				},
				plugins: [{
					id: 'officeVisitorCounts',
					afterDatasetsDraw(chart) {
						const ctx = chart.ctx;
						ctx.save();
						ctx.font = '600 11px system-ui, sans-serif';
						ctx.fillStyle = '#42547d';
						ctx.textAlign = 'left';
						ctx.textBaseline = 'middle';
						chart.getDatasetMeta(0).data.forEach((bar, index) => {
							ctx.fillText(String(officeData[index] ?? 0), bar.x + 6, bar.y);
						});
						ctx.restore();
					},
				}],
			});
		}
	</script>
	@include('partials.live-auto-refresh')
	@include('admin.partials.admin-responsive-script')
</body>
</html>
