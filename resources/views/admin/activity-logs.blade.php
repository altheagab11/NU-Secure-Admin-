<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Activity Logs</title>
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

		.main {
			flex: 1;
			background: #f7f8ff;
			padding: 24px 32px;
			margin-left: 260px;
			min-height: 100vh;
		}

		.page-subtitle {
			margin: 4px 0 0;
			color: #64748b;
			font-size: 14px;
		}

		.stat-card,
		.filters-card,
		.table-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		}

		.stat-card {
			padding: 16px 18px;
			height: 100%;
		}

		.stat-card .stat-label {
			font-size: 13px;
			color: #64748b;
			margin-bottom: 6px;
		}

		.stat-card .stat-value {
			font-size: 28px;
			font-weight: 700;
			color: #0f172a;
			margin: 0;
			line-height: 1.2;
			word-break: break-word;
		}

		.filters-card {
			padding: 20px;
			margin-top: 16px;
		}

		.filters-grid {
			display: grid;
			grid-template-columns: repeat(6, minmax(0, 1fr));
			gap: 10px;
			align-items: end;
		}

		.filters-actions {
			display: flex;
			gap: 8px;
			flex-wrap: wrap;
			margin-top: 12px;
		}

		.custom-range-row {
			display: none;
			grid-template-columns: repeat(2, minmax(0, 220px));
			gap: 10px;
			margin-top: 12px;
		}

		.custom-range-row.is-visible { display: grid; }

		.filters-label {
			margin: 0 0 12px;
			font-size: 18px;
			font-weight: 700;
			color: #111827;
		}

		.filter-input, .filter-select {
			height: 38px;
			border: 1px solid #d6dde8;
			outline: none;
			border-radius: 8px;
			padding: 0 12px;
			width: 100%;
			background: #fff;
		}

		.filter-input:focus, .filter-select:focus {
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

		.search-wrap {
			margin-top: 14px;
			position: relative;
		}

		.search-wrap i {
			position: absolute;
			left: 12px;
			top: 50%;
			transform: translateY(-50%);
			color: #94a3b8;
		}

		.search-wrap input {
			padding-left: 36px;
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

		.table thead th.sortable {
			cursor: pointer;
			user-select: none;
		}

		.table thead th.sortable:hover { color: #39459a; }

		.log-datetime {
			white-space: nowrap;
		}

		.log-datetime small {
			display: block;
			color: #64748b;
		}

		.log-user {
			font-weight: 600;
			color: #0f172a;
		}

		.log-role {
			display: block;
			font-size: 12px;
			color: #64748b;
			font-weight: 500;
		}

		.log-description {
			max-width: 280px;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
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

		.status-success { background: #dcfce7; color: #166534; }
		.status-failed { background: #fee2e2; color: #991b1b; }
		.status-warning { background: #fef3c7; color: #92400e; }

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

		.refresh-meta {
			display: flex;
			align-items: center;
			gap: 10px;
			color: #64748b;
			font-size: 13px;
		}

		.activity-pagination-bar {
			border: 0;
			border-top: 1px solid #e8ecf1;
			border-radius: 0 0 12px 12px;
			margin-top: 0;
			background: #fff;
		}

		.diff-changed {
			background: #fff7ed;
		}

		.detail-section h6 {
			font-size: 13px;
			font-weight: 700;
			letter-spacing: 0.04em;
			text-transform: uppercase;
			color: #39459a;
			margin: 0 0 10px;
		}

		.detail-grid {
			display: grid;
			grid-template-columns: 140px 1fr;
			gap: 6px 12px;
			font-size: 14px;
			margin-bottom: 18px;
		}

		.detail-grid dt { color: #64748b; font-weight: 600; margin: 0; }
		.detail-grid dd { margin: 0; color: #0f172a; word-break: break-word; }

		.offcanvas { width: min(520px, 100vw); }

		@media (max-width: 1400px) {
			.filters-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
		}

		@media (max-width: 1200px) {
			.layout { min-width: 0; }
			.filters-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
		}

		@media (max-width: 992px) {
			.main {
				margin-left: 0;
				padding: 16px;
			}

			.layout { min-width: 0; }
			.filters-grid { grid-template-columns: 1fr; }
			.custom-range-row.is-visible { grid-template-columns: 1fr; }
		}

		@include('admin.partials.table-pagination-styles')
		@include('admin.partials.admin-topbar-styles')
		@include('admin.partials.admin-responsive-styles')
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
					@include('admin.partials.sidebar-guard-duty-link')
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

		<main class="main">
			@include('admin.partials.admin-topbar', ['title' => 'Activity Logs'])

			<div class="toolbar mb-3">
				<div>
					<p class="page-subtitle mb-0">Monitor and review system activities performed by users.</p>
				</div>
				<div class="refresh-meta">
					<span id="lastUpdatedLabel">Last updated: just now</span>
					<button type="button" class="btn btn-outline-primary btn-sm" id="refreshLogsBtn">
						<i class="bi bi-arrow-clockwise me-1"></i> Refresh
					</button>
				</div>
			</div>

			<div id="pageError" class="alert alert-danger d-none" role="alert"></div>

			<div class="row g-3">
				<div class="col-md-6 col-xl-3">
					<div class="stat-card">
						<div class="stat-label">Total Activities Today</div>
						<p class="stat-value" id="summaryTotal">—</p>
					</div>
				</div>
				<div class="col-md-6 col-xl-3">
					<div class="stat-card">
						<div class="stat-label">Active Users Today</div>
						<p class="stat-value" id="summaryUsers">—</p>
					</div>
				</div>
				<div class="col-md-6 col-xl-3">
					<div class="stat-card">
						<div class="stat-label">Failed Activities</div>
						<p class="stat-value" id="summaryFailed">—</p>
					</div>
				</div>
				<div class="col-md-6 col-xl-3">
					<div class="stat-card">
						<div class="stat-label">Most Active Module</div>
						<p class="stat-value" id="summaryModule">—</p>
					</div>
				</div>
			</div>

			<div class="filters-card">
				<p class="filters-label">Search &amp; Filter</p>
				<form id="activityFilterForm" data-prevent-submit>
					<div class="filters-grid">
						<div>
							<label class="field-label" for="date_range">Date Range</label>
							<select id="date_range" class="filter-select">
								<option value="today">Today</option>
								<option value="yesterday">Yesterday</option>
								<option value="last_7_days">Last 7 Days</option>
								<option value="last_30_days">Last 30 Days</option>
								<option value="custom">Custom Range</option>
								<option value="all" selected>All</option>
							</select>
						</div>
						<div>
							<label class="field-label" for="user_id">User</label>
							<select id="user_id" class="filter-select">
								<option value="">All Users</option>
							</select>
						</div>
						<div>
							<label class="field-label" for="role_id">Role</label>
							<select id="role_id" class="filter-select">
								<option value="">All Roles</option>
								<option value="1">Admin</option>
								<option value="2">Guard</option>
								<option value="3">Office Staff</option>
								<option value="system">System</option>
							</select>
						</div>
						<div>
							<label class="field-label" for="module">Module</label>
							<select id="module" class="filter-select">
								<option value="">All Modules</option>
							</select>
						</div>
						<div>
							<label class="field-label" for="action">Action</label>
							<select id="action" class="filter-select">
								<option value="">All Actions</option>
							</select>
						</div>
						<div>
							<label class="field-label" for="status">Status</label>
							<select id="status" class="filter-select">
								<option value="">All Status</option>
								<option value="Success">Success</option>
								<option value="Failed">Failed</option>
								<option value="Warning">Warning</option>
							</select>
						</div>
					</div>
					<div class="custom-range-row" id="customRangeRow">
						<div>
							<label class="field-label" for="date_from">From</label>
							<input type="date" id="date_from" class="filter-input">
						</div>
						<div>
							<label class="field-label" for="date_to">To</label>
							<input type="date" id="date_to" class="filter-input">
						</div>
					</div>
					<div class="search-wrap">
						<i class="bi bi-search"></i>
						<input type="search" id="search" class="filter-input" placeholder="Search activity logs...">
					</div>
					<div class="filters-actions">
						<button type="button" class="btn btn-outline-secondary" id="resetFiltersBtn">Reset</button>
						<button type="button" class="btn btn-primary" id="applyFiltersBtn">Apply Filters</button>
					</div>
				</form>
			</div>

			<div class="table-card">
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead>
							<tr>
								<th class="sortable" data-sort="created_at">Date &amp; Time</th>
								<th class="sortable" data-sort="user">User</th>
								<th>Role</th>
								<th class="sortable" data-sort="module">Module</th>
								<th class="sortable" data-sort="action">Action</th>
								<th>Description</th>
								<th>IP Address</th>
								<th class="sortable" data-sort="status">Status</th>
								<th>Details</th>
							</tr>
						</thead>
						<tbody id="activityLogsBody">
							<tr>
								<td colspan="9" class="text-center text-muted py-4">Loading activity logs...</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="table-pagination-bar activity-pagination-bar" id="activityPagination" role="navigation" aria-label="Activity logs pagination">
					<div class="table-pagination-left">
						<label class="table-pagination-label" for="activityPageSize">Page size:</label>
						<select id="activityPageSize" class="table-page-size" aria-label="Page size">
							<option value="5" selected>5</option>
							<option value="10">10</option>
							<option value="25">25</option>
							<option value="50">50</option>
							<option value="75">75</option>
							<option value="100">100</option>
						</select>
						<span class="table-pagination-range" id="activityPaginationRange">0 to 0 of 0</span>
					</div>
					<div class="table-pagination-right">
						<button type="button" class="table-pagination-nav is-disabled" id="activityPaginationFirst" aria-label="First page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 6L5 12l6 6M19 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
						<button type="button" class="table-pagination-nav is-disabled" id="activityPaginationPrev" aria-label="Previous page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
						<span class="table-pagination-page" id="activityPaginationPageLabel">Page <strong>1</strong> of 1</span>
						<button type="button" class="table-pagination-nav is-disabled" id="activityPaginationNext" aria-label="Next page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
						<button type="button" class="table-pagination-nav is-disabled" id="activityPaginationLast" aria-label="Last page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6l6 6-6 6M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
					</div>
				</div>
			</div>
		</main>
	</div>

	<div class="offcanvas offcanvas-end" tabindex="-1" id="activityLogDrawer" aria-labelledby="activityLogDrawerLabel">
		<div class="offcanvas-header border-bottom">
			<h5 class="offcanvas-title" id="activityLogDrawerLabel">Activity Details</h5>
			<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body" id="activityLogDrawerBody"></div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	<script nonce="{{ $cspNonce }}">
		(function () {
			const listUrl = @json(route('api.admin.activity-logs'));
			const summaryUrl = @json(route('api.admin.activity-logs.summary'));
			const filtersUrl = @json(route('api.admin.activity-logs.filters'));
			const showUrlBase = @json(url('/api/admin/activity-logs'));

			const state = {
				page: 1,
				lastPage: 1,
				perPage: 5,
				sortBy: 'created_at',
				sortDirection: 'desc',
				searchTimer: null,
				refreshTimer: null,
				lastUpdatedAt: Date.now(),
				drawerOpen: false,
			};

			const els = {
				body: document.getElementById('activityLogsBody'),
				pagination: document.getElementById('activityPagination'),
				pageSize: document.getElementById('activityPageSize'),
				range: document.getElementById('activityPaginationRange'),
				pageLabel: document.getElementById('activityPaginationPageLabel'),
				first: document.getElementById('activityPaginationFirst'),
				prev: document.getElementById('activityPaginationPrev'),
				next: document.getElementById('activityPaginationNext'),
				last: document.getElementById('activityPaginationLast'),
				error: document.getElementById('pageError'),
				lastUpdated: document.getElementById('lastUpdatedLabel'),
				dateRange: document.getElementById('date_range'),
				customRange: document.getElementById('customRangeRow'),
				user: document.getElementById('user_id'),
				role: document.getElementById('role_id'),
				module: document.getElementById('module'),
				action: document.getElementById('action'),
				status: document.getElementById('status'),
				search: document.getElementById('search'),
				dateFrom: document.getElementById('date_from'),
				dateTo: document.getElementById('date_to'),
				drawerBody: document.getElementById('activityLogDrawerBody'),
			};

			const drawerEl = document.getElementById('activityLogDrawer');
			const drawer = drawerEl ? new bootstrap.Offcanvas(drawerEl) : null;

			function showError(message) {
				els.error.textContent = message;
				els.error.classList.remove('d-none');
			}

			function hideError() {
				els.error.classList.add('d-none');
				els.error.textContent = '';
			}

			function escapeHtml(value) {
				return String(value ?? '')
					.replace(/&/g, '&amp;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;')
					.replace(/"/g, '&quot;');
			}

			function statusClass(status) {
				const normalized = String(status || '').toLowerCase();
				if (normalized === 'failed') return 'status-failed';
				if (normalized === 'warning') return 'status-warning';
				return 'status-success';
			}

			function queryParams() {
				const params = new URLSearchParams({
					page: String(state.page),
					per_page: String(state.perPage),
					sort_by: state.sortBy,
					sort_direction: state.sortDirection,
					date_range: els.dateRange.value || 'all',
				});
				if (els.search.value.trim()) params.set('search', els.search.value.trim());
				if (els.user.value) params.set('user_id', els.user.value);
				if (els.role.value) params.set('role_id', els.role.value);
				if (els.module.value) params.set('module', els.module.value);
				if (els.action.value) params.set('action', els.action.value);
				if (els.status.value) params.set('status', els.status.value);
				if (els.dateRange.value === 'custom') {
					if (els.dateFrom.value) params.set('date_from', els.dateFrom.value);
					if (els.dateTo.value) params.set('date_to', els.dateTo.value);
				}
				return params;
			}

			function toggleCustomRange() {
				els.customRange.classList.toggle('is-visible', els.dateRange.value === 'custom');
			}

			async function fetchJson(url) {
				const response = await fetch(url, {
					headers: { Accept: 'application/json' },
					credentials: 'same-origin',
					cache: 'no-store',
				});
				const payload = await response.json().catch(() => null);
				if (!response.ok || !payload || payload.success === false) {
					throw new Error((payload && payload.message) || 'Unable to load activity logs. Please try again.');
				}
				return payload;
			}

			function renderSummary(data) {
				document.getElementById('summaryTotal').textContent = Number(data.total_today || 0).toLocaleString();
				document.getElementById('summaryUsers').textContent = Number(data.active_users_today || 0).toLocaleString();
				document.getElementById('summaryFailed').textContent = Number(data.failed_today || 0).toLocaleString();
				document.getElementById('summaryModule').textContent = data.most_active_module || '—';
			}

			function fillSelect(select, items, allLabel, valueKey, labelKey) {
				const current = select.value;
				select.innerHTML = '';
				const all = document.createElement('option');
				all.value = '';
				all.textContent = allLabel;
				select.appendChild(all);
				items.forEach(function (item) {
					const option = document.createElement('option');
					if (typeof item === 'string') {
						option.value = item;
						option.textContent = item;
					} else {
						option.value = String(item[valueKey]);
						option.textContent = item[labelKey];
					}
					select.appendChild(option);
				});
				if ([...select.options].some((opt) => opt.value === current)) {
					select.value = current;
				}
			}

			function renderRows(payload) {
				const rows = payload.data || [];
				if (!rows.length) {
					const filtered = payload.empty_reason === 'filtered';
					els.body.innerHTML = `
						<tr>
							<td colspan="9">
								<div class="empty-state">
									<i class="bi bi-clipboard-data"></i>
									<h5 class="mb-1">${filtered ? 'No activities match your selected filters.' : 'No activity logs found.'}</h5>
									<p class="mb-0">${filtered ? 'Try adjusting the filters or search terms.' : 'Activities performed by system users will appear here.'}</p>
								</div>
							</td>
						</tr>
					`;
					renderPagination(payload.meta || {});
					return;
				}

				els.body.innerHTML = rows.map(function (row) {
					return `
						<tr>
							<td class="log-datetime">${escapeHtml(row.date_label)}<small>${escapeHtml(row.time_label)}</small></td>
							<td><span class="log-user">${escapeHtml(row.user_name)}</span><span class="log-role">${escapeHtml(row.role)}</span></td>
							<td>${escapeHtml(row.role)}</td>
							<td>${escapeHtml(row.module)}</td>
							<td>${escapeHtml(row.action)}</td>
							<td class="log-description" title="${escapeHtml(row.description)}">${escapeHtml(row.description)}</td>
							<td>${escapeHtml(row.ip_address)}</td>
							<td><span class="status-badge ${statusClass(row.status)}">${escapeHtml(row.status)}</span></td>
							<td><button type="button" class="btn btn-sm btn-outline-dark view-log-btn" data-log-id="${row.log_id}">View</button></td>
						</tr>
					`;
				}).join('');
				renderPagination(payload.meta || {});
			}

			function setNavDisabled(button, disabled) {
				if (!button) return;
				button.classList.toggle('is-disabled', disabled);
				button.disabled = disabled;
				button.setAttribute('aria-disabled', disabled ? 'true' : 'false');
			}

			function renderPagination(meta) {
				const total = Number(meta.total || 0);
				const from = Number(meta.from || 0);
				const to = Number(meta.to || 0);
				const page = Number(meta.current_page || 1);
				const last = Math.max(1, Number(meta.last_page || 1));
				state.page = page;
				state.lastPage = last;
				state.perPage = Number(meta.per_page || state.perPage);

				if (els.range) {
					els.range.textContent = from + ' to ' + to + ' of ' + total;
				}
				if (els.pageLabel) {
					els.pageLabel.innerHTML = 'Page <strong>' + page + '</strong> of ' + last;
				}
				if (els.pageSize && els.pageSize.value !== String(state.perPage)) {
					els.pageSize.value = String(state.perPage);
				}

				const onFirst = page <= 1;
				const onLast = page >= last;
				setNavDisabled(els.first, onFirst);
				setNavDisabled(els.prev, onFirst);
				setNavDisabled(els.next, onLast);
				setNavDisabled(els.last, onLast);
			}

			function renderDetail(data) {
				const userBlock = data.performed_by_system
					? `<div class="detail-grid"><dt>Performed By</dt><dd>System</dd></div>`
					: `<div class="detail-grid">
						<dt>Full name</dt><dd>${escapeHtml(data.user.full_name)}</dd>
						<dt>Email</dt><dd>${escapeHtml(data.user.email)}</dd>
						<dt>Role</dt><dd>${escapeHtml(data.user.role)}</dd>
						<dt>User ID</dt><dd>${escapeHtml(data.user.user_id)}</dd>
					</div>`;

				const changes = (data.changes || []);
				const changesHtml = changes.length
					? `<div class="table-responsive"><table class="table table-sm align-middle">
						<thead><tr><th>Field</th><th>Previous</th><th>New</th></tr></thead>
						<tbody>
							${changes.map((row) => `
								<tr class="${row.changed ? 'diff-changed' : ''}">
									<td>${escapeHtml(row.field)}</td>
									<td>${escapeHtml(row.previous)}</td>
									<td>${escapeHtml(row.new)}</td>
								</tr>
							`).join('')}
						</tbody>
					</table></div>`
					: '<p class="text-muted mb-0">No data changes were recorded for this activity.</p>';

				els.drawerBody.innerHTML = `
					<div class="detail-section">
						<h6>Activity Information</h6>
						<div class="detail-grid">
							<dt>Log ID</dt><dd>#${escapeHtml(data.log_id)}</dd>
							<dt>Date</dt><dd>${escapeHtml(data.date_label)}</dd>
							<dt>Time</dt><dd>${escapeHtml(data.time_label)}</dd>
							<dt>Action</dt><dd>${escapeHtml(data.action)}</dd>
							<dt>Module</dt><dd>${escapeHtml(data.module)}</dd>
							<dt>Status</dt><dd><span class="status-badge ${statusClass(data.status)}">${escapeHtml(data.status)}</span></dd>
						</div>
					</div>
					<div class="detail-section">
						<h6>User Information</h6>
						${userBlock}
					</div>
					<div class="detail-section">
						<h6>Request Information</h6>
						<div class="detail-grid">
							<dt>IP Address</dt><dd>${escapeHtml(data.request.ip_address)}</dd>
							<dt>Device / Browser</dt><dd>${escapeHtml(data.request.user_agent)}</dd>
							<dt>Request Method</dt><dd>${escapeHtml(data.request.method)}</dd>
							<dt>Request URL</dt><dd>${escapeHtml(data.request.url)}</dd>
						</div>
					</div>
					<div class="detail-section">
						<h6>Record Information</h6>
						<div class="detail-grid">
							<dt>Entity Type</dt><dd>${escapeHtml(data.record.entity_type)}</dd>
							<dt>Entity ID</dt><dd>${escapeHtml(data.record.entity_id ?? '—')}</dd>
						</div>
					</div>
					<div class="detail-section">
						<h6>Description</h6>
						<p>${escapeHtml(data.description)}</p>
					</div>
					<div class="detail-section">
						<h6>Data Changes</h6>
						${changesHtml}
					</div>
				`;
			}

			async function loadSummary() {
				try {
					const payload = await fetchJson(summaryUrl);
					renderSummary(payload.data || {});
				} catch (error) {
					showError('Unable to load activity summary. Please try again.');
				}
			}

			async function loadFilters() {
				try {
					const payload = await fetchJson(filtersUrl);
					const data = payload.data || {};
					fillSelect(els.user, data.users || [], 'All Users', 'user_id', 'name');
					fillSelect(els.module, data.modules || [], 'All Modules');
					fillSelect(els.action, data.actions || [], 'All Actions');
				} catch (error) {
					showError('Unable to load activity log filters. Please try again.');
				}
			}

			async function loadLogs() {
				try {
					hideError();
					const payload = await fetchJson(listUrl + '?' + queryParams().toString());
					renderRows(payload);
					state.lastUpdatedAt = Date.now();
					els.lastUpdated.textContent = 'Last updated: just now';
				} catch (error) {
					els.body.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-4">${escapeHtml(error.message)}</td></tr>`;
					showError(error.message);
				}
			}

			async function openLog(logId) {
				els.drawerBody.innerHTML = '<p class="text-muted">Loading details...</p>';
				drawer?.show();
				try {
					const payload = await fetchJson(showUrlBase + '/' + logId);
					renderDetail(payload.data);
				} catch (error) {
					els.drawerBody.innerHTML = `<div class="alert alert-danger">${escapeHtml(error.message)}</div>`;
				}
			}

			function resetFilters() {
				els.dateRange.value = 'all';
				els.user.value = '';
				els.role.value = '';
				els.module.value = '';
				els.action.value = '';
				els.status.value = '';
				els.search.value = '';
				els.dateFrom.value = '';
				els.dateTo.value = '';
				state.page = 1;
				toggleCustomRange();
				loadLogs();
			}

			els.dateRange.addEventListener('change', toggleCustomRange);
			document.getElementById('applyFiltersBtn').addEventListener('click', function () {
				state.page = 1;
				loadLogs();
			});
			document.getElementById('resetFiltersBtn').addEventListener('click', resetFilters);
			document.getElementById('refreshLogsBtn').addEventListener('click', function () {
				loadSummary();
				loadLogs();
			});
			els.search.addEventListener('input', function () {
				clearTimeout(state.searchTimer);
				state.searchTimer = setTimeout(function () {
					state.page = 1;
					loadLogs();
				}, 400);
			});
			els.body.addEventListener('click', function (event) {
				const button = event.target.closest('.view-log-btn');
				if (button) openLog(button.getAttribute('data-log-id'));
			});
			if (els.first) {
				els.first.addEventListener('click', function () {
					if (state.page <= 1) return;
					state.page = 1;
					loadLogs();
				});
			}
			if (els.prev) {
				els.prev.addEventListener('click', function () {
					if (state.page <= 1) return;
					state.page -= 1;
					loadLogs();
				});
			}
			if (els.next) {
				els.next.addEventListener('click', function () {
					if (state.page >= state.lastPage) return;
					state.page += 1;
					loadLogs();
				});
			}
			if (els.last) {
				els.last.addEventListener('click', function () {
					if (state.page >= state.lastPage) return;
					state.page = state.lastPage;
					loadLogs();
				});
			}
			if (els.pageSize) {
				els.pageSize.addEventListener('change', function () {
					state.perPage = Number(els.pageSize.value) || 5;
					state.page = 1;
					loadLogs();
				});
			}
			document.querySelectorAll('th.sortable').forEach(function (th) {
				th.addEventListener('click', function () {
					const sortBy = th.getAttribute('data-sort');
					if (state.sortBy === sortBy) {
						state.sortDirection = state.sortDirection === 'asc' ? 'desc' : 'asc';
					} else {
						state.sortBy = sortBy;
						state.sortDirection = sortBy === 'created_at' ? 'desc' : 'asc';
					}
					loadLogs();
				});
			});

			if (drawerEl) {
				drawerEl.addEventListener('shown.bs.offcanvas', function () { state.drawerOpen = true; });
				drawerEl.addEventListener('hidden.bs.offcanvas', function () { state.drawerOpen = false; });
			}

			const toggle = document.getElementById('userMenuToggle');
			const group = document.getElementById('userMenuGroup');
			if (toggle && group) {
				toggle.addEventListener('click', function () {
					const open = group.classList.toggle('open');
					toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
				});
			}

			setInterval(function () {
				const elapsed = Math.round((Date.now() - state.lastUpdatedAt) / 1000);
				if (elapsed < 10) {
					els.lastUpdated.textContent = 'Last updated: just now';
				} else if (elapsed < 60) {
					els.lastUpdated.textContent = 'Last updated: ' + elapsed + ' seconds ago';
				} else {
					els.lastUpdated.textContent = 'Last updated: ' + Math.floor(elapsed / 60) + ' min ago';
				}
			}, 5000);

			setInterval(function () {
				if (document.hidden || state.drawerOpen) return;
				loadSummary();
				loadLogs();
			}, 45000);

			toggleCustomRange();
			loadFilters();
			loadSummary();
			loadLogs();
		})();
	</script>
	@include('admin.partials.admin-responsive-script')
</body>
</html>
