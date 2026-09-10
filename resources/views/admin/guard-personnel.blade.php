<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Security Guard Personnel</title>
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

		.back-link {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			color: #273b9e;
			text-decoration: none;
			font-size: 14px;
			font-weight: 600;
			margin-bottom: 10px;
		}

		.back-link:hover { color: #1e2f8a; }

		.filters-card,
		.table-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		}

		.filters-card {
			padding: 20px;
			margin-top: 8px;
		}

		.filters-grid {
			display: grid;
			grid-template-columns: 1.6fr minmax(0, 220px);
			gap: 10px;
			align-items: end;
		}

		.filters-actions {
			display: flex;
			gap: 8px;
			flex-wrap: wrap;
			margin-top: 12px;
		}

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

		.status-active { background: #dcfce7; color: #166534; }
		.status-inactive { background: #e2e8f0; color: #475569; }

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

		.action-link:hover { color: #1e2f8a; }
		.action-link.is-danger { color: #b45309; }
		.action-link.is-danger:hover { color: #92400e; }
		.action-link.is-success { color: #15803d; }
		.action-link.is-success:hover { color: #166534; }

		.row-actions {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			align-items: center;
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

		.guard-name { font-weight: 700; color: #0f172a; }

		.personnel-pagination-bar {
			border: 0;
			border-top: 1px solid #e8ecf1;
			border-radius: 0 0 12px 12px;
			margin-top: 0;
			background: #fff;
		}

		.user-modal-overlay {
			display: none;
			position: fixed;
			top: 0;
			right: 0;
			bottom: 0;
			left: 260px;
			background: rgba(2, 6, 23, 0.6);
			z-index: 1200;
			padding: 16px;
			overflow: auto;
			box-sizing: border-box;
		}

		.user-modal-overlay.is-open {
			display: grid !important;
			place-items: center;
			align-content: center;
			justify-content: center;
		}

		.user-modal-dialog {
			background: #fff;
			border-radius: 10px;
			width: 520px;
			max-width: min(520px, 100%);
			padding: 20px;
			box-shadow: 0 10px 30px rgba(2, 6, 23, 0.35);
			margin: 0;
			max-height: calc(100vh - 32px);
			overflow: auto;
			justify-self: center;
			align-self: center;
		}

		@media (max-width: 991.98px) {
			.user-modal-overlay { left: 0; }
		}

		.user-modal-head {
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.user-modal-title {
			margin: 0;
			font-size: 18px;
		}

		.user-modal-close {
			border: 0;
			background: transparent;
			font-size: 22px;
			cursor: pointer;
			line-height: 1;
			color: #374151;
		}

		.user-modal-lead {
			color: #6b7280;
			margin: 8px 0 14px;
		}

		.user-modal-row {
			display: flex;
			gap: 10px;
		}

		.user-modal-field { flex: 1; }
		.user-modal-field.stacked { margin-top: 10px; }

		.user-modal-label {
			font-size: 13px;
			color: #334155;
		}

		.user-modal-input,
		.user-modal-select {
			width: 100%;
			padding: 10px;
			border: 1px solid #e5e7eb;
			border-radius: 6px;
			margin-top: 6px;
		}

		.user-modal-select { background: #fff; }

		.user-modal-input.is-invalid,
		.user-modal-select.is-invalid {
			border-color: #dc2626;
		}

		.field-error {
			display: block;
			color: #dc2626;
			font-size: 12px;
			margin-top: 4px;
		}

		.user-modal-alert {
			display: none;
			margin: 0 0 12px;
			padding: 10px 12px;
			border-radius: 8px;
			background: #fef2f2;
			border: 1px solid #fecaca;
			color: #991b1b;
			font-size: 13px;
		}

		.user-modal-alert.is-visible { display: block; }

		.user-modal-actions {
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			margin-top: 18px;
		}

		.user-modal-btn-cancel {
			background: #ecedf2;
			border-radius: 8px;
			padding: 8px 14px;
			border: 0;
			cursor: pointer;
			color: #0f172a;
		}

		.user-modal-btn-submit {
			background: #4b5cd1;
			color: #fff;
			border-radius: 8px;
			padding: 8px 14px;
			border: 0;
			cursor: pointer;
		}

		.user-modal-btn-submit:disabled,
		.user-modal-btn-cancel:disabled {
			opacity: 0.65;
			cursor: not-allowed;
		}

		@media (max-width: 1200px) {
			.layout { min-width: 0; }
			.filters-grid { grid-template-columns: 1fr minmax(0, 220px); }
		}

		@media (max-width: 992px) {
			.main {
				margin-left: 0;
				padding: 16px;
			}

			.layout { min-width: 0; }
			.filters-grid { grid-template-columns: 1fr; }
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
			@include('admin.partials.admin-topbar', ['title' => 'Security Guard Personnel'])

			<a href="{{ route('admin.guard-duty') }}" class="back-link">
				<i class="bi bi-arrow-left"></i> Back to Guard Duty
			</a>

			<div class="toolbar mb-3">
				<div>
					<p class="page-subtitle mb-0">Manage security guard personnel records, status, and duty PIN.</p>
				</div>
				<div>
					<button type="button" class="btn btn-primary btn-sm" id="openAddGuardBtn">
						<i class="bi bi-plus-lg me-1"></i> Add Guard
					</button>
				</div>
			</div>

			<div id="pageError" class="alert alert-danger d-none" role="alert"></div>
			<div id="pageSuccess" class="alert alert-success d-none" role="alert"></div>

			<div class="filters-card">
				<p class="filters-label">Search &amp; Filter</p>
				<form id="guardFilterForm" data-prevent-submit>
					<div class="filters-grid">
						<div>
							<label class="field-label" for="search">Search</label>
							<div class="search-wrap">
								<i class="bi bi-search"></i>
								<input type="search" id="search" class="filter-input" placeholder="Search name or badge number...">
							</div>
						</div>
						<div>
							<label class="field-label" for="status">Status</label>
							<select id="status" class="filter-select">
								<option value="">All</option>
								<option value="active">Active</option>
								<option value="inactive">Inactive</option>
							</select>
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
								<th>Badge No.</th>
								<th>Guard Name</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody id="guardsBody">
							<tr>
								<td colspan="4" class="text-center text-muted py-4">Loading guard personnel...</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="table-pagination-bar personnel-pagination-bar" id="guardsPagination" role="navigation" aria-label="Guard personnel pagination">
					<div class="table-pagination-left">
						<label class="table-pagination-label" for="guardsPageSize">Page size:</label>
						<select id="guardsPageSize" class="table-page-size" aria-label="Page size">
							<option value="5">5</option>
							<option value="10" selected>10</option>
							<option value="25">25</option>
							<option value="50">50</option>
						</select>
						<span class="table-pagination-range" id="guardsPaginationRange">0 to 0 of 0</span>
					</div>
					<div class="table-pagination-right">
						<button type="button" class="table-pagination-nav is-disabled" id="guardsPaginationFirst" aria-label="First page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 6L5 12l6 6M19 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
						<button type="button" class="table-pagination-nav is-disabled" id="guardsPaginationPrev" aria-label="Previous page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
						<span class="table-pagination-page" id="guardsPaginationPageLabel">Page <strong>1</strong> of 1</span>
						<button type="button" class="table-pagination-nav is-disabled" id="guardsPaginationNext" aria-label="Next page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
						<button type="button" class="table-pagination-nav is-disabled" id="guardsPaginationLast" aria-label="Last page" aria-disabled="true" disabled>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6l6 6-6 6M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
					</div>
				</div>
			</div>
		</main>
	</div>

	{{-- Add Guard Modal --}}
	<div id="addGuardModal" class="user-modal-overlay" aria-hidden="true">
		<div role="dialog" aria-modal="true" aria-labelledby="addGuardTitle" class="user-modal-dialog">
			<div class="user-modal-head">
				<h3 id="addGuardTitle" class="user-modal-title">Add Guard</h3>
				<button type="button" class="user-modal-close js-close-add-guard" aria-label="Close">&times;</button>
			</div>
			<p class="user-modal-lead">Create a new security guard personnel record.</p>
			<div id="addGuardAlert" class="user-modal-alert" role="alert"></div>
			<form id="addGuardForm" autocomplete="off">
				<div class="user-modal-row">
					<div class="user-modal-field">
						<label class="user-modal-label" for="add_first_name">First Name <span class="text-danger">*</span></label>
						<input id="add_first_name" name="first_name" class="user-modal-input" type="text" required maxlength="255">
						<span class="field-error" data-error-for="first_name"></span>
					</div>
					<div class="user-modal-field">
						<label class="user-modal-label" for="add_middle_name">Middle Name</label>
						<input id="add_middle_name" name="middle_name" class="user-modal-input" type="text" maxlength="255">
						<span class="field-error" data-error-for="middle_name"></span>
					</div>
				</div>
				<div class="user-modal-field stacked">
					<label class="user-modal-label" for="add_last_name">Last Name <span class="text-danger">*</span></label>
					<input id="add_last_name" name="last_name" class="user-modal-input" type="text" required maxlength="255">
					<span class="field-error" data-error-for="last_name"></span>
				</div>
				<div class="user-modal-field stacked">
					<label class="user-modal-label" for="add_badge_number">Badge Number <span class="text-danger">*</span></label>
					<input id="add_badge_number" name="badge_number" class="user-modal-input" type="text" required maxlength="50">
					<span class="field-error" data-error-for="badge_number"></span>
				</div>
				<div class="user-modal-row" style="margin-top: 10px;">
					<div class="user-modal-field">
						<label class="user-modal-label" for="add_duty_pin">Duty PIN <span class="text-danger">*</span></label>
						<input id="add_duty_pin" name="duty_pin" class="user-modal-input" type="password" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="new-password">
						<span class="field-error" data-error-for="duty_pin"></span>
					</div>
					<div class="user-modal-field">
						<label class="user-modal-label" for="add_duty_pin_confirmation">Confirm PIN <span class="text-danger">*</span></label>
						<input id="add_duty_pin_confirmation" name="duty_pin_confirmation" class="user-modal-input" type="password" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="new-password">
						<span class="field-error" data-error-for="duty_pin_confirmation"></span>
					</div>
				</div>
				<div class="user-modal-field stacked">
					<label class="user-modal-label" for="add_status">Status</label>
					<select id="add_status" name="status" class="user-modal-select">
						<option value="active" selected>Active</option>
						<option value="inactive">Inactive</option>
					</select>
					<span class="field-error" data-error-for="status"></span>
				</div>
				<div class="user-modal-actions">
					<button type="button" class="user-modal-btn-cancel js-close-add-guard">Cancel</button>
					<button type="submit" class="user-modal-btn-submit" id="addGuardSubmit">Add Guard</button>
				</div>
			</form>
		</div>
	</div>

	{{-- Edit Guard Modal --}}
	<div id="editGuardModal" class="user-modal-overlay" aria-hidden="true">
		<div role="dialog" aria-modal="true" aria-labelledby="editGuardTitle" class="user-modal-dialog">
			<div class="user-modal-head">
				<h3 id="editGuardTitle" class="user-modal-title">Edit Guard</h3>
				<button type="button" class="user-modal-close js-close-edit-guard" aria-label="Close">&times;</button>
			</div>
			<p class="user-modal-lead">Update guard details. Duty PIN is not changed here.</p>
			<div id="editGuardAlert" class="user-modal-alert" role="alert"></div>
			<form id="editGuardForm" autocomplete="off">
				<input type="hidden" id="edit_guard_id" name="id" value="">
				<div class="user-modal-row">
					<div class="user-modal-field">
						<label class="user-modal-label" for="edit_first_name">First Name <span class="text-danger">*</span></label>
						<input id="edit_first_name" name="first_name" class="user-modal-input" type="text" required maxlength="255">
						<span class="field-error" data-error-for="first_name"></span>
					</div>
					<div class="user-modal-field">
						<label class="user-modal-label" for="edit_middle_name">Middle Name</label>
						<input id="edit_middle_name" name="middle_name" class="user-modal-input" type="text" maxlength="255">
						<span class="field-error" data-error-for="middle_name"></span>
					</div>
				</div>
				<div class="user-modal-field stacked">
					<label class="user-modal-label" for="edit_last_name">Last Name <span class="text-danger">*</span></label>
					<input id="edit_last_name" name="last_name" class="user-modal-input" type="text" required maxlength="255">
					<span class="field-error" data-error-for="last_name"></span>
				</div>
				<div class="user-modal-field stacked">
					<label class="user-modal-label" for="edit_badge_number">Badge Number <span class="text-danger">*</span></label>
					<input id="edit_badge_number" name="badge_number" class="user-modal-input" type="text" required maxlength="50">
					<span class="field-error" data-error-for="badge_number"></span>
				</div>
				<div class="user-modal-field stacked">
					<label class="user-modal-label" for="edit_status">Status</label>
					<select id="edit_status" name="status" class="user-modal-select" required>
						<option value="active">Active</option>
						<option value="inactive">Inactive</option>
					</select>
					<span class="field-error" data-error-for="status"></span>
				</div>
				<div class="user-modal-actions">
					<button type="button" class="user-modal-btn-cancel js-close-edit-guard">Cancel</button>
					<button type="submit" class="user-modal-btn-submit" id="editGuardSubmit">Save</button>
				</div>
			</form>
		</div>
	</div>

	{{-- Reset PIN Modal --}}
	<div id="resetPinModal" class="user-modal-overlay" aria-hidden="true">
		<div role="dialog" aria-modal="true" aria-labelledby="resetPinTitle" class="user-modal-dialog">
			<div class="user-modal-head">
				<h3 id="resetPinTitle" class="user-modal-title">Reset Duty PIN</h3>
				<button type="button" class="user-modal-close js-close-reset-pin" aria-label="Close">&times;</button>
			</div>
			<p class="user-modal-lead" id="resetPinLead">Set a new 6-digit duty PIN for this guard.</p>
			<div id="resetPinAlert" class="user-modal-alert" role="alert"></div>
			<form id="resetPinForm" autocomplete="off">
				<input type="hidden" id="reset_guard_id" name="id" value="">
				<div class="user-modal-field stacked" style="margin-top: 0;">
					<label class="user-modal-label" for="reset_duty_pin">New Duty PIN <span class="text-danger">*</span></label>
					<input id="reset_duty_pin" name="duty_pin" class="user-modal-input" type="password" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="new-password">
					<span class="field-error" data-error-for="duty_pin"></span>
				</div>
				<div class="user-modal-field stacked">
					<label class="user-modal-label" for="reset_duty_pin_confirmation">Confirm PIN <span class="text-danger">*</span></label>
					<input id="reset_duty_pin_confirmation" name="duty_pin_confirmation" class="user-modal-input" type="password" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="new-password">
					<span class="field-error" data-error-for="duty_pin_confirmation"></span>
				</div>
				<div class="user-modal-actions">
					<button type="button" class="user-modal-btn-cancel js-close-reset-pin">Cancel</button>
					<button type="submit" class="user-modal-btn-submit" id="resetPinSubmit">Update PIN</button>
				</div>
			</form>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	<script nonce="{{ $cspNonce }}">
		(function () {
			const listUrl = @json(route('api.admin.guards'));
			const storeUrl = @json(route('api.admin.guards.store'));
			const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

			const state = {
				page: 1,
				lastPage: 1,
				perPage: 10,
				searchTimer: null,
				guardsById: {},
			};

			const els = {
				body: document.getElementById('guardsBody'),
				pageSize: document.getElementById('guardsPageSize'),
				range: document.getElementById('guardsPaginationRange'),
				pageLabel: document.getElementById('guardsPaginationPageLabel'),
				first: document.getElementById('guardsPaginationFirst'),
				prev: document.getElementById('guardsPaginationPrev'),
				next: document.getElementById('guardsPaginationNext'),
				last: document.getElementById('guardsPaginationLast'),
				error: document.getElementById('pageError'),
				success: document.getElementById('pageSuccess'),
				search: document.getElementById('search'),
				status: document.getElementById('status'),
			};

			function escapeHtml(value) {
				return String(value ?? '')
					.replace(/&/g, '&amp;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;')
					.replace(/"/g, '&quot;');
			}

			function showPageError(message) {
				els.error.textContent = message;
				els.error.classList.remove('d-none');
				els.success.classList.add('d-none');
			}

			function hidePageError() {
				els.error.classList.add('d-none');
				els.error.textContent = '';
			}

			function showPageSuccess(message) {
				els.success.textContent = message;
				els.success.classList.remove('d-none');
				els.error.classList.add('d-none');
			}

			function queryParams() {
				const params = new URLSearchParams({
					page: String(state.page),
					per_page: String(state.perPage),
				});
				if (els.search.value.trim()) params.set('search', els.search.value.trim());
				if (els.status.value) params.set('status', els.status.value);
				return params;
			}

			function jsonHeaders(withBody) {
				const headers = {
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
					'X-CSRF-TOKEN': csrfToken,
				};
				if (withBody) headers['Content-Type'] = 'application/json';
				return headers;
			}

			async function parseJsonResponse(response) {
				const payload = await response.json().catch(function () { return {}; });
				return { response: response, payload: payload };
			}

			function formatValidationMessage(payload) {
				if (payload && payload.errors && typeof payload.errors === 'object') {
					const messages = [];
					Object.keys(payload.errors).forEach(function (key) {
						const list = payload.errors[key];
						if (Array.isArray(list)) {
							list.forEach(function (msg) { messages.push(String(msg)); });
						} else if (list) {
							messages.push(String(list));
						}
					});
					if (messages.length) return messages.join(' ');
				}
				return (payload && payload.message) || 'Request failed. Please try again.';
			}

			async function fetchJson(url, options) {
				const opts = options || {};
				const response = await fetch(url, Object.assign({
					credentials: 'same-origin',
					headers: jsonHeaders(!!opts.body),
				}, opts));
				const parsed = await parseJsonResponse(response);
				if (!parsed.response.ok || parsed.payload.success === false) {
					const error = new Error(formatValidationMessage(parsed.payload));
					error.status = parsed.response.status;
					error.payload = parsed.payload;
					throw error;
				}
				return parsed.payload;
			}

			function statusBadge(guard) {
				const active = !!guard.is_active || String(guard.status || '').toLowerCase() === 'active';
				return '<span class="status-badge ' + (active ? 'status-active' : 'status-inactive') + '">' +
					escapeHtml(guard.status_label || (active ? 'Active' : 'Inactive')) +
					'</span>';
			}

			function setNav(button, disabled) {
				button.disabled = disabled;
				button.classList.toggle('is-disabled', disabled);
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

				els.range.textContent = from + ' to ' + to + ' of ' + total;
				els.pageLabel.innerHTML = 'Page <strong>' + page + '</strong> of ' + last;
				if (els.pageSize.value !== String(state.perPage)) {
					els.pageSize.value = String(state.perPage);
				}

				const onFirst = page <= 1;
				const onLast = page >= last;
				setNav(els.first, onFirst);
				setNav(els.prev, onFirst);
				setNav(els.next, onLast);
				setNav(els.last, onLast);
			}

			function renderRows(payload) {
				const rows = payload.data || [];
				state.guardsById = {};
				rows.forEach(function (guard) {
					state.guardsById[String(guard.id)] = guard;
				});

				if (!rows.length) {
					els.body.innerHTML =
						'<tr><td colspan="4"><div class="empty-state">' +
							'<i class="bi bi-shield-lock"></i>' +
							'<h5 class="mb-1">No guard personnel found.</h5>' +
							'<p class="mb-0">Try adjusting search or status filters, or add a new guard.</p>' +
						'</div></td></tr>';
					renderPagination(payload.meta || {});
					return;
				}

				els.body.innerHTML = rows.map(function (guard) {
					const active = !!guard.is_active || String(guard.status || '').toLowerCase() === 'active';
					const statusAction = active
						? '<button type="button" class="action-link is-danger js-toggle-status" data-id="' + escapeHtml(guard.id) + '" data-status="inactive">Deactivate</button>'
						: '<button type="button" class="action-link is-success js-toggle-status" data-id="' + escapeHtml(guard.id) + '" data-status="active">Activate</button>';

					return (
						'<tr>' +
							'<td>' + escapeHtml(guard.badge_number || '—') + '</td>' +
							'<td><span class="guard-name">' + escapeHtml(guard.full_name || '—') + '</span></td>' +
							'<td>' + statusBadge(guard) + '</td>' +
							'<td><div class="row-actions">' +
								'<button type="button" class="action-link js-edit-guard" data-id="' + escapeHtml(guard.id) + '">Edit</button>' +
								statusAction +
								'<button type="button" class="action-link js-reset-pin" data-id="' + escapeHtml(guard.id) + '">Reset PIN</button>' +
							'</div></td>' +
						'</tr>'
					);
				}).join('');

				renderPagination(payload.meta || {});
			}

			async function loadGuards() {
				hidePageError();
				try {
					const payload = await fetchJson(listUrl + '?' + queryParams().toString());
					renderRows(payload);
				} catch (error) {
					showPageError(error.message || 'Unable to load security guard personnel.');
					els.body.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Unable to load data.</td></tr>';
				}
			}

			function openModal(modal) {
				if (!modal) return;
				modal.classList.add('is-open');
				modal.setAttribute('aria-hidden', 'false');
			}

			function closeModal(modal) {
				if (!modal) return;
				modal.classList.remove('is-open');
				modal.setAttribute('aria-hidden', 'true');
			}

			function clearFormErrors(form) {
				if (!form) return;
				form.querySelectorAll('.field-error').forEach(function (el) { el.textContent = ''; });
				form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
			}

			function showModalAlert(alertEl, message) {
				if (!alertEl) return;
				if (message) {
					alertEl.textContent = message;
					alertEl.classList.add('is-visible');
				} else {
					alertEl.textContent = '';
					alertEl.classList.remove('is-visible');
				}
			}

			function applyFieldErrors(form, errors) {
				if (!form || !errors) return;
				Object.keys(errors).forEach(function (field) {
					const input = form.querySelector('[name="' + field + '"]');
					const errorEl = form.querySelector('[data-error-for="' + field + '"]');
					const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
					if (input) input.classList.add('is-invalid');
					if (errorEl) errorEl.textContent = messages.filter(Boolean).join(' ');
				});
			}

			function setSubmitting(submitBtn, cancelBtns, busy, busyLabel, idleLabel) {
				if (submitBtn) {
					submitBtn.disabled = !!busy;
					submitBtn.textContent = busy ? busyLabel : idleLabel;
				}
				(cancelBtns || []).forEach(function (btn) {
					if (btn) btn.disabled = !!busy;
				});
			}

			const addModal = document.getElementById('addGuardModal');
			const editModal = document.getElementById('editGuardModal');
			const resetModal = document.getElementById('resetPinModal');
			const addForm = document.getElementById('addGuardForm');
			const editForm = document.getElementById('editGuardForm');
			const resetForm = document.getElementById('resetPinForm');
			const addAlert = document.getElementById('addGuardAlert');
			const editAlert = document.getElementById('editGuardAlert');
			const resetAlert = document.getElementById('resetPinAlert');
			const addSubmit = document.getElementById('addGuardSubmit');
			const editSubmit = document.getElementById('editGuardSubmit');
			const resetSubmit = document.getElementById('resetPinSubmit');

			function resetAddForm() {
				addForm.reset();
				document.getElementById('add_status').value = 'active';
				clearFormErrors(addForm);
				showModalAlert(addAlert, '');
			}

			function resetEditForm() {
				editForm.reset();
				document.getElementById('edit_guard_id').value = '';
				clearFormErrors(editForm);
				showModalAlert(editAlert, '');
			}

			function resetPinFormFields() {
				resetForm.reset();
				document.getElementById('reset_guard_id').value = '';
				clearFormErrors(resetForm);
				showModalAlert(resetAlert, '');
			}

			document.getElementById('openAddGuardBtn').addEventListener('click', function () {
				resetAddForm();
				openModal(addModal);
			});

			document.querySelectorAll('.js-close-add-guard').forEach(function (btn) {
				btn.addEventListener('click', function () { closeModal(addModal); });
			});
			document.querySelectorAll('.js-close-edit-guard').forEach(function (btn) {
				btn.addEventListener('click', function () { closeModal(editModal); });
			});
			document.querySelectorAll('.js-close-reset-pin').forEach(function (btn) {
				btn.addEventListener('click', function () { closeModal(resetModal); });
			});

			[addModal, editModal, resetModal].forEach(function (modal) {
				modal.addEventListener('click', function (e) {
					if (e.target === modal) closeModal(modal);
				});
			});

			function openEdit(guard) {
				resetEditForm();
				document.getElementById('edit_guard_id').value = String(guard.id);
				document.getElementById('edit_first_name').value = guard.first_name || '';
				document.getElementById('edit_middle_name').value = guard.middle_name || '';
				document.getElementById('edit_last_name').value = guard.last_name || '';
				document.getElementById('edit_badge_number').value = guard.badge_number || '';
				document.getElementById('edit_status').value = (guard.status || 'active').toLowerCase() === 'inactive' ? 'inactive' : 'active';
				openModal(editModal);
			}

			function openResetPin(guard) {
				resetPinFormFields();
				document.getElementById('reset_guard_id').value = String(guard.id);
				document.getElementById('resetPinLead').textContent =
					'Set a new 6-digit duty PIN for ' + (guard.full_name || 'this guard') + '.';
				openModal(resetModal);
			}

			els.body.addEventListener('click', async function (e) {
				const editBtn = e.target.closest('.js-edit-guard');
				const resetBtn = e.target.closest('.js-reset-pin');
				const toggleBtn = e.target.closest('.js-toggle-status');

				if (editBtn) {
					const guard = state.guardsById[String(editBtn.getAttribute('data-id'))];
					if (guard) openEdit(guard);
					return;
				}

				if (resetBtn) {
					const guard = state.guardsById[String(resetBtn.getAttribute('data-id'))];
					if (guard) openResetPin(guard);
					return;
				}

				if (toggleBtn) {
					const id = toggleBtn.getAttribute('data-id');
					const nextStatus = toggleBtn.getAttribute('data-status');
					const guard = state.guardsById[String(id)];
					const label = nextStatus === 'inactive' ? 'deactivate' : 'activate';
					const name = guard ? (guard.full_name || 'this guard') : 'this guard';
					if (!window.confirm('Are you sure you want to ' + label + ' ' + name + '?')) return;

					toggleBtn.disabled = true;
					try {
						const payload = await fetchJson(listUrl + '/' + id + '/status', {
							method: 'PATCH',
							body: JSON.stringify({ status: nextStatus }),
						});
						showPageSuccess(payload.message || ('Guard ' + label + 'd.'));
						await loadGuards();
					} catch (error) {
						showPageError(error.message || 'Unable to update guard status.');
						toggleBtn.disabled = false;
					}
				}
			});

			addForm.addEventListener('submit', async function (e) {
				e.preventDefault();
				clearFormErrors(addForm);
				showModalAlert(addAlert, '');

				const body = {
					first_name: document.getElementById('add_first_name').value.trim(),
					middle_name: document.getElementById('add_middle_name').value.trim() || null,
					last_name: document.getElementById('add_last_name').value.trim(),
					badge_number: document.getElementById('add_badge_number').value.trim(),
					duty_pin: document.getElementById('add_duty_pin').value,
					duty_pin_confirmation: document.getElementById('add_duty_pin_confirmation').value,
					status: document.getElementById('add_status').value || 'active',
				};

				const cancelBtns = Array.from(document.querySelectorAll('.js-close-add-guard'));
				setSubmitting(addSubmit, cancelBtns, true, 'Saving...', 'Add Guard');

				try {
					const payload = await fetchJson(storeUrl, {
						method: 'POST',
						body: JSON.stringify(body),
					});
					closeModal(addModal);
					showPageSuccess(payload.message || 'Guard personnel added.');
					state.page = 1;
					await loadGuards();
				} catch (error) {
					const payload = error.payload || {};
					if (payload.errors) applyFieldErrors(addForm, payload.errors);
					showModalAlert(addAlert, error.message || 'Unable to add guard.');
				} finally {
					setSubmitting(addSubmit, cancelBtns, false, 'Saving...', 'Add Guard');
				}
			});

			editForm.addEventListener('submit', async function (e) {
				e.preventDefault();
				clearFormErrors(editForm);
				showModalAlert(editAlert, '');

				const id = document.getElementById('edit_guard_id').value;
				const body = {
					first_name: document.getElementById('edit_first_name').value.trim(),
					middle_name: document.getElementById('edit_middle_name').value.trim() || null,
					last_name: document.getElementById('edit_last_name').value.trim(),
					badge_number: document.getElementById('edit_badge_number').value.trim(),
					status: document.getElementById('edit_status').value,
				};

				const cancelBtns = Array.from(document.querySelectorAll('.js-close-edit-guard'));
				setSubmitting(editSubmit, cancelBtns, true, 'Saving...', 'Save');

				try {
					const payload = await fetchJson(listUrl + '/' + id, {
						method: 'PUT',
						body: JSON.stringify(body),
					});
					closeModal(editModal);
					showPageSuccess(payload.message || 'Guard personnel updated.');
					await loadGuards();
				} catch (error) {
					const payload = error.payload || {};
					if (payload.errors) applyFieldErrors(editForm, payload.errors);
					showModalAlert(editAlert, error.message || 'Unable to update guard.');
				} finally {
					setSubmitting(editSubmit, cancelBtns, false, 'Saving...', 'Save');
				}
			});

			resetForm.addEventListener('submit', async function (e) {
				e.preventDefault();
				clearFormErrors(resetForm);
				showModalAlert(resetAlert, '');

				const id = document.getElementById('reset_guard_id').value;
				const body = {
					duty_pin: document.getElementById('reset_duty_pin').value,
					duty_pin_confirmation: document.getElementById('reset_duty_pin_confirmation').value,
				};

				const cancelBtns = Array.from(document.querySelectorAll('.js-close-reset-pin'));
				setSubmitting(resetSubmit, cancelBtns, true, 'Updating...', 'Update PIN');

				try {
					const payload = await fetchJson(listUrl + '/' + id + '/pin', {
						method: 'PATCH',
						body: JSON.stringify(body),
					});
					closeModal(resetModal);
					showPageSuccess(payload.message || 'Duty PIN updated.');
					await loadGuards();
				} catch (error) {
					const payload = error.payload || {};
					if (payload.errors) applyFieldErrors(resetForm, payload.errors);
					showModalAlert(resetAlert, error.message || 'Unable to update Duty PIN.');
				} finally {
					setSubmitting(resetSubmit, cancelBtns, false, 'Updating...', 'Update PIN');
				}
			});

			document.getElementById('applyFiltersBtn').addEventListener('click', function () {
				state.page = 1;
				loadGuards();
			});

			document.getElementById('resetFiltersBtn').addEventListener('click', function () {
				els.search.value = '';
				els.status.value = '';
				state.page = 1;
				loadGuards();
			});

			els.search.addEventListener('input', function () {
				clearTimeout(state.searchTimer);
				state.searchTimer = setTimeout(function () {
					state.page = 1;
					loadGuards();
				}, 350);
			});

			els.pageSize.addEventListener('change', function () {
				state.perPage = Number(els.pageSize.value || 10);
				state.page = 1;
				loadGuards();
			});

			els.first.addEventListener('click', function () { state.page = 1; loadGuards(); });
			els.prev.addEventListener('click', function () { state.page = Math.max(1, state.page - 1); loadGuards(); });
			els.next.addEventListener('click', function () { state.page = Math.min(state.lastPage, state.page + 1); loadGuards(); });
			els.last.addEventListener('click', function () { state.page = state.lastPage; loadGuards(); });

			const toggle = document.getElementById('userMenuToggle');
			const group = document.getElementById('userMenuGroup');
			if (toggle && group) {
				toggle.addEventListener('click', function () {
					const open = group.classList.toggle('open');
					toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
				});
			}

			loadGuards();
		})();
	</script>
	@include('admin.partials.admin-responsive-script')
</body>
</html>
