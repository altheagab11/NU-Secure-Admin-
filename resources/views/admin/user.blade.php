<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	@php
		$activeSection = $section ?? 'overview';
	@endphp
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $activeSection === 'guards' ? 'Guards' : ($activeSection === 'offices' ? 'Offices' : 'User Management') }}</title>
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

		* { box-sizing: border-box; }

		body {
			margin: 0;
			background: #eef2ff;
			color: #0f172a;
		}

		.layout {
			display: flex;
			min-height: 100vh;
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
			margin: 8px 0 0;
			color: #64748b;
			font-size: 15px;
		}

		.header-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 12px;
		}

		.add-guard-btn {
			border: 0;
			background: #39459a;
			color: #f4f6ff;
			font-size: 14px;
			font-weight: 600;
			padding: 8px 12px;
			border-radius: 6px;
			display: inline-flex;
			align-items: center;
			gap: 6px;
			cursor: pointer;
		}

		.add-guard-btn svg {
			width: 16px;
			height: 16px;
		}

		.guard-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
			padding: 14px 0 0;
			overflow: hidden;
		}

		.guard-card-head {
			padding: 0 16px 10px;
		}

		.guard-title {
			margin: 0;
			font-size: 20px;
			font-weight: 600;
			color: #1f2937;
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.guard-title svg {
			width: 18px;
			height: 18px;
			color: #4b5cd1;
		}

		.guard-total {
			margin: 8px 0 0;
			font-size: 14px;
			color: #475569;
		}

		.guard-table {
			width: 100%;
			border-collapse: collapse;
		}

		.guard-table th,
		.guard-table td {
			padding: 10px 16px;
			text-align: left;
			font-size: 13px;
		}

		.guard-table th {
			font-weight: 500;
			color: #334155;
			border-bottom: 1px solid #e5e7eb;
		}

		.guard-table td {
			color: #111827;
			border-bottom: 1px solid #edf0f4;
		}

		.guard-table tbody tr:last-child td {
			border-bottom: 0;
		}

		@include('admin.partials.table-pagination-styles')

		/* Filters card (copied-simplified from visitor view) */
		.filters-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			padding: 12px 14px;
			margin-bottom: 12px;
			display: flex;
			gap: 10px;
			align-items: center;
		}

		.filter-input, .filter-select {
			padding: 8px 10px;
			border-radius: 8px;
			border: 1px solid #e6edf7;
			font-size: 14px;
			min-width: 160px;
		}

		.search-wrap { display:flex; align-items:center; gap:8px; }

		.clear-filters-btn { color:#64748b; text-decoration:none; padding:6px 10px; border-radius:8px; border:1px solid transparent; }

		.email-cell {
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		.email-cell svg {
			width: 14px;
			height: 14px;
			color: #111827;
		}

		.badge-pill {
			display: inline-block;
			padding: 2px 8px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 600;
			color: #0369a1;
			background: #dbeafe;
		}

		.action-icons {
			display: inline-flex;
			align-items: center;
			gap: 10px;
		}

		.action-icons svg {
			width: 14px;
			height: 14px;
		}

		.action-edit {
			color: #4b5563;
		}

		.action-delete {
			color: #ef4444;
		}

		.icon-btn {
			border: 0;
			background: transparent;
			padding: 0;
			line-height: 0;
			cursor: pointer;
		}

		.office-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
			padding: 14px 0 0;
			overflow: hidden;
		}

		.office-card-head {
			padding: 0 16px 10px;
		}

		.office-title {
			margin: 0;
			font-size: 20px;
			font-weight: 600;
			color: #1f2937;
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.office-title svg {
			width: 18px;
			height: 18px;
			color: #4b5cd1;
		}

		.office-total {
			margin: 8px 0 0;
			font-size: 14px;
			color: #475569;
		}

		.office-table {
			width: 100%;
			border-collapse: collapse;
		}

		.office-table th,
		.office-table td {
			padding: 10px 16px;
			text-align: left;
			font-size: 13px;
		}

		.office-table th {
			font-weight: 500;
			color: #334155;
			border-bottom: 1px solid #e5e7eb;
		}

		.office-table td {
			color: #111827;
			border-bottom: 1px solid #edf0f4;
		}

		.office-table tbody tr:last-child td {
			border-bottom: 0;
		}

		.office-summary-grid {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 14px;
			margin-top: 18px;
		}

		.office-summary-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
			padding: 14px;
		}

		.office-summary-name {
			margin: 0;
			font-size: 14px;
			color: #1f2937;
		}

		.office-summary-count {
			margin: 10px 0 2px;
			font-size: 30px;
			line-height: 1;
			font-weight: 500;
			color: #111827;
		}

		.office-summary-label {
			font-size: 13px;
			color: #374151;
		}

		@media (max-width: 1200px) {
			.office-summary-grid {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
		}

		@media (max-width: 700px) {
			.header-row {
				flex-direction: column;
				align-items: flex-start;
			}

			.office-summary-grid {
				grid-template-columns: 1fr;
			}

			.brand-title {
				font-size: 18px;
			}
		}

		.action-icons img {
			width: 14px;
			height: 14px;
		}

		.action-edit.edit-office-btn {
			cursor: pointer;
		}

		.header-actions {
			display: flex;
			gap: 8px;
			align-items: center;
		}

		.add-guard-btn.btn-recycle {
			background: #334155;
		}

		.add-guard-btn.btn-restore {
			padding: 6px 10px;
			font-size: 12px;
		}

		.add-guard-btn.btn-force-delete {
			padding: 6px 10px;
			font-size: 12px;
			background: #b91c1c;
		}

		.add-guard-btn.btn-force-delete:hover {
			background: #991b1b;
		}

		.recycle-actions {
			display: flex;
			flex-wrap: wrap;
			gap: 6px;
			align-items: center;
		}

		.flash-success {
			margin: 12px 0;
			padding: 10px;
			border-radius: 8px;
			background: #ecfdf5;
			color: #065f46;
		}

		.flash-error {
			margin: 12px 0;
			padding: 10px 36px 10px 10px;
			border-radius: 8px;
			background: #fff1f2;
			color: #9f1239;
			position: relative;
		}

		.flash-error-close {
			position: absolute;
			top: 6px;
			right: 8px;
			border: 0;
			background: transparent;
			font-size: 18px;
			line-height: 1;
			cursor: pointer;
			color: #9f1239;
		}

		.flash-error ul {
			margin: 0;
			padding-left: 18px;
		}

		.inline-form {
			display: inline;
		}

		.table-empty-cell {
			text-align: center;
			color: #64748b;
			padding: 18px;
		}

		.office-filters-form {
			margin: 12px 0 18px;
		}

		.filter-actions {
			margin-left: auto;
			display: flex;
			gap: 8px;
			align-items: center;
		}

		.user-modal-overlay {
			display: none;
			position: fixed;
			inset: 0;
			background: rgba(2, 6, 23, 0.6);
			align-items: center;
			justify-content: center;
			z-index: 80;
		}

		.user-modal-overlay.z-recycle {
			z-index: 85;
		}

		.user-modal-dialog {
			background: #fff;
			border-radius: 10px;
			width: 520px;
			max-width: 94%;
			padding: 20px;
			box-shadow: 0 10px 30px rgba(2, 6, 23, 0.35);
		}

		.user-modal-dialog.wide {
			width: 560px;
		}

		.user-modal-dialog.recycle {
			width: 760px;
			max-width: 96%;
		}

		.user-modal-head {
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.user-modal-head.with-gap {
			margin-bottom: 8px;
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

		.user-modal-lead.tight {
			margin: 4px 0 14px;
		}

		.user-modal-row {
			display: flex;
			gap: 10px;
		}

		.user-modal-field {
			flex: 1;
		}

		.user-modal-field.stacked {
			margin-top: 10px;
		}

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

		.user-modal-select {
			background: #fff;
		}

		.user-modal-actions {
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			margin-top: 18px;
		}

		.user-modal-footer {
			display: flex;
			justify-content: flex-end;
			margin-top: 14px;
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

		.recycle-table-wrap {
			max-height: 420px;
			overflow: auto;
			border: 1px solid #e5e7eb;
			border-radius: 8px;
		}

		.recycle-table-wrap .guard-table,
		.recycle-table-wrap .office-table {
			margin: 0;
			width: 100%;
		}

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
					$isUserMgmtOpen = request()->is('admin/user*');
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
							<a href="/admin/user/guards" class="submenu-link {{ $activeSection === 'guards' ? 'active' : '' }}">
								<i class="bi bi-shield-fill-check"></i>
								<span>Guards</span>
							</a>
							<a href="/admin/user/offices" class="submenu-link {{ $activeSection === 'offices' ? 'active' : '' }}">
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
			@include('admin.partials.admin-topbar', [
				'title' => $activeSection === 'guards'
					? 'Guard Management'
					: ($activeSection === 'offices' ? 'Office User Management' : 'User Management'),
			])
			@if ($activeSection === 'guards')
				<div class="header-row">
					<div></div>
					<div class="header-actions">
						<button type="button" id="openGuardRecycleBinBtn" class="add-guard-btn btn-recycle">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3 6h18M8 6V4h8v2M7 6l1 14h8l1-14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							Recycle Bin ({{ isset($recycledGuards) ? count($recycledGuards) : 0 }})
						</button>
						<button type="button" id="openAddGuardBtn" class="add-guard-btn">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							Add Guard
						</button>
					</div>
				</div>

				@if(session('success'))
					<div id="guardSuccessAlert" class="flash-success">{{ session('success') }}</div>
					<script nonce="{{ $cspNonce }}">
						setTimeout(function(){
							var el = document.getElementById('guardSuccessAlert');
							if (el) {
								el.style.transition = 'opacity 0.4s ease';
								el.style.opacity = '0';
								setTimeout(function(){ el.remove(); }, 400);
							}
						}, 5000);
					</script>
				@endif

				@if($errors->any())
					<div id="guardErrorAlert" class="js-auto-dismiss-alert flash-error" role="alert">
						<button type="button" class="js-dismiss-alert flash-error-close" aria-label="Close">&times;</button>
						<ul>
							@foreach($errors->all() as $err)
								<li>{{ $err }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<section class="guard-card">
					<div class="guard-card-head">
						<h2 class="guard-title">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 3 5 6v6c0 5.2 3.4 8.6 7 9.9 3.6-1.3 7-4.7 7-9.9V6l-7-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							Guard Accounts
						</h2>
						<p class="guard-total">Total Guards: {{ (isset($guards) && method_exists($guards, 'total')) ? $guards->total() : count($guards ?? []) }}</p>
					</div>

					<table class="guard-table" aria-label="Guard accounts table">
						<thead>
							<tr>
								<th>Name</th>
								<th>Email</th>
								<th>Badge Number</th>
								<th>Station</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							@forelse($guards ?? [] as $guard)
								<tr>
									<td>{{ $guard->name }}</td>
									<td>
										<span class="email-cell">
											<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
												<path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											{{ $guard->email }}
										</span>
									</td>
									<td><span class="badge-pill">{{ $guard->badge_number ?? $guard->badge ?? '' }}</span></td>
									<td>{{ $guard->station ?? '—' }}</td>
									<td>
										<span class="action-icons">
											<img src="{{ asset('picture/bx_edit.png') }}" alt="Edit" class="action-edit edit-guard-btn"
												 data-user-id="{{ $guard->user_id ?? '' }}"
												 data-first-name="{{ $guard->first_name ?? '' }}"
												 data-last-name="{{ $guard->last_name ?? '' }}"
												 data-name="{{ $guard->name ?? '' }}"
												 data-email="{{ $guard->email ?? '' }}"
												 data-badge-number="{{ $guard->badge_number ?? $guard->badge ?? '' }}"
												 data-station="{{ $guard->station ?? '' }}"
											/>
											<form method="POST" action="/admin/user/guards/{{ $guard->user_id ?? '' }}" class="inline-form" data-confirm="Move this guard account to recycle bin?">
												@csrf
												@method('DELETE')
												<button type="submit" class="icon-btn" aria-label="Delete">
													<img src="{{ asset('picture/Vector.png') }}" alt="Delete" class="action-delete" />
												</button>
											</form>
										</span>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="table-empty-cell">No guards found.</td>
								</tr>
							@endforelse
						</tbody>
					</table>

					@include('admin.partials.table-pagination', [
						'paginator' => $guards,
						'perPageParam' => 'per_page',
						'ariaLabel' => 'Guard table pagination',
					])
				</section>
			@elseif ($activeSection === 'offices')
				<div class="header-row">
					<div></div>
					<div class="header-actions">
						<button type="button" id="openRecycleBinBtn" class="add-guard-btn btn-recycle">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3 6h18M8 6V4h8v2M7 6l1 14h8l1-14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							Recycle Bin ({{ isset($recycledOffices) ? count($recycledOffices) : 0 }})
						</button>
						<button type="button" id="openAddOfficeBtn" class="add-guard-btn">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								<circle cx="10" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
								<path d="M22 8h-6M19 5v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							Add Office User
						</button>
					</div>
				</div>

				{{-- Filters for offices (placed between page title and Office User Accounts) --}}
				<form method="GET" action="" class="office-filters-form">
					<div class="filters-card" role="search" aria-label="Office filters">
						<div class="search-wrap">
							<input class="filter-input" type="text" name="search" value="{{ request('search', '') }}" placeholder="Search name or email" aria-label="Search users">
						</div>

						<select name="office" class="filter-select" aria-label="Filter by office" data-auto-submit>
							<option value="">All offices</option>
							@foreach($officeOptions ?? [] as $opt)
								<option value="{{ $opt->office_id ?? $opt->id }}" @selected(request('office') == ($opt->office_id ?? $opt->id))>{{ $opt->office_name ?? $opt->name }}</option>
							@endforeach
						</select>

						<select name="position" class="filter-select" aria-label="Filter by position" data-auto-submit>
							<option value="">All positions</option>
							@foreach($positions ?? [] as $pos)
								@if(!empty($pos))
									<option value="{{ $pos }}" @selected(request('position') === (string) $pos)>{{ $pos }}</option>
								@endif
							@endforeach
						</select>

						<div class="filter-actions">
							<button type="submit" class="add-guard-btn">Apply</button>
							<a href="/admin/user/offices" class="clear-filters-btn">Clear</a>
						</div>
					</div>
				</form>

				@if(session('success'))
					<div id="officeSuccessAlert" class="flash-success">{{ session('success') }}</div>
					<script nonce="{{ $cspNonce }}">
						setTimeout(function(){
							var el = document.getElementById('officeSuccessAlert');
							if (el) {
								el.style.transition = 'opacity 0.4s ease';
								el.style.opacity = '0';
								setTimeout(function(){ el.remove(); }, 400);
							}
						}, 5000);
					</script>
				@endif

				@if($errors->any())
					<div id="officeErrorAlert" class="js-auto-dismiss-alert flash-error" role="alert">
						<button type="button" class="js-dismiss-alert flash-error-close" aria-label="Close">&times;</button>
						<ul>
							@foreach($errors->all() as $err)
								<li>{{ $err }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<section class="office-card">
					<div class="office-card-head">
						<h2 class="office-title">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M4 21h16M7 21V6h10v15M10 9h1M13 9h1M10 12h1M13 12h1M10 15h1M13 15h1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							Office User Accounts
						</h2>
						<p class="office-total">Total Users: {{ isset($totalUsers) ? $totalUsers : count($offices ?? []) }} across {{ isset($totalOffices) ? $totalOffices : count($officeOptions ?? []) }} offices</p>
					</div>

					<table class="office-table" aria-label="Office user accounts table">
						<thead>
							<tr>
								<th>Name</th>
								<th>Email</th>
								<th>Office</th>
								<th>Position</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							@forelse($offices ?? [] as $office)
								<tr>
									<td>{{ $office->name ?? ($office->first_name ? ($office->first_name . ' ' . ($office->last_name ?? '')) : ($office->full_name ?? '')) }}</td>
									<td>
										<span class="email-cell">
											<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
												<path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											{{ $office->email ?? '' }}
										</span>
									</td>
									<td><span class="badge-pill">{{ $office->office_name ?? $office->office ?? '—' }}</span></td>
									<td>{{ $office->position ?? '—' }}</td>
									<td>
										<span class="action-icons">
											<img src="{{ asset('picture/bx_edit.png') }}" alt="Edit" class="action-edit edit-office-btn"
												 data-user-id="{{ $office->user_id ?? '' }}"
												 data-name="{{ $office->name ?? '' }}"
												 data-email="{{ $office->email ?? '' }}"
												 data-office-id="{{ $office->office_id ?? '' }}"
												 data-position="{{ $office->position ?? '' }}"
											/>
											<form method="POST" action="/admin/user/offices/{{ $office->user_id ?? '' }}" class="inline-form" data-confirm="Move this office user to recycle bin?">
												@csrf
												@method('DELETE')
												<button type="submit" class="icon-btn" aria-label="Delete">
													<img src="{{ asset('picture/Vector.png') }}" alt="Delete" class="action-delete" />
												</button>
											</form>
										</span>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="table-empty-cell">No users found.</td>
								</tr>
							@endforelse
						</tbody>
					</table>

					@include('admin.partials.table-pagination', [
						'paginator' => $offices,
						'perPageParam' => 'per_page',
						'ariaLabel' => 'Office table pagination',
					])
				</section>

				<div class="office-summary-grid">
					@php
						// Prefer pre-computed $officeSummaries from controller (total counts across all users).
						if (isset($officeSummaries)) {
							$officeSummaries = collect($officeSummaries);
						} else {
							// Fallback: build office summary from current $offices (may be paginated - this will reflect current page only)
							$collectionForSummary = (isset($offices) && method_exists($offices, 'items')) ? collect($offices->items()) : collect($offices ?? []);
							$officeSummaries = $collectionForSummary->groupBy(function($item) {
								return data_get($item, 'office_name') ?? data_get($item, 'office') ?? 'Unknown Office';
							})->map(function($group, $name) {
								return ['name' => $name, 'count' => count($group)];
							})->values();
						}
					@endphp

					@forelse($officeSummaries as $os)
						<div class="office-summary-card">
							<p class="office-summary-name">{{ $os['name'] }}</p>
							<p class="office-summary-count">{{ $os['count'] }}</p>
							<span class="office-summary-label">Active users</span>
						</div>
					@empty
						<div class="office-summary-card">
							<p class="office-summary-name">No offices</p>
							<p class="office-summary-count">0</p>
							<span class="office-summary-label">Active users</span>
						</div>
					@endforelse
				</div>

			@else
				<p class="page-subtitle">
					Manage user accounts from this section.
				</p>
			@endif


		</main>
	</div>

	<script nonce="{{ $cspNonce }}">
		@include('admin.partials.table-pagination-script')

		const userMenuGroup = document.getElementById('userMenuGroup');
		const userMenuToggle = document.getElementById('userMenuToggle');

		if (userMenuGroup && userMenuToggle) {
			userMenuToggle.addEventListener('click', () => {
				const isOpen = userMenuGroup.classList.toggle('open');
				userMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		}

		// Modal open/close and form handling for Add / Edit Guard
		(function() {
			const addBtn = document.getElementById('openAddGuardBtn');

			function getModal() { return document.getElementById('addGuardModal'); }
			function getForm() { return document.getElementById('addGuardForm'); }
			function getHiddenFullName() { return document.getElementById('fullNameHidden'); }
			function getLead() {
				const modal = getModal();
				return modal ? modal.querySelector('.user-modal-lead') : null;
			}

			function cleanupEditState() {
				const form = getForm();
				if (!form) return;

				const methodInput = form.querySelector('input[name="_method"][data-edit-added]');
				if (methodInput) methodInput.remove();

				const uidInput = form.querySelector('input[name="user_id"][data-edit-added]');
				if (uidInput) uidInput.remove();

				if (form.dataset.originalAction) {
					form.setAttribute('action', form.dataset.originalAction);
				}

				const emailInput = form.querySelector('input[name="email"]');
				if (emailInput) {
					emailInput.readOnly = false;
					emailInput.required = true;
					emailInput.removeAttribute('aria-readonly');
					emailInput.style.background = '';
					emailInput.style.cursor = '';
				}

				const title = document.getElementById('addGuardTitle');
				if (title) title.textContent = 'Add Guard Account';

				const lead = getLead();
				if (lead) lead.textContent = 'Create a new security guard account for the system.';

				const submitBtn = form.querySelector('button[type=submit]');
				if (submitBtn) submitBtn.textContent = 'Add User';
			}

			function resetFormFields() {
				const form = getForm();
				if (!form) return;
				form.reset();
				const hiddenFullName = getHiddenFullName();
				if (hiddenFullName) hiddenFullName.value = '';
			}

			function showModal() {
				const modal = getModal();
				if (!modal) return;
				modal.style.display = 'flex';
				const first = modal.querySelector('input[name="first_name"]');
				if (first) first.focus();
				if (!modal._backdropAttached) {
					modal.addEventListener('click', (e) => {
						if (e.target === modal) {
							cleanupEditState();
							hideModal();
						}
					});
					modal._backdropAttached = true;
				}
			}

			function hideModal() {
				const modal = getModal();
				if (!modal) return;
				modal.style.display = 'none';
			}

			if (addBtn) {
				addBtn.addEventListener('click', () => {
					cleanupEditState();
					resetFormFields();
					showModal();
				});
			}

			document.addEventListener('click', (e) => {
				if (e.target && e.target.id === 'closeAddGuard') {
					cleanupEditState();
					hideModal();
				}
				if (e.target && e.target.id === 'cancelAddGuard') {
					cleanupEditState();
					hideModal();
				}
			});

			document.addEventListener('submit', (e) => {
				if (e.target && e.target.id === 'addGuardForm') {
					const form = e.target;
					const fn = form.querySelector('input[name="first_name"]')?.value?.trim() || '';
					const ln = form.querySelector('input[name="last_name"]')?.value?.trim() || '';
					const hiddenFullName = getHiddenFullName();
					if (hiddenFullName) hiddenFullName.value = (fn + (fn && ln ? ' ' : '') + ln).trim();
				}
			});

			document.querySelectorAll('.edit-guard-btn').forEach(function (btn) {
				btn.addEventListener('click', function (e) {
					e.preventDefault();
					const ds = btn.dataset || {};
					const userId = ds.userId || ds.user_id || ds.userid;
					if (!userId) return;

					const form = getForm();
					if (!form) return;

					if (!form.dataset.originalAction) {
						form.dataset.originalAction = form.getAttribute('action');
					}

					cleanupEditState();

					let first = (ds.firstName || ds.first_name || '').trim();
					let last = (ds.lastName || ds.last_name || '').trim();
					if (!first && !last) {
						const parts = String(ds.name || '').trim().split(/\s+/).filter(Boolean);
						first = parts.shift() || '';
						last = parts.join(' ') || '';
					}

					form.querySelector('input[name="first_name"]').value = first;
					form.querySelector('input[name="last_name"]').value = last;

					const emailInput = form.querySelector('input[name="email"]');
					if (emailInput) {
						emailInput.value = ds.email || '';
						emailInput.readOnly = true;
						emailInput.required = false;
						emailInput.setAttribute('aria-readonly', 'true');
						emailInput.style.background = '#f8fafc';
						emailInput.style.cursor = 'not-allowed';
					}

					form.querySelector('input[name="badge_number"]').value = ds.badgeNumber || ds.badge_number || '';
					form.querySelector('input[name="station"]').value = ds.station || '';

					const methodInput = document.createElement('input');
					methodInput.type = 'hidden';
					methodInput.name = '_method';
					methodInput.value = 'PUT';
					methodInput.setAttribute('data-edit-added', '1');
					form.appendChild(methodInput);

					const uidInput = document.createElement('input');
					uidInput.type = 'hidden';
					uidInput.name = 'user_id';
					uidInput.value = userId;
					uidInput.setAttribute('data-edit-added', '1');
					form.appendChild(uidInput);

					form.setAttribute('action', '/admin/user/guards/' + encodeURIComponent(userId));

					const title = document.getElementById('addGuardTitle');
					if (title) title.textContent = 'Edit Guard Account';

					const lead = getLead();
					if (lead) lead.textContent = 'Update this security guard account.';

					const submitBtn = form.querySelector('button[type=submit]');
					if (submitBtn) submitBtn.textContent = 'Save Changes';

					showModal();
				});
			});
		})();
	</script>

	<!-- Add Guard Modal -->
	<div id="addGuardModal" class="user-modal-overlay">
		<div role="dialog" aria-modal="true" aria-labelledby="addGuardTitle" class="user-modal-dialog">
			<div class="user-modal-head">
				<h3 id="addGuardTitle" class="user-modal-title">Add Guard Account</h3>
				<button id="closeAddGuard" class="user-modal-close" aria-label="Close">&times;</button>
			</div>

			<p class="user-modal-lead">Create a new security guard account for the system.</p>
			<form id="addGuardForm" method="POST" action="/admin/user/guards">
				@csrf
				<div class="user-modal-row">
					<div class="user-modal-field">
						<label class="user-modal-label">First Name</label>
						<input name="first_name" class="user-modal-input" type="text" required>
					</div>
					<div class="user-modal-field">
						<label class="user-modal-label">Last Name</label>
						<input name="last_name" class="user-modal-input" type="text" required>
					</div>
				</div>
				<input type="hidden" name="name" id="fullNameHidden">
				<div class="user-modal-field stacked">
					<label class="user-modal-label">Email</label>
					<input name="email" class="user-modal-input" type="email" required>
				</div>
				<div class="user-modal-field stacked">
					<label class="user-modal-label">Badge Number</label>
					<input name="badge_number" class="user-modal-input" type="text">
				</div>
				<div class="user-modal-field stacked">
					<label class="user-modal-label">Station</label>
					<input name="station" class="user-modal-input" type="text">
				</div>
				<div class="user-modal-actions">
					<button type="button" id="cancelAddGuard" class="user-modal-btn-cancel">Cancel</button>
					<button type="submit" class="user-modal-btn-submit">Add User</button>
				</div>
			</form>
		</div>
	</div>


	<!-- Add Office User Modal -->
	<div id="addOfficeModal" class="user-modal-overlay">
		<div role="dialog" aria-modal="true" aria-labelledby="addOfficeTitle" class="user-modal-dialog wide">
			<div class="user-modal-head">
				<h3 id="addOfficeTitle" class="user-modal-title">Add Office User Account</h3>
				<button id="closeAddOffice" class="user-modal-close" aria-label="Close">&times;</button>
			</div>

			<p class="user-modal-lead">Create a new office staff account for the system.</p>
			<form id="addOfficeForm" method="POST" action="/admin/user/offices">
				@csrf
				<div class="user-modal-row">
					<div class="user-modal-field">
						<label class="user-modal-label">First Name</label>
						<input name="first_name" class="user-modal-input" type="text" required>
					</div>
					<div class="user-modal-field">
						<label class="user-modal-label">Last Name</label>
						<input name="last_name" class="user-modal-input" type="text" required>
					</div>
				</div>
				<input type="hidden" name="name" id="fullNameOfficeHidden">
				<div class="user-modal-field stacked">
					<label class="user-modal-label">Email</label>
					<input name="email" class="user-modal-input" type="email" required>
				</div>
				<div class="user-modal-field stacked">
					<label class="user-modal-label">Office</label>
					<select name="office_id" class="user-modal-select" required>
						<option value="" disabled selected>Select Office</option>
						@foreach($officeOptions ?? [] as $officeOption)
							<option value="{{ $officeOption->office_id }}">{{ $officeOption->office_name }}</option>
						@endforeach
					</select>
				</div>
				<div class="user-modal-field stacked">
					<label class="user-modal-label">Position</label>
					<input name="position" class="user-modal-input" type="text">
				</div>
				<div class="user-modal-actions">
					<button type="button" id="cancelAddOffice" class="user-modal-btn-cancel">Cancel</button>
					<button type="submit" class="user-modal-btn-submit">Add User</button>
				</div>
			</form>
		</div>
	</div>

	<!-- Guard Recycle Bin Modal -->
	<div id="guardRecycleBinModal" class="user-modal-overlay z-recycle">
		<div role="dialog" aria-modal="true" aria-labelledby="guardRecycleBinTitle" class="user-modal-dialog recycle">
			<div class="user-modal-head with-gap">
				<h3 id="guardRecycleBinTitle" class="user-modal-title">Guard Recycle Bin</h3>
				<button id="closeGuardRecycleBin" class="user-modal-close" aria-label="Close">&times;</button>
			</div>

			<p class="user-modal-lead tight">Deleted guard accounts are stored here. You can restore them or permanently delete them.</p>

			<div class="recycle-table-wrap">
				<table class="guard-table" aria-label="Guard recycle bin table">
					<thead>
						<tr>
							<th>Name</th>
							<th>Email</th>
							<th>Badge Number</th>
							<th>Station</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						@forelse(($recycledGuards ?? collect([])) as $recycled)
							<tr>
								<td>{{ $recycled->name ?? '—' }}</td>
								<td>{{ $recycled->email ?? '—' }}</td>
								<td>{{ $recycled->badge_number ?? '—' }}</td>
								<td>{{ $recycled->station ?? '—' }}</td>
								<td>
									<div class="recycle-actions">
										<form method="POST" action="/admin/user/guards/{{ $recycled->user_id }}/restore" class="inline-form">
											@csrf
											<button type="submit" class="add-guard-btn btn-restore">Restore</button>
										</form>
										<form method="POST" action="/admin/user/guards/{{ $recycled->user_id }}/force" class="inline-form" data-confirm="Permanently delete this guard account? This cannot be undone.">
											@csrf
											@method('DELETE')
											<button type="submit" class="add-guard-btn btn-force-delete">Delete Permanently</button>
										</form>
									</div>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="5" class="table-empty-cell">Recycle bin is empty.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div class="user-modal-footer">
				<button type="button" id="cancelGuardRecycleBin" class="user-modal-btn-cancel">Close</button>
			</div>
		</div>
	</div>

	<!-- Office Recycle Bin Modal -->
	<div id="officeRecycleBinModal" class="user-modal-overlay z-recycle">
		<div role="dialog" aria-modal="true" aria-labelledby="officeRecycleBinTitle" class="user-modal-dialog recycle">
			<div class="user-modal-head with-gap">
				<h3 id="officeRecycleBinTitle" class="user-modal-title">Office Recycle Bin</h3>
				<button id="closeOfficeRecycleBin" class="user-modal-close" aria-label="Close">&times;</button>
			</div>

			<p class="user-modal-lead tight">Deleted office users are stored here. You can restore them or permanently delete them.</p>

			<div class="recycle-table-wrap">
				<table class="office-table" aria-label="Office recycle bin table">
					<thead>
						<tr>
							<th>Name</th>
							<th>Email</th>
							<th>Office</th>
							<th>Position</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						@forelse(($recycledOffices ?? collect([])) as $recycled)
							<tr>
								<td>{{ $recycled->name ?? '—' }}</td>
								<td>{{ $recycled->email ?? '—' }}</td>
								<td>{{ $recycled->office_name ?? '—' }}</td>
								<td>{{ $recycled->position ?? '—' }}</td>
								<td>
									<div class="recycle-actions">
										<form method="POST" action="/admin/user/offices/{{ $recycled->user_id }}/restore" class="inline-form">
											@csrf
											<button type="submit" class="add-guard-btn btn-restore">Restore</button>
										</form>
										<form method="POST" action="/admin/user/offices/{{ $recycled->user_id }}/force" class="inline-form" data-confirm="Permanently delete this office user? This cannot be undone.">
											@csrf
											@method('DELETE')
											<button type="submit" class="add-guard-btn btn-force-delete">Delete Permanently</button>
										</form>
									</div>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="5" class="table-empty-cell">Recycle bin is empty.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div class="user-modal-footer">
				<button type="button" id="cancelOfficeRecycleBin" class="user-modal-btn-cancel">Close</button>
			</div>
		</div>
	</div>

	<script nonce="{{ $cspNonce }}">
		// Modal open/close and form handling for Add Office User
		(function() {
			try {
				const openBtn = document.getElementById('openAddOfficeBtn');
				function getModal() { return document.getElementById('addOfficeModal'); }
				function getHiddenFullName() { return document.getElementById('fullNameOfficeHidden'); }

				function showModal() {
					const modal = getModal(); if (!modal) return; modal.style.display = 'flex';
					const first = modal.querySelector('input[name="first_name"]'); if (first) first.focus();
					if (!modal._backdropAttached) { modal.addEventListener('click', (e) => { if (e.target === modal) hideModal(); }); modal._backdropAttached = true; }
				}

				function hideModal() { const modal = getModal(); if (!modal) return; modal.style.display = 'none'; }

				if (openBtn) openBtn.addEventListener('click', showModal);
				document.addEventListener('click', (e) => { if (e.target && e.target.id === 'closeAddOffice') hideModal(); if (e.target && e.target.id === 'cancelAddOffice') hideModal(); });

				document.addEventListener('submit', (e) => {
					if (e.target && e.target.id === 'addOfficeForm') {
						const form = e.target;
						const fn = form.querySelector('input[name="first_name"]')?.value?.trim() || '';
						const ln = form.querySelector('input[name="last_name"]')?.value?.trim() || '';
						const hidden = getHiddenFullName(); if (hidden) hidden.value = (fn + (fn && ln ? ' ' : '') + ln).trim();
					}
				});
			} catch (err) { console && console.error && console.error('Office modal init error:', err); }
		})();

		// Recycle bin modal open/close
		(function() {
			try {
				const openBtn = document.getElementById('openRecycleBinBtn');
				const modal = document.getElementById('officeRecycleBinModal');

				function openModal() { if (modal) modal.style.display = 'flex'; }
				function closeModal() { if (modal) modal.style.display = 'none'; }

				if (openBtn) openBtn.addEventListener('click', openModal);
				document.addEventListener('click', function(e){
					if (!e.target) return;
					if (e.target.id === 'closeOfficeRecycleBin' || e.target.id === 'cancelOfficeRecycleBin') closeModal();
				});

				if (modal) {
					modal.addEventListener('click', function(e){
						if (e.target === modal) closeModal();
					});
				}
			} catch (err) {
				console && console.error && console.error('Recycle bin modal init error:', err);
			}
		})();

		// Guard recycle bin modal open/close
		(function() {
			try {
				const openBtn = document.getElementById('openGuardRecycleBinBtn');
				const modal = document.getElementById('guardRecycleBinModal');

				function openModal() { if (modal) modal.style.display = 'flex'; }
				function closeModal() { if (modal) modal.style.display = 'none'; }

				if (openBtn) openBtn.addEventListener('click', openModal);
				document.addEventListener('click', function(e){
					if (!e.target) return;
					if (e.target.id === 'closeGuardRecycleBin' || e.target.id === 'cancelGuardRecycleBin') closeModal();
				});

				if (modal) {
					modal.addEventListener('click', function(e){
						if (e.target === modal) closeModal();
					});
				}
			} catch (err) {
				console && console.error && console.error('Guard recycle bin modal init error:', err);
			}
		})();

			// Edit button: reuse Add Office modal to perform edits
			(function(){
				function cleanupEditState() {
					const form = document.getElementById('addOfficeForm');
					if (!form) return;
					const methodInput = form.querySelector('input[name="_method"][data-edit-added]');
					if (methodInput) methodInput.remove();
					const uidInput = form.querySelector('input[name="user_id"][data-edit-added]');
					if (uidInput) uidInput.remove();
					if (form.dataset.originalAction) form.setAttribute('action', form.dataset.originalAction);
					const emailInput = form.querySelector('input[name="email"]');
					if (emailInput) {
						emailInput.readOnly = false;
						emailInput.removeAttribute('aria-readonly');
						emailInput.style.background = '';
						emailInput.style.cursor = '';
					}
					const title = document.getElementById('addOfficeTitle'); if (title) title.textContent = 'Add Office User Account';
					const submitBtn = form.querySelector('button[type=submit]'); if (submitBtn) submitBtn.textContent = 'Add User';
				}

				document.querySelectorAll('.edit-office-btn').forEach(function(btn){
					btn.addEventListener('click', function(e){
						e.preventDefault();
						const ds = btn.dataset || {};
						const userId = ds.userId || ds.user_id || ds.userid;
						if (!userId) return;

						const modal = document.getElementById('addOfficeModal'); if (!modal) return; modal.style.display = 'flex';
						const form = document.getElementById('addOfficeForm'); if (!form) return;

						// store original action so we can restore later
						if (!form.dataset.originalAction) form.dataset.originalAction = form.getAttribute('action');

						// fill fields (split name into first/last)
						const fullName = ds.name || '';
						const parts = fullName.trim().split(/\s+/);
						const first = parts.shift() || '';
						const last = parts.join(' ') || '';
						form.querySelector('input[name="first_name"]').value = first;
						form.querySelector('input[name="last_name"]').value = last;
						const emailInput = form.querySelector('input[name="email"]');
						if (emailInput) {
							emailInput.value = ds.email || '';
							emailInput.readOnly = true;
							emailInput.setAttribute('aria-readonly', 'true');
							emailInput.style.background = '#f8fafc';
							emailInput.style.cursor = 'not-allowed';
						}
						const sel = form.querySelector('select[name="office_id"]'); if (sel) sel.value = ds.officeId || ds.office_id || '';
						form.querySelector('input[name="position"]').value = ds.position || '';

						// add method override _method = PUT
						if (!form.querySelector('input[name="_method"][data-edit-added]')){
							const methodInput = document.createElement('input');
							methodInput.type = 'hidden'; methodInput.name = '_method'; methodInput.value = 'PUT'; methodInput.setAttribute('data-edit-added','1');
							form.appendChild(methodInput);
						}

						// add hidden user_id
						if (!form.querySelector('input[name="user_id"][data-edit-added]')){
							const uidInput = document.createElement('input');
							uidInput.type = 'hidden'; uidInput.name = 'user_id'; uidInput.value = userId; uidInput.setAttribute('data-edit-added','1');
							form.appendChild(uidInput);
						}

						// set form action to update URL
						form.setAttribute('action', '/admin/user/offices/' + encodeURIComponent(userId));

						// update modal title and submit text
						const title = document.getElementById('addOfficeTitle'); if (title) title.textContent = 'Edit Office User';
						const submitBtn = form.querySelector('button[type=submit]'); if (submitBtn) submitBtn.textContent = 'Save Changes';
					});
				});

				// cleanup when modal closed via close or cancel buttons
				document.addEventListener('click', function(e){
					if (e.target && (e.target.id === 'closeAddOffice' || e.target.id === 'cancelAddOffice')) {
						cleanupEditState();
					}
				});

				// cleanup on backdrop click as well
				const modal = document.getElementById('addOfficeModal');
				if (modal) {
					modal.addEventListener('click', function(e){
						if (e.target === modal) cleanupEditState();
					});
				}
			})();
	</script>

	<script nonce="{{ $cspNonce }}">
		(function () {
			function dismissAlert(el) {
				if (!el || el.dataset.dismissing === '1') return;
				el.dataset.dismissing = '1';
				el.style.transition = 'opacity 0.4s ease';
				el.style.opacity = '0';
				setTimeout(function () { el.remove(); }, 400);
			}

			document.querySelectorAll('.js-auto-dismiss-alert').forEach(function (el) {
				setTimeout(function () { dismissAlert(el); }, 5000);
			});

			document.addEventListener('click', function (e) {
				var btn = e.target.closest('.js-dismiss-alert');
				if (!btn) return;
				dismissAlert(btn.closest('.js-auto-dismiss-alert'));
			});
		})();
	</script>
	@include('admin.partials.admin-responsive-script')
	</body>
	</html>
