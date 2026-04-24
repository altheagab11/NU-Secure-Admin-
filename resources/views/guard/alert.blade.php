<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Active Alerts</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
	<style>
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
			background: var(--sidebar-bg);
			color: #0f172a;
		}

		.layout {
			display: flex;
			min-height: 100vh;
		}

		.sidebar {
			width: 280px;
			background: var(--sidebar-bg);
			color: var(--text-white);
			padding: 12px 10px;
			display: flex;
			flex-direction: column;
			border-right: 1px solid rgba(0, 0, 0, 0.05);
		}

		.brand-row {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 6px 10px 2px;
			margin-bottom: 18px;
		}

		.brand-icon {
			width: 32px;
			height: 32px;
			background: #f6f8ff;
			border-radius: 6px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #273272;
			flex-shrink: 0;
		}

		.brand-title {
			margin: 0;
			font-size: 0;
			line-height: 1;
			font-weight: 800;
			letter-spacing: -0.02em;
			display: flex;
			gap: 4px;
			align-items: baseline;
		}

		.brand-title span:first-child {
			color: var(--text-yellow);
			font-size: 24px;
		}

		.brand-title span:last-child {
			color: #eef2ff;
			font-size: 22px;
			font-weight: 700;
		}

		.brand-subtitle {
			margin: 2px 0 0;
			color: var(--muted);
			font-size: 12px;
			white-space: nowrap;
		}

		.menu {
			display: flex;
			flex-direction: column;
			gap: 6px;
		}

		.menu-item {
			display: block;
			text-decoration: none;
			color: var(--text-white);
			padding: 9px 12px;
			border-radius: 6px;
			font-size: 16px;
			font-weight: 500;
			line-height: 1.2;
		}

		.menu-item .inner {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.menu-item svg {
			flex-shrink: 0;
			width: 22px;
			height: 22px;
		}

		.menu-item.active {
			background: var(--sidebar-bg-light);
			color: var(--text-yellow);
		}

		.menu-item:hover {
			background: rgba(255, 255, 255, 0.08);
		}

		.menu-group {
			display: flex;
			flex-direction: column;
			gap: 6px;
		}

		.menu-toggle {
			width: 100%;
			border: 0;
			background: transparent;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}

		.menu-toggle.active {
			background: var(--sidebar-bg-light);
			color: var(--text-yellow);
		}

		.menu-toggle .caret {
			width: 16px;
			height: 16px;
			transition: transform 0.2s ease;
		}

		.menu-group.open .menu-toggle .caret {
			transform: rotate(180deg);
		}

		.submenu {
			display: none;
			flex-direction: column;
			gap: 4px;
			margin-left: 34px;
		}

		.menu-group.open .submenu {
			display: flex;
		}

		.submenu-item {
			text-decoration: none;
			color: var(--text-white);
			font-size: 14px;
			font-weight: 500;
			padding: 6px 10px;
			border-radius: 6px;
		}

		.submenu-item:hover {
			background: rgba(255, 255, 255, 0.08);
		}

		.submenu-item.active {
			background: var(--sidebar-bg-light);
			color: var(--text-yellow);
		}

		.spacer {
			flex: 1;
		}

		.bottom {
			border-top: 2px solid var(--line);
			margin: 0 -10px;
			padding: 14px 10px 14px;
		}

		.admin-row {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			font-size: 16px;
			font-weight: 500;
			margin-bottom: 12px;
		}

		.admin-row svg {
			width: 20px;
			height: 20px;
		}

		.logout-wrap {
			display: flex;
			justify-content: center;
		}

		.logout-btn {
			border: 0;
			background: #ecedf2;
			color: #ff0000;
			font-weight: 700;
			font-size: 17px;
			border-radius: 14px;
			padding: 8px 18px;
			display: inline-flex;
			align-items: center;
			gap: 8px;
			cursor: pointer;
		}

		.logout-btn svg {
			width: 18px;
			height: 18px;
		}

		/* Register page sidebar parity */
		.layout {
			display: flex;
			height: 100vh;
			overflow: hidden;
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
			overflow-y: auto;
			overflow-x: hidden;
			z-index: 1000;
		}

		.sidebar::-webkit-scrollbar {
			width: 6px;
		}

		.sidebar::-webkit-scrollbar-thumb {
			background: rgba(255,255,255,0.18);
			border-radius: 10px;
		}

		.menu {
			display: block;
		}

		.sidebar-brand {
			gap: 12px;
			padding: 10px 10px 18px;
			margin-bottom: 10px;
			border-bottom: 1px solid rgba(255,255,255,0.12);
		}

		.brand-icon {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			background: rgba(255,255,255,0.14);
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 22px;
			flex-shrink: 0;
			color: inherit;
		}

		.brand-title {
			font-size: 28px;
			font-weight: 800;
			color: #ffd84d;
			line-height: 1;
			display: block;
			gap: 0;
			letter-spacing: 0;
		}

		.brand-subtitle {
			color: rgba(255,255,255,0.78);
			font-size: 12px;
			display: block;
			margin-top: 2px;
			white-space: normal;
		}

		.sidebar-section {
			margin-top: 18px;
		}

		.sidebar-label {
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 1px;
			color: rgba(255,255,255,0.55);
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
			background: rgba(255,255,255,0.10);
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
		}

		.dropdown-arrow {
			transition: transform 0.25s ease;
			font-size: 13px;
		}

		.sidebar-toggle[aria-expanded="true"] .dropdown-arrow {
			transform: rotate(180deg);
		}

		.collapse .submenu {
			margin: 6px 0 8px 14px;
			padding-left: 14px;
			border-left: 1px solid rgba(255,255,255,0.15);
			display: grid;
			gap: 5px;
		}

		.submenu-link {
			display: flex;
			align-items: center;
			gap: 10px;
			color: rgba(255,255,255,0.88);
			text-decoration: none;
			padding: 10px 12px;
			border-radius: 10px;
			margin-bottom: 0;
			font-size: 14px;
			transition: all 0.2s ease;
		}

		.submenu-link:hover {
			background: rgba(255,255,255,0.10);
			color: #fff;
			transform: translateX(3px);
		}

		.submenu-link.active {
			background: rgba(255,255,255,0.16);
			color: #ffd84d;
			font-weight: 600;
		}

		.quick-action-btn {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			width: 100%;
			text-decoration: none;
			background: #ffd84d;
			color: #243c96;
			font-weight: 700;
			border-radius: 12px;
			padding: 12px 14px;
			margin-top: 6px;
			transition: all 0.25s ease;
		}

		.quick-action-btn:hover {
			background: #ffcf1f;
			color: #243c96;
			transform: translateY(-2px);
			box-shadow: 0 10px 18px rgba(255, 216, 77, 0.25);
		}

		.sidebar-footer {
			padding-top: 16px;
			margin-top: 20px;
			border-top: 1px solid rgba(255,255,255,0.12);
		}

		.admin-card {
			display: flex;
			align-items: center;
			gap: 12px;
			background: rgba(255,255,255,0.08);
			border-radius: 14px;
			padding: 12px;
			margin-bottom: 12px;
		}

		.admin-avatar {
			width: 42px;
			height: 42px;
			border-radius: 50%;
			background: rgba(255,255,255,0.15);
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
			color: rgba(255,255,255,0.72);
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
			background: #f5f7fb;
			padding: 0;
			overflow-y: auto;
			margin-left: 260px;
		}

		.alerts-page-wrap {
			padding: 24px;
		}

		.alerts-page-header {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			gap: 16px;
			flex-wrap: wrap;
			margin-bottom: 24px;
		}

		.alerts-page-title {
			margin: 0 0 6px;
			font-size: 30px;
			font-weight: 700;
			color: #111827;
		}

		.alerts-page-subtitle {
			margin: 0;
			font-size: 14px;
			color: #6b7280;
		}

		.alerts-page-meta {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.live-badge {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			background: #fff;
			border: 1px solid #e5e7eb;
			padding: 10px 14px;
			border-radius: 999px;
			font-size: 13px;
			font-weight: 600;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
		}

		.dot {
			width: 8px;
			height: 8px;
			background: #22c55e;
			border-radius: 50%;
			display: inline-block;
		}

		.alerts-summary-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(220px, 280px));
			gap: 18px;
			margin-bottom: 22px;
			justify-content: center;
		}

		.alerts-summary-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 16px;
			padding: 20px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
			transition: 0.2s ease;
		}

		.alerts-summary-card:hover {
			transform: translateY(-2px);
		}

		.summary-top {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 14px;
		}

		.summary-label {
			font-size: 15px;
			font-weight: 600;
			color: #111827;
		}

		.summary-icon {
			width: 40px;
			height: 40px;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 16px;
		}

		.soft-red {
			background: #fdecec;
			color: #dc2626;
		}

		.soft-green {
			background: #eafaf1;
			color: #16a34a;
		}

		.soft-green-lite {
			background: #ecfdf3;
			color: #16a34a;
		}

		.summary-number {
			margin: 0 0 4px;
			font-size: 34px;
			font-weight: 700;
			line-height: 1.1;
		}

		.summary-text {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
		}

		.alerts-panel-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 20px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
			margin-bottom: 22px;
			overflow: hidden;
		}

		.alerts-panel-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 16px;
			flex-wrap: wrap;
			padding: 22px;
			border-bottom: 1px solid #e5e7eb;
		}

		.alerts-panel-title-wrap {
			display: flex;
			align-items: flex-start;
			gap: 12px;
		}

		.section-icon {
			width: 42px;
			height: 42px;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 16px;
			flex-shrink: 0;
		}

		.alerts-panel-header h3 {
			margin: 0 0 4px;
			font-size: 18px;
			font-weight: 700;
		}

		.alerts-panel-header p {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
		}

		.alerts-panel-actions {
			display: flex;
			align-items: center;
			gap: 10px;
			flex-wrap: wrap;
		}

		.alerts-search-input,
		.alerts-filter-select {
			height: 42px;
			border: 1px solid #e5e7eb;
			background: #fff;
			border-radius: 10px;
			padding: 0 14px;
			font-size: 14px;
			color: #1f2937;
			outline: none;
		}

		.alerts-search-input {
			min-width: 220px;
		}

		.alerts-search-input:focus,
		.alerts-filter-select:focus {
			border-color: #c7d2fe;
			box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08);
		}

		.alerts-list {
			padding: 20px 22px 22px;
		}

		.alert-visitor-item {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 18px;
			background: #f9fafb;
			border: 1px solid #e5e7eb;
			border-radius: 16px;
			padding: 18px;
			transition: 0.2s ease;
			flex-wrap: wrap;
		}

		.alert-visitor-item.completed {
			background: #f0fdf4;
			border-color: #86efac;
		}

		.alert-visitor-item:hover {
			transform: translateY(-1px);
		}

		.alert-visitor-left {
			display: flex;
			align-items: center;
			gap: 14px;
		}

		.alert-avatar {
			width: 48px;
			height: 48px;
			border-radius: 50%;
			background: #eef2ff;
			color: #3949ab;
			font-weight: 700;
			font-size: 14px;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.alert-visitor-info h4 {
			margin: 0 0 4px;
			font-size: 18px;
			font-weight: 700;
			color: #111827;
		}

		.alert-visitor-info p {
			margin: 0 0 4px;
			font-size: 14px;
			color: #374151;
		}

		.alert-visitor-info span {
			font-size: 13px;
			color: #6b7280;
		}

		.alert-visitor-right {
			display: flex;
			align-items: center;
			gap: 12px;
			flex-wrap: wrap;
		}

		.alert-status-badge {
			display: inline-flex;
			align-items: center;
			padding: 8px 12px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 700;
		}

		.alert-status-badge.ready {
			background: #dcfce7;
			color: #15803d;
		}

		.alert-action-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			height: 40px;
			padding: 0 16px;
			border-radius: 10px;
			background: #111827;
			color: #fff;
			text-decoration: none;
			font-size: 13px;
			font-weight: 600;
			transition: 0.2s ease;
		}

		.alert-action-btn:hover {
			background: #1f2937;
			color: #fff;
		}

		.alerts-empty-card {
			background: #ffffff;
			border: 1px dashed #d1d5db;
			border-radius: 20px;
			padding: 32px 24px;
			text-align: center;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
		}

		.alerts-empty-icon {
			width: 56px;
			height: 56px;
			margin: 0 auto 14px;
			border-radius: 50%;
			background: #f3f4f6;
			color: #6b7280;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 22px;
		}

		.alerts-empty-card h3 {
			margin: 0 0 8px;
			font-size: 18px;
			font-weight: 700;
			color: #111827;
		}

		.alerts-empty-card p {
			margin: 0;
			font-size: 14px;
			color: #6b7280;
		}

		@media (max-width: 1024px) {
			.sidebar {
				width: 100%;
				min-height: 100vh;
				position: relative;
			}

			.main {
				display: block;
				margin-left: 0;
			}
		}

		@media (max-width: 992px) {
			.alerts-summary-grid {
				grid-template-columns: 1fr 1fr;
				justify-content: stretch;
			}
		}

		@media (max-width: 768px) {
			.alerts-page-wrap {
				padding: 16px;
			}

			.alerts-summary-grid {
				grid-template-columns: 1fr;
			}

			.alerts-panel-actions {
				width: 100%;
				flex-direction: column;
			}

			.alerts-search-input,
			.alerts-filter-select {
				width: 100%;
			}

			.alert-visitor-item {
				align-items: flex-start;
			}

			.alert-visitor-right {
				width: 100%;
				justify-content: flex-start;
			}
		}
	</style>
