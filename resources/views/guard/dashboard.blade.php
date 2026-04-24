<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Guard Dashboard</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
			background: rgba(255,255,255,0.18);
			border-radius: 10px;
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
		}

		.brand-title {
			font-size: 28px;
			font-weight: 800;
			color: #ffd84d;
			line-height: 1;
		}

		.brand-subtitle {
			color: rgba(255,255,255,0.78);
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

		.submenu {
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
			min-height: 100vh;
		}

		.dashboard-wrap {
			padding: 24px;
		}

		.dashboard-header {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			gap: 16px;
			margin-bottom: 24px;
			flex-wrap: wrap;
		}

		.page-title {
			font-size: 30px;
			font-weight: 700;
			margin: 0 0 6px;
			color: #111827;
		}

		.page-subtitle {
			margin: 0;
			font-size: 14px;
			color: #6b7280;
		}

		.header-meta {
			display: flex;
			align-items: center;
			gap: 12px;
			flex-wrap: wrap;
		}

		.live-badge {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			background: #ffffff;
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

		.last-update {
			font-size: 13px;
			color: #6b7280;
		}

		.stats-grid {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 18px;
			margin-bottom: 18px;
		}

		.stat-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 18px;
			padding: 20px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
			transition: 0.2s ease;
		}

		.stat-card:hover {
			transform: translateY(-2px);
		}

		.stat-card-primary {
			background: linear-gradient(135deg, #3949ab, #2f3f9d);
			color: #fff;
			border: none;
		}

		.stat-card-primary .stat-label,
		.stat-card-primary .stat-text {
			color: rgba(255, 255, 255, 0.85);
		}

		.stat-top {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 18px;
		}

		.stat-label {
			font-size: 14px;
			color: #6b7280;
			font-weight: 600;
		}

		.stat-icon {
			width: 42px;
			height: 42px;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 16px;
		}

		.soft-green {
			background: #eafaf1;
			color: #16a34a;
		}

		.soft-yellow {
			background: #fff8e6;
			color: #d97706;
		}

		.soft-gray {
			background: #f3f4f6;
			color: #6b7280;
		}

		.soft-blue-light {
			background: rgba(255, 255, 255, 0.18);
			color: #ffffff;
		}

		.stat-number {
			margin: 0 0 6px;
			font-size: 32px;
			font-weight: 700;
			line-height: 1.1;
		}

		.stat-text {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
		}

		.overview-strip {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 14px;
			margin-bottom: 18px;
		}

		.overview-item {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 14px;
			padding: 14px 16px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
		}

		.overview-title {
			display: block;
			font-size: 12px;
			color: #6b7280;
			margin-bottom: 6px;
		}

		.overview-item strong {
			font-size: 20px;
			color: #111827;
		}

		.action-grid {
			display: grid;
			grid-template-columns: 1.4fr 1fr;
			gap: 18px;
			margin-bottom: 18px;
		}

		.action-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 18px;
			padding: 22px;
			display: flex;
			align-items: center;
			gap: 16px;
			text-decoration: none;
			color: inherit;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
			transition: all 0.2s ease;
		}

		.action-card:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
			color: inherit;
		}

		.action-card-primary {
			background: linear-gradient(135deg, #3949ab, #2f3f9d);
			border: none;
			color: #ffffff;
		}

		.action-card-primary .action-content p {
			color: rgba(255, 255, 255, 0.82);
		}

		.action-icon {
			width: 52px;
			height: 52px;
			min-width: 52px;
			border-radius: 14px;
			background: #fef3c7;
			color: #d97706;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 18px;
		}

		.action-icon.secondary {
			background: #eef2ff;
			color: #3949ab;
		}

		.action-content {
			flex: 1;
		}

		.action-content h3 {
			margin: 0 0 6px;
			font-size: 18px;
			font-weight: 700;
		}

		.action-content p {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
			line-height: 1.5;
		}

		.action-arrow {
			font-size: 18px;
			opacity: 0.8;
		}

		.alert-banner {
			background: #ffffff;
			border: 1px solid #f3e8b3;
			border-left: 5px solid #facc15;
			border-radius: 18px;
			padding: 18px 20px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 16px;
			margin-bottom: 20px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
		}

		.alert-banner-left {
			display: flex;
			align-items: center;
			gap: 14px;
		}

		.alert-banner-icon {
			width: 46px;
			height: 46px;
			border-radius: 12px;
			background: #fff8e6;
			color: #d97706;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 18px;
		}

		.alert-banner h4 {
			margin: 0 0 4px;
			font-size: 16px;
			font-weight: 700;
		}

		.alert-banner p {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
		}

		.alert-banner-btn {
			text-decoration: none;
			background: #111827;
			color: #ffffff;
			padding: 10px 16px;
			border-radius: 10px;
			font-size: 13px;
			font-weight: 600;
		}

		.panel-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 20px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
			overflow: hidden;
		}

		.panel-header {
			padding: 20px 22px;
			border-bottom: 1px solid #e5e7eb;
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 16px;
			flex-wrap: wrap;
		}

		.panel-header h3 {
			margin: 0 0 4px;
			font-size: 18px;
			font-weight: 700;
		}

		.panel-header p {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
		}

		.panel-actions {
			display: flex;
			gap: 10px;
			flex-wrap: wrap;
		}

		.search-input,
		.filter-select {
			height: 42px;
			border: 1px solid #e5e7eb;
			background: #ffffff;
			border-radius: 10px;
			padding: 0 14px;
			font-size: 14px;
			color: #1f2937;
			outline: none;
		}

		.search-input {
			min-width: 220px;
		}

		.search-input:focus,
		.filter-select:focus {
			border-color: #c7d2fe;
			box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08);
		}

		.visitor-table-wrap {
			width: 100%;
			overflow-x: auto;
		}

		.visitor-table {
			width: 100%;
			border-collapse: collapse;
		}

		.visitor-table thead th {
			text-align: left;
			font-size: 12px;
			font-weight: 700;
			color: #6b7280;
			padding: 16px 22px;
			background: #fafbfc;
			border-bottom: 1px solid #e5e7eb;
			text-transform: uppercase;
			letter-spacing: 0.04em;
		}

		.visitor-table tbody td {
			padding: 18px 22px;
			border-bottom: 1px solid #f1f5f9;
			vertical-align: middle;
			font-size: 14px;
		}

		.visitor-table tbody tr:hover {
			background: #fafcff;
		}

		.visitor-cell {
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.avatar-circle {
			width: 42px;
			height: 42px;
			border-radius: 50%;
			background: #eef2ff;
			color: #3949ab;
			font-weight: 700;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 13px;
		}

		.visitor-name {
			font-weight: 600;
			color: #111827;
		}

		.visitor-meta {
			font-size: 12px;
			color: #6b7280;
			margin-top: 2px;
		}

		.status-badge {
			display: inline-flex;
			align-items: center;
			padding: 7px 12px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 700;
		}

		.status-badge.arrived {
			background: #eafaf1;
			color: #15803d;
		}

		.status-badge.transit {
			background: #eaf4ff;
			color: #2563eb;
		}

		.status-badge.exit {
			background: #fff8e6;
			color: #b45309;
		}

		.table-link {
			color: #3949ab;
			text-decoration: none;
			font-weight: 600;
		}

		.table-link:hover {
			text-decoration: underline;
		}

		@media (max-width: 1024px) {
			.layout {
				flex-direction: column;
			}

			.sidebar {
				width: 100%;
				min-height: auto;
			}

			.main {
				display: block;
			}
		}

		@media (max-width: 1200px) {
			.stats-grid,
			.overview-strip {
				grid-template-columns: repeat(2, 1fr);
			}

			.action-grid {
				grid-template-columns: 1fr;
			}
		}

		@media (max-width: 768px) {
			.dashboard-wrap {
				padding: 16px;
			}

			.stats-grid,
			.overview-strip {
				grid-template-columns: 1fr;
			}

			.panel-actions {
				width: 100%;
				flex-direction: column;
			}

			.search-input,
			.filter-select {
				width: 100%;
			}

			.alert-banner {
				flex-direction: column;
				align-items: flex-start;
			}

			.visitor-table thead th,
			.visitor-table tbody td {
				padding: 14px 16px;
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
					<a href="/guard/register" class="quick-action-btn">
						<i class="bi bi-qr-code-scan"></i>
						<span>Register Visitor</span>
					</a>
				</div>
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

				<a href="{{ route('logout') }}" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
					<i class="bi bi-box-arrow-right"></i>
					<span>Logout</span>
				</a>

				<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
					@csrf
				</form>
			</div>
		</aside>

		<main class="main">
			<div class="dashboard-wrap">
				<div class="dashboard-header">
					<div>
						<h1 class="page-title">Guard Dashboard</h1>
						<p class="page-subtitle">Monitor visitors, alerts, and campus activity in real time.</p>
					</div>

					<div class="header-meta">
						<div class="live-badge">
							<span class="dot"></span>
							Live Monitoring
						</div>
						<div class="last-update">Last updated: <span id="lastUpdateTime">--:-- --</span></div>
					</div>
				</div>

				<div class="stats-grid">
					<div class="stat-card">
						<div class="stat-top">
							<span class="stat-label">Active Visitors</span>
							<div class="stat-icon soft-green">
								<i class="fas fa-users"></i>
							</div>
						</div>
						<h2 class="stat-number">3</h2>
						<p class="stat-text">Currently inside campus</p>
					</div>

					<div class="stat-card stat-card-primary">
						<div class="stat-top">
							<span class="stat-label">Current Time</span>
							<div class="stat-icon soft-blue-light">
								<i class="fas fa-clock"></i>
							</div>
						</div>
						<h2 class="stat-number" id="currentTimeValue">--:-- --</h2>
						<p class="stat-text">System time</p>
					</div>

					<div class="stat-card">
						<div class="stat-top">
							<span class="stat-label">Active Alerts</span>
							<div class="stat-icon soft-yellow">
								<i class="fas fa-bell"></i>
							</div>
						</div>
						<h2 class="stat-number">2</h2>
						<p class="stat-text">1 overstay • 1 ready to exit</p>
					</div>

					<div class="stat-card">
						<div class="stat-top">
							<span class="stat-label">Exited Today</span>
							<div class="stat-icon soft-gray">
								<i class="fas fa-arrow-right-from-bracket"></i>
							</div>
						</div>
						<h2 class="stat-number">12</h2>
						<p class="stat-text">Completed visitor exits</p>
					</div>
				</div>

				<div class="overview-strip">
					<div class="overview-item">
						<span class="overview-title">Entries Today</span>
						<strong>15</strong>
					</div>
					<div class="overview-item">
						<span class="overview-title">Pending Exit Scans</span>
						<strong>1</strong>
					</div>
					<div class="overview-item">
						<span class="overview-title">Unresolved Alerts</span>
						<strong>1</strong>
					</div>
					<div class="overview-item">
						<span class="overview-title">Resolved Today</span>
						<strong>6</strong>
					</div>
				</div>

				<div class="action-grid">
					<a href="/guard/register?type=normal" class="action-card action-card-primary">
						<div class="action-icon">
							<i class="fas fa-user-plus"></i>
						</div>
						<div class="action-content">
							<h3>Register New Visitor</h3>
							<p>Capture face and ID, fill out form, and generate QR ticket.</p>
						</div>
						<div class="action-arrow">
							<i class="fas fa-arrow-right"></i>
						</div>
					</a>

					<a href="/guard/exit" class="action-card">
						<div class="action-icon secondary">
							<i class="fas fa-qrcode"></i>
						</div>
						<div class="action-content">
							<h3>Exit Scan</h3>
							<p>Scan visitor QR code to process exit quickly and accurately.</p>
						</div>
						<div class="action-arrow">
							<i class="fas fa-arrow-right"></i>
						</div>
					</a>
				</div>

				<div class="alert-banner">
					<div class="alert-banner-left">
						<div class="alert-banner-icon">
							<i class="fas fa-bell"></i>
						</div>
						<div>
							<h4>Attention Needed</h4>
							<p>1 visitor is ready to exit and waiting for QR scan.</p>
						</div>
					</div>

					<a href="/guard/alert" class="alert-banner-btn">View Alerts</a>
				</div>

				<div class="panel-card">
					<div class="panel-header">
						<div>
							<h3>Active Visitors Inside Campus</h3>
							<p>Track current visitor movement and status.</p>
						</div>

						<div class="panel-actions">
							<input type="text" class="search-input" placeholder="Search visitor..." />
							<select class="filter-select">
								<option>All Status</option>
								<option>Arrived</option>
								<option>In Transit</option>
								<option>Ready to Exit</option>
							</select>
						</div>
					</div>

					<div class="visitor-table-wrap">
						<table class="visitor-table">
							<thead>
								<tr>
									<th>Visitor</th>
									<th>Department</th>
									<th>Entry Time</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<div class="visitor-cell">
											<div class="avatar-circle">RK</div>
											<div>
												<div class="visitor-name">Robert Kim</div>
												<div class="visitor-meta">Pass No: PASS-0012</div>
											</div>
										</div>
									</td>
									<td>Finance Department</td>
									<td>09:23 AM</td>
									<td><span class="status-badge arrived">Arrived</span></td>
									<td><a href="#" class="table-link">View</a></td>
								</tr>

								<tr>
									<td>
										<div class="visitor-cell">
											<div class="avatar-circle">MG</div>
											<div>
												<div class="visitor-name">Maria Garcia</div>
												<div class="visitor-meta">Pass No: PASS-0015</div>
											</div>
										</div>
									</td>
									<td>IT Department</td>
									<td>11:53 AM</td>
									<td><span class="status-badge transit">In Transit</span></td>
									<td><a href="#" class="table-link">View</a></td>
								</tr>

								<tr>
									<td>
										<div class="visitor-cell">
											<div class="avatar-circle">JA</div>
											<div>
												<div class="visitor-name">John Anderson</div>
												<div class="visitor-meta">Pass No: PASS-0018</div>
											</div>
										</div>
									</td>
									<td>Human Resources</td>
									<td>10:23 AM</td>
									<td><span class="status-badge arrived">Arrived</span></td>
									<td><a href="#" class="table-link">View</a></td>
								</tr>

								<tr>
									<td>
										<div class="visitor-cell">
											<div class="avatar-circle">LC</div>
											<div>
												<div class="visitor-name">Liam Cruz</div>
												<div class="visitor-meta">Pass No: PASS-0021</div>
											</div>
										</div>
									</td>
									<td>Registrar</td>
									<td>01:14 PM</td>
									<td><span class="status-badge exit">Ready to Exit</span></td>
									<td><a href="#" class="table-link">View</a></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</main>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		const timeTarget = document.getElementById('currentTimeValue');
		const lastUpdateTarget = document.getElementById('lastUpdateTime');
		if (timeTarget && lastUpdateTarget) {
			const formatter = new Intl.DateTimeFormat('en-US', {
				hour: '2-digit',
				minute: '2-digit',
				hour12: true
			});

			const updateClock = () => {
				const now = new Date();
				const formattedTime = formatter.format(now);
				timeTarget.textContent = formattedTime;
				lastUpdateTarget.textContent = formattedTime;
			};

			updateClock();
			setInterval(updateClock, 30000);
		}
	</script>
</body>
</html>