<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Guard Duty Monitoring</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<style nonce="{{ $cspNonce }}">
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

		.section-title {
			margin: 0 0 12px;
			font-size: 18px;
			font-weight: 700;
			color: #111827;
		}

		.duty-card,
		.warning-card,
		.filters-card,
		.table-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		}

		.current-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(min(320px, 100%), 1fr));
			gap: 12px;
			margin-bottom: 24px;
		}

		.duty-card {
			padding: 18px 20px;
		}

		.duty-status {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			font-size: 12px;
			font-weight: 800;
			letter-spacing: 0.06em;
			text-transform: uppercase;
			color: #15803d;
			margin-bottom: 12px;
		}

		.duty-status::before {
			content: "";
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: #22c55e;
			box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.16);
		}

		.duty-name {
			margin: 0;
			font-size: 22px;
			font-weight: 800;
			color: #0f172a;
		}

		.duty-meta {
			margin: 4px 0 16px;
			color: #64748b;
			font-size: 14px;
		}

		.duty-stats {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 12px;
		}

		.duty-stat-label {
			display: block;
			font-size: 12px;
			font-weight: 600;
			color: #64748b;
			margin-bottom: 4px;
		}

		.duty-stat-value {
			margin: 0;
			font-size: 15px;
			font-weight: 700;
			color: #0f172a;
		}

		.warning-card {
			padding: 20px;
			border-color: #fde68a;
			background: #fffbeb;
			margin-bottom: 24px;
		}

		.warning-card h3 {
			margin: 0 0 8px;
			font-size: 18px;
			font-weight: 800;
			color: #92400e;
		}

		.warning-card p {
			margin: 0 0 8px;
			color: #78350f;
			font-size: 14px;
		}

		.warning-card p:last-child { margin-bottom: 0; }

		.last-guard {
			margin-top: 14px;
			padding-top: 14px;
			border-top: 1px solid #fde68a;
			color: #0f172a;
			font-size: 14px;
		}

		.last-guard strong { display: block; margin: 2px 0 4px; }
		.last-guard span { color: #64748b; }

		.filters-card {
			padding: 20px;
			margin-top: 8px;
		}

		.filters-grid {
			display: grid;
			grid-template-columns: 1.4fr repeat(3, minmax(0, 1fr));
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

		.search-wrap { position: relative; }
		.search-wrap i {
			position: absolute;
			left: 12px;
			top: 50%;
			transform: translateY(-50%);
			color: #94a3b8;
		}
		.search-wrap input { padding-left: 36px; }

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

		.status-on-duty { background: #dcfce7; color: #166534; }
		.status-completed { background: #e2e8f0; color: #334155; }
		.status-inside { background: #c7f0dd; color: #15803d; }
		.status-exited { background: #e2e8f0; color: #334155; }

		.action-link {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			text-decoration: none;
			color: #273b9e;
			font-size: 14px;
			font-weight: 600;
			background: none;
			border: 0;
			padding: 0;
			cursor: pointer;
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

		.refresh-meta {
			display: flex;
			align-items: center;
			gap: 10px;
			color: #64748b;
			font-size: 13px;
		}

		.duty-pagination-bar {
			border: 0;
			border-top: 1px solid #e8ecf1;
			border-radius: 0 0 12px 12px;
			margin-top: 0;
			background: #fff;
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

		.offcanvas { width: min(640px, 100vw); }

		.guard-name { font-weight: 700; color: #0f172a; }

		@media (max-width: 1200px) {
			.layout { min-width: 0; }
			.filters-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
			.duty-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
		}

		@media (max-width: 992px) {
			.main {
				margin-left: 0;
				padding: 16px;
			}

			.layout { min-width: 0; }
			.filters-grid { grid-template-columns: 1fr; }
			.custom-range-row.is-visible { grid-template-columns: 1fr; }
			.duty-stats { grid-template-columns: 1fr 1fr; }
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
			@include('admin.partials.admin-topbar', ['title' => 'Guard Duty Monitoring'])

			<div class="toolbar mb-3">
				<div>
					<p class="page-subtitle mb-0">Monitor currently assigned security guards and review duty history.</p>
				</div>
				<div class="refresh-meta">
					<span id="lastUpdatedLabel">Last updated: just now</span>
					<button type="button" class="btn btn-outline-primary btn-sm" id="refreshDutyBtn">
						<i class="bi bi-arrow-clockwise me-1"></i> Refresh
					</button>
				</div>
			</div>

			<div id="pageError" class="alert alert-danger d-none" role="alert"></div>

			<h2 class="section-title">Currently On Duty</h2>
			<div id="currentDutyWrap">
				<div class="duty-card">
					<p class="text-muted mb-0">Loading current duty status...</p>
				</div>
			</div>

			<h2 class="section-title">Duty History</h2>
			<div class="filters-card">
				<p class="filters-label">Search &amp; Filter</p>
				<form id="dutyFilterForm" data-prevent-submit>
					<div class="filters-grid">
						<div>
							<label class="field-label" for="search">Search Guard</label>
							<div class="search-wrap">
								<i class="bi bi-search"></i>
								<input type="search" id="search" class="filter-input" placeholder="Search Guard...">
							</div>
						</div>
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
							<label class="field-label" for="station">Station</label>
							<select id="station" class="filter-select">
								<option value="">All Stations</option>
							</select>
						</div>
						<div>
							<label class="field-label" for="status">Status</label>
							<select id="status" class="filter-select">
								<option value="">All</option>
								<option value="on_duty">On Duty</option>
								<option value="completed">Completed</option>
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
					<div class="filters-actions">
						<button type="button" class="btn btn-outline-secondary" id="resetFiltersBtn">Reset</button>
						<button type="button" class="btn btn-primary" id="applyFiltersBtn">Apply</button>
					</div>
				</form>
			</div>

			<div class="table-card">
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead>
							<tr>
								<th>Guard</th>
								<th>Badge No.</th>
								<th>Station</th>
								<th>Duty Started</th>
								<th>Duty Ended</th>
								<th>Duration</th>
								<th>Visitors</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody id="dutyHistoryBody">
							<tr>
								<td colspan="9" class="text-center text-muted py-4">Loading duty history...</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="table-pagination-bar duty-pagination-bar" id="dutyPagination" role="navigation" aria-label="Duty history pagination">
					<div class="table-pagination-left">
						<label class="table-pagination-label" for="dutyPageSize">Page size:</label>
						<select id="dutyPageSize" class="table-page-size" aria-label="Page size">
							<option value="5" selected>5</option>
							<option value="10">10</option>
							<option value="25">25</option>
							<option value="50">50</option>
							<option value="75">75</option>
							<option value="100">100</option>
						</select>
						<span class="table-pagination-range" id="dutyPaginationRange">0 to 0 of 0</span>
					</div>
					<div class="table-pagination-right">
						<button type="button" class="table-pagination-nav is-disabled" id="dutyPaginationFirst" aria-label="First page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 6L5 12l6 6M19 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
						<button type="button" class="table-pagination-nav is-disabled" id="dutyPaginationPrev" aria-label="Previous page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
						<span class="table-pagination-page" id="dutyPaginationPageLabel">Page <strong>1</strong> of 1</span>
						<button type="button" class="table-pagination-nav is-disabled" id="dutyPaginationNext" aria-label="Next page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
						<button type="button" class="table-pagination-nav is-disabled" id="dutyPaginationLast" aria-label="Last page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6l6 6-6 6M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
					</div>
				</div>
			</div>
		</main>
	</div>

	<div class="offcanvas offcanvas-end" tabindex="-1" id="dutyDrawer" aria-labelledby="dutyDrawerLabel">
		<div class="offcanvas-header border-bottom">
			<div>
				<button type="button" class="btn btn-link p-0 mb-1 d-none" id="drawerBackBtn">Back to details</button>
				<h5 class="offcanvas-title mb-0" id="dutyDrawerLabel">Guard Duty Details</h5>
			</div>
			<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body" id="dutyDrawerBody"></div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	<script nonce="{{ $cspNonce }}">
		(function () {
			const listUrl = @json(route('api.admin.guard-duty'));
			const filtersUrl = @json(route('api.admin.guard-duty.filters'));
			const showUrlBase = @json(url('/api/admin/guard-duty'));

			const state = {
				page: 1,
				lastPage: 1,
				perPage: 5,
				searchTimer: null,
				lastUpdatedAt: Date.now(),
				drawerOpen: false,
				drawerMode: 'details',
				activeShiftId: null,
				visitorsPage: 1,
				visitorsPerPage: 5,
			};

			const els = {
				current: document.getElementById('currentDutyWrap'),
				body: document.getElementById('dutyHistoryBody'),
				pageSize: document.getElementById('dutyPageSize'),
				range: document.getElementById('dutyPaginationRange'),
				pageLabel: document.getElementById('dutyPaginationPageLabel'),
				first: document.getElementById('dutyPaginationFirst'),
				prev: document.getElementById('dutyPaginationPrev'),
				next: document.getElementById('dutyPaginationNext'),
				last: document.getElementById('dutyPaginationLast'),
				error: document.getElementById('pageError'),
				lastUpdated: document.getElementById('lastUpdatedLabel'),
				dateRange: document.getElementById('date_range'),
				customRange: document.getElementById('customRangeRow'),
				station: document.getElementById('station'),
				status: document.getElementById('status'),
				search: document.getElementById('search'),
				dateFrom: document.getElementById('date_from'),
				dateTo: document.getElementById('date_to'),
				drawerBody: document.getElementById('dutyDrawerBody'),
				drawerLabel: document.getElementById('dutyDrawerLabel'),
				drawerBack: document.getElementById('drawerBackBtn'),
			};

			const drawerEl = document.getElementById('dutyDrawer');
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

			function queryParams() {
				const params = new URLSearchParams({
					page: String(state.page),
					per_page: String(state.perPage),
					date_range: els.dateRange.value || 'all',
				});
				if (els.search.value.trim()) params.set('search', els.search.value.trim());
				if (els.station.value) params.set('station', els.station.value);
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
					headers: {
						'Accept': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
					},
					credentials: 'same-origin',
				});
				const payload = await response.json().catch(function () { return {}; });
				if (!response.ok || payload.success === false) {
					throw new Error(payload.message || 'Unable to load Guard Duty data.');
				}
				return payload;
			}

			function renderCurrent(current, lastCompleted) {
				if (Array.isArray(current) && current.length > 0) {
					els.current.innerHTML = '<div class="current-grid">' + current.map(function (shift) {
						const guard = shift.guard || {};
						return (
							'<article class="duty-card">' +
								'<div class="duty-status">On Duty</div>' +
								'<h3 class="duty-name">' + escapeHtml(guard.name || 'Security Guard') + '</h3>' +
								'<p class="duty-meta">Security Guard • Badge ' + escapeHtml(guard.badge_number || '—') + '</p>' +
								'<div class="duty-stats">' +
									'<div><span class="duty-stat-label">Station</span><p class="duty-stat-value">' + escapeHtml(guard.station || '—') + '</p></div>' +
									'<div><span class="duty-stat-label">Duty Started</span><p class="duty-stat-value">' + escapeHtml(shift.clock_in_time_label || '—') + '</p></div>' +
									'<div><span class="duty-stat-label">Duration</span><p class="duty-stat-value">' + escapeHtml(shift.duration_label || '—') + '</p></div>' +
									'<div><span class="duty-stat-label">Visitors</span><p class="duty-stat-value">' + escapeHtml(shift.visitors_count ?? 0) + '</p></div>' +
								'</div>' +
							'</article>'
						);
					}).join('') + '</div>';
					return;
				}

				const lastGuard = lastCompleted && lastCompleted.guard ? lastCompleted.guard : null;
				els.current.innerHTML =
					'<div class="warning-card">' +
						'<h3>No Guard Currently On Duty</h3>' +
						'<p>There is currently no security guard assigned to the Self-Registration station.</p>' +
						'<p>Self-registration may currently be unavailable.</p>' +
						(lastGuard
							? '<div class="last-guard"><span>Last Guard:</span><strong>' + escapeHtml(lastGuard.name || '—') + '</strong><span>Ended Duty: ' + escapeHtml(lastCompleted.clock_out_time_label || '—') + '</span></div>'
							: '') +
					'</div>';
			}

			function statusBadge(shift) {
				const active = !!shift.is_active;
				return '<span class="status-badge ' + (active ? 'status-on-duty' : 'status-completed') + '">' +
					escapeHtml(active ? 'ON DUTY' : 'COMPLETED') +
					'</span>';
			}

			function renderHistory(payload) {
				const rows = (payload.history && payload.history.data) ? payload.history.data : [];
				const meta = (payload.history && payload.history.meta) ? payload.history.meta : {};
				state.lastPage = meta.last_page || 1;
				state.page = meta.current_page || 1;

				if (!rows.length) {
					els.body.innerHTML = '<tr><td colspan="9"><div class="empty-state"><i class="bi bi-stopwatch"></i>No duty history found.</div></td></tr>';
				} else {
					els.body.innerHTML = rows.map(function (shift) {
						const guard = shift.guard || {};
						return (
							'<tr>' +
								'<td><span class="guard-name">' + escapeHtml(guard.name || '—') + '</span></td>' +
								'<td>' + escapeHtml(guard.badge_number || '—') + '</td>' +
								'<td>' + escapeHtml(guard.station || '—') + '</td>' +
								'<td>' + escapeHtml(shift.clock_in_label || '—') + '</td>' +
								'<td>' + escapeHtml(shift.is_active ? '—' : (shift.clock_out_label || '—')) + '</td>' +
								'<td>' + escapeHtml(shift.duration_label || '—') + '</td>' +
								'<td>' + escapeHtml(shift.visitors_count ?? 0) + '</td>' +
								'<td>' + statusBadge(shift) + '</td>' +
								'<td><button type="button" class="action-link js-view-shift" data-shift-id="' + escapeHtml(shift.shift_id) + '">View Details</button></td>' +
							'</tr>'
						);
					}).join('');
				}

				els.range.textContent = (meta.from || 0) + ' to ' + (meta.to || 0) + ' of ' + (meta.total || 0);
				els.pageLabel.innerHTML = 'Page <strong>' + (meta.current_page || 1) + '</strong> of ' + (meta.last_page || 1);
				setNav(els.first, state.page <= 1);
				setNav(els.prev, state.page <= 1);
				setNav(els.next, state.page >= state.lastPage);
				setNav(els.last, state.page >= state.lastPage);
			}

			function setNav(button, disabled) {
				button.disabled = disabled;
				button.classList.toggle('is-disabled', disabled);
				button.setAttribute('aria-disabled', disabled ? 'true' : 'false');
			}

			async function loadDuty() {
				hideError();
				try {
					const payload = await fetchJson(listUrl + '?' + queryParams().toString());
					renderCurrent(payload.current || [], payload.last_completed || null);
					renderHistory(payload);
					state.lastUpdatedAt = Date.now();
					els.lastUpdated.textContent = 'Last updated: just now';
				} catch (error) {
					showError(error.message || 'Unable to load Guard Duty data.');
				}
			}

			async function loadFilters() {
				try {
					const payload = await fetchJson(filtersUrl);
					const stations = (payload.data && payload.data.stations) ? payload.data.stations : [];
					const current = els.station.value;
					els.station.innerHTML = '<option value="">All Stations</option>' + stations.map(function (station) {
						return '<option value="' + escapeHtml(station) + '">' + escapeHtml(station) + '</option>';
					}).join('');
					if (current) els.station.value = current;
				} catch (error) {
					// Keep default station options if the filter endpoint fails.
				}
			}

			function renderDetails(shift) {
				const guard = shift.guard || {};
				els.drawerMode = 'details';
				els.drawerLabel.textContent = 'Guard Duty Details';
				els.drawerBack.classList.add('d-none');
				els.drawerBody.innerHTML =
					'<div class="detail-section">' +
						'<h6>Guard Information</h6>' +
						'<p class="guard-name mb-2">' + escapeHtml(guard.name || '—') + '</p>' +
						'<dl class="detail-grid">' +
							'<dt>Badge Number</dt><dd>' + escapeHtml(guard.badge_number || '—') + '</dd>' +
							'<dt>Station</dt><dd>' + escapeHtml(guard.station || '—') + '</dd>' +
						'</dl>' +
					'</div>' +
					'<div class="detail-section">' +
						'<h6>Shift Information</h6>' +
						'<dl class="detail-grid">' +
							'<dt>Started</dt><dd>' + escapeHtml(shift.clock_in_detail_label || '—') + '</dd>' +
							'<dt>Ended</dt><dd>' + escapeHtml(shift.clock_out_detail_label || '—') + '</dd>' +
							'<dt>Duration</dt><dd>' + escapeHtml(shift.duration_label || '—') + '</dd>' +
						'</dl>' +
					'</div>' +
					'<div class="detail-section">' +
						'<h6>Visitors During Shift</h6>' +
						'<p class="guard-name">' + escapeHtml(shift.visitors_count ?? 0) + '</p>' +
						'<button type="button" class="btn btn-primary mt-2" id="viewVisitorsBtn">View Visitors</button>' +
					'</div>';

				const viewBtn = document.getElementById('viewVisitorsBtn');
				if (viewBtn) {
					viewBtn.addEventListener('click', function () {
						state.visitorsPage = 1;
						loadVisitors(shift.shift_id);
					});
				}
			}

			async function openDetails(shiftId) {
				state.activeShiftId = shiftId;
				els.drawerBody.innerHTML = '<p class="text-muted mb-0">Loading details...</p>';
				drawer.show();
				try {
					const payload = await fetchJson(showUrlBase + '/' + shiftId);
					renderDetails(payload.data || {});
				} catch (error) {
					els.drawerBody.innerHTML = '<p class="text-danger mb-0">' + escapeHtml(error.message) + '</p>';
				}
			}

			async function loadVisitors(shiftId) {
				els.drawerMode = 'visitors';
				els.drawerBack.classList.remove('d-none');
				els.drawerBody.innerHTML = '<p class="text-muted mb-0">Loading visitors...</p>';
				try {
					const params = new URLSearchParams({
						page: String(state.visitorsPage),
						per_page: String(state.visitorsPerPage),
					});
					const payload = await fetchJson(showUrlBase + '/' + shiftId + '/visitors?' + params.toString());
					const shift = payload.shift || {};
					const guard = shift.guard || {};
					const rows = payload.data || [];
					const meta = payload.meta || {};
					els.drawerLabel.textContent = 'Visitors During Shift';

					let table = '<p class="guard-name mb-1">' + escapeHtml(guard.name || '—') + '</p>' +
						'<p class="text-muted mb-3">' + escapeHtml(shift.visitors_range_label || '') + '</p>';

					if (!rows.length) {
						table += '<div class="empty-state"><i class="bi bi-people"></i>No visitors were registered during this shift.</div>';
					} else {
						table += '<div class="table-responsive"><table class="table table-sm align-middle">' +
							'<thead><tr><th>Control Number</th><th>Visitor Name</th><th>Visitor Type</th><th>Destination</th><th>Time In</th><th>Time Out</th><th>Status</th></tr></thead><tbody>' +
							rows.map(function (visit) {
								const statusClass = visit.status_key === 'inside' ? 'status-inside' : 'status-exited';
								return '<tr>' +
									'<td>' + escapeHtml(visit.control_number) + '</td>' +
									'<td>' + escapeHtml(visit.visitor_name) + '</td>' +
									'<td>' + escapeHtml(visit.visit_type) + '</td>' +
									'<td>' + escapeHtml(visit.destination) + '</td>' +
									'<td>' + escapeHtml(visit.entry_time_label) + '</td>' +
									'<td>' + escapeHtml(visit.exit_time_label) + '</td>' +
									'<td><span class="status-badge ' + statusClass + '">' + escapeHtml(visit.status) + '</span></td>' +
								'</tr>';
							}).join('') +
							'</tbody></table></div>';
					}

					const lastPage = meta.last_page || 1;
					table += '<div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">' +
						'<span class="text-muted small">' + (meta.from || 0) + ' to ' + (meta.to || 0) + ' of ' + (meta.total || 0) + '</span>' +
						'<div class="d-flex gap-2">' +
							'<button type="button" class="btn btn-outline-secondary btn-sm" id="visitorsPrev" ' + (state.visitorsPage <= 1 ? 'disabled' : '') + '>Previous</button>' +
							'<button type="button" class="btn btn-outline-secondary btn-sm" id="visitorsNext" ' + (state.visitorsPage >= lastPage ? 'disabled' : '') + '>Next</button>' +
						'</div></div>';

					els.drawerBody.innerHTML = table;

					const prev = document.getElementById('visitorsPrev');
					const next = document.getElementById('visitorsNext');
					if (prev) prev.addEventListener('click', function () {
						state.visitorsPage = Math.max(1, state.visitorsPage - 1);
						loadVisitors(shiftId);
					});
					if (next) next.addEventListener('click', function () {
						state.visitorsPage += 1;
						loadVisitors(shiftId);
					});
				} catch (error) {
					els.drawerBody.innerHTML = '<p class="text-danger mb-0">' + escapeHtml(error.message) + '</p>';
				}
			}

			els.body.addEventListener('click', function (event) {
				const button = event.target.closest('.js-view-shift');
				if (!button) return;
				openDetails(button.getAttribute('data-shift-id'));
			});

			els.drawerBack.addEventListener('click', function () {
				if (state.activeShiftId) openDetails(state.activeShiftId);
			});

			document.getElementById('applyFiltersBtn').addEventListener('click', function () {
				state.page = 1;
				loadDuty();
			});
			document.getElementById('resetFiltersBtn').addEventListener('click', function () {
				els.search.value = '';
				els.dateRange.value = 'all';
				els.station.value = '';
				els.status.value = '';
				els.dateFrom.value = '';
				els.dateTo.value = '';
				toggleCustomRange();
				state.page = 1;
				loadDuty();
			});
			document.getElementById('refreshDutyBtn').addEventListener('click', loadDuty);
			els.dateRange.addEventListener('change', toggleCustomRange);
			els.search.addEventListener('input', function () {
				clearTimeout(state.searchTimer);
				state.searchTimer = setTimeout(function () {
					state.page = 1;
					loadDuty();
				}, 350);
			});
			els.pageSize.addEventListener('change', function () {
				state.perPage = parseInt(els.pageSize.value, 10) || 5;
				state.page = 1;
				loadDuty();
			});
			els.first.addEventListener('click', function () { state.page = 1; loadDuty(); });
			els.prev.addEventListener('click', function () { state.page = Math.max(1, state.page - 1); loadDuty(); });
			els.next.addEventListener('click', function () { state.page = Math.min(state.lastPage, state.page + 1); loadDuty(); });
			els.last.addEventListener('click', function () { state.page = state.lastPage; loadDuty(); });

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
				loadDuty();
			}, 45000);

			toggleCustomRange();
			loadFilters();
			loadDuty();
		})();
	</script>
	@include('admin.partials.admin-responsive-script')
</body>
</html>