</head>
<body>
	<div class="layout">
		@php
			$guardSidebarUser = auth()->user();
			$guardSidebarName = trim(((string) ($guardSidebarUser->first_name ?? '')).' '.((string) ($guardSidebarUser->last_name ?? '')));
			$guardSidebarName = $guardSidebarName !== '' ? $guardSidebarName : ((string) ($guardSidebarUser->name ?? $guardSidebarUser->email ?? 'Guard Officer'));
			$isSelfRegisteredRole = (int) optional($guardSidebarUser)->role_id === 4;
		@endphp

		<aside class="sidebar d-flex flex-column justify-content-between">
			<div>
				<div class="sidebar-brand d-flex align-items-center">
					<div class="brand-icon">
						<i class="bi bi-shield-lock-fill"></i>
					</div>
					<div>
						<h4 class="brand-title mb-0">SVMS Guard</h4>
						<small class="brand-subtitle">Smart Visitor Monitoring System</small>
					</div>
				</div>

				<nav class="menu" aria-label="Sidebar Navigation">
					@if (! $isSelfRegisteredRole)
						<div class="sidebar-section">
							<p class="sidebar-label">MAIN</p>
							<a href="/guard/dashboard" class="sidebar-link {{ request()->is('guard/dashboard') ? 'active' : '' }}">
								<span class="sidebar-icon"><i class="bi bi-grid-1x2-fill"></i></span>
								<span class="sidebar-text">Dashboard</span>
							</a>
						</div>
					@endif

					<div class="sidebar-section">
						<p class="sidebar-label">VISITOR ACTIONS</p>

						<div class="sidebar-dropdown">
							<button class="sidebar-link sidebar-toggle {{ request()->is('guard/register*') ? 'active open' : '' }}"
								type="button"
								data-bs-toggle="collapse"
								data-bs-target="#registerMenu"
								aria-expanded="{{ request()->is('guard/register*') ? 'true' : 'false' }}">
								<span class="d-flex align-items-center gap-2">
									<span class="sidebar-icon"><i class="bi bi-person-plus-fill"></i></span>
									<span class="sidebar-text">Register</span>
								</span>
								<span class="dropdown-arrow"><i class="bi bi-chevron-down"></i></span>
							</button>

							<div class="collapse {{ request()->is('guard/register*') ? 'show' : '' }}" id="registerMenu">
								<div class="submenu">
									<a href="/guard/register?type=normal" class="submenu-link {{ request('type', 'normal') === 'normal' ? 'active' : '' }}">
										<i class="bi bi-person"></i>
										<span>Normal Visitor</span>
									</a>

									<a href="/guard/register?type=enrollee" class="submenu-link {{ request('type') === 'enrollee' ? 'active' : '' }}">
										<i class="bi bi-mortarboard-fill"></i>
										<span>Enrollee</span>
									</a>

									<a href="/guard/register?type=contractor" class="submenu-link {{ request('type') === 'contractor' ? 'active' : '' }}">
										<i class="bi bi-briefcase-fill"></i>
										<span>Contractor</span>
									</a>
								</div>
							</div>
						</div>

						@if (! $isSelfRegisteredRole)
							<a href="/guard/exit" class="sidebar-link {{ request()->is('guard/exit') ? 'active' : '' }}">
								<span class="sidebar-icon"><i class="bi bi-box-arrow-right"></i></span>
								<span class="sidebar-text">Exit Scan</span>
							</a>
						@endif
					</div>

					@if (! $isSelfRegisteredRole)
						<div class="sidebar-section">
							<p class="sidebar-label">MONITORING</p>
							<a href="/guard/alert" class="sidebar-link {{ request()->is('guard/alert') ? 'active' : '' }}">
								<span class="sidebar-icon"><i class="bi bi-bell-fill"></i></span>
								<span class="sidebar-text">Active Alerts</span>
								<span class="sidebar-badge">{{ $activeAlertsCount ?? 0 }}</span>
							</a>
						</div>
					@endif

					<div class="sidebar-section">
						<p class="sidebar-label">QUICK ACTION</p>

						<a href="/guard/register?type=normal" class="quick-action-btn">
							<i class="bi bi-qr-code-scan"></i>
							<span>Register Visitor</span>
						</a>
					</div>
				</nav>
			</div>

			<div class="sidebar-footer">
				<div class="admin-card">
					<div class="admin-avatar">
						<i class="bi bi-person-badge-fill"></i>
					</div>
					<div class="admin-info">
						<h6 class="mb-0">{{ $guardSidebarName }}</h6>
						<small>Guard Officer</small>
					</div>
				</div>

				<a href="{{ route('logout') }}"
				   class="logout-btn"
				   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
					<i class="bi bi-box-arrow-right"></i>
					<span>Logout</span>
				</a>

				<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
					@csrf
				</form>
			</div>
		</aside>

		<main class="main">
			<div class="alerts-page-wrap">
				<div class="alerts-page-header">
					<div>
						<h1 class="alerts-page-title">Active Alerts</h1>
						<p class="alerts-page-subtitle">Monitor visitor alerts and completed visits that are ready for exit processing.</p>
					</div>

					<div class="alerts-page-meta">
						<div class="live-badge">
							<span class="dot"></span>
							Live Status
						</div>
					</div>
				</div>

				<div class="alerts-summary-grid">
					<div class="alerts-summary-card">
						<div class="summary-top">
							<span class="summary-label">Wrong Office</span>
							<div class="summary-icon soft-red">
								<i class="fas fa-circle-xmark"></i>
							</div>
						</div>
						<h2 class="summary-number">0</h2>
						<p class="summary-text">Active alerts</p>
					</div>

					<div class="alerts-summary-card">
						<div class="summary-top">
							<span class="summary-label">Completed</span>
							<div class="summary-icon soft-green">
								<i class="fas fa-circle-check"></i>
							</div>
						</div>
						<h2 class="summary-number">1</h2>
						<p class="summary-text">Ready to exit</p>
					</div>
				</div>

				<div class="alerts-panel-card">
					<div class="alerts-panel-header">
						<div class="alerts-panel-title-wrap">
							<div class="section-icon soft-green-lite">
								<i class="fas fa-check-circle"></i>
							</div>
							<div>
								<h3>Completed Visitors</h3>
								<p>Visitors who have completed their business and are ready to exit.</p>
							</div>
						</div>

						<div class="alerts-panel-actions">
							<input type="text" class="alerts-search-input" placeholder="Search visitor..." />
							<select class="alerts-filter-select">
								<option>All</option>
								<option>Ready to Exit</option>
								<option>Completed</option>
								<option>Wrong Office</option>
							</select>
						</div>
					</div>

					<div class="alerts-list">
						<div class="alert-visitor-item completed">
							<div class="alert-visitor-left">
								<div class="alert-avatar">RK</div>
								<div class="alert-visitor-info">
									<h4>Robert Kim</h4>
									<p>Finance Department • ID123456</p>
									<span>Completed at: 5:00 PM</span>
								</div>
							</div>

							<div class="alert-visitor-right">
								<span class="alert-status-badge ready">Ready to Exit</span>
								<a href="#" class="alert-action-btn">Process Exit</a>
							</div>
						</div>
					</div>
				</div>

				<div class="alerts-empty-card">
					<div class="alerts-empty-icon">
						<i class="fas fa-shield-check"></i>
					</div>
					<h3>No wrong office alerts</h3>
					<p>There are currently no visitors flagged for wrong office routing.</p>
				</div>
			</div>
		</main>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>