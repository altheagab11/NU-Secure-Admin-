<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin Dashboard</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
			font-size: 28px;
			font-weight: 800;
			color: #ffd84d;
			line-height: 1;
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

		.dashboard-title {
			margin: 0 0 14px 0;
			font-size: 28px;
			font-weight: 700;
			letter-spacing: -0.02em;
			color: #0b111b;
		}

		.stats-grid {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 14px;
			margin-bottom: 16px;
		}

		.stat-card {
			position: relative;
			background: #ffffff;
			border-radius: 10px;
			padding: 14px 14px 10px;
			box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
			border: 1px solid #cfd3d8;
		}

		.stat-card.visitors {
			background: #e9eef8;
		}

		.stat-card.inside {
			background: #e9f7ef;
		}

		.stat-card.offices {
			background: #f8f3e9;
		}

		.stat-card.duration {
			background: #f3edf8;
		}

		.stat-icon {
			width: 24px;
			height: 24px;
			border-radius: 6px;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 10px;
		}

		.stat-icon svg {
			width: 14px;
			height: 14px;
		}

		.stat-card.visitors .stat-icon {
			background: #e8f0ff;
			color: #4b5cd1;
		}

		.stat-card.inside .stat-icon {
			background: #e8ffe8;
			color: #10b981;
		}

		.stat-card.offices .stat-icon {
			background: #fff5e6;
			color: #f59e0b;
		}

		.stat-card.duration .stat-icon {
			background: #f3e8ff;
			color: #a855f7;
		}

		.stat-number {
			font-size: 34px;
			font-weight: 500;
			color: #0f172a;
			margin: 4px 0 0;
			line-height: 1.1;
		}

		.stat-label {
			font-size: 13px;
			color: #111827;
			margin: 2px 0 0;
		}

		.stat-trend {
			position: absolute;
			right: 12px;
			top: 8px;
			color: #94a3b8;
		}

		.stat-trend svg {
			width: 12px;
			height: 12px;
		}

		.charts-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 16px 14px;
			margin-bottom: 18px;
		}

		.chart-card {
			background: #ffffff;
			border-radius: 10px;
			padding: 10px 12px 12px;
			box-shadow: 0 2px 6px rgba(0, 0, 0, 0.14);
			border: 1px solid #cfd3d8;
		}

		.chart-title {
			font-size: 22px;
			font-weight: 500;
			color: #0f172a;
			margin: 0 0 8px;
			text-align: center;
		}

		.chart-body {
			height: 230px;
			background: #f4f5f6;
			border-radius: 8px;
			padding: 10px;
			border: 1px solid #e5e7eb;
			overflow: hidden;
		}

		.chart-svg {
			width: 100%;
			height: 100%;
			display: block;
		}

		.axis,
		.grid-line {
			stroke: #d6d7d9;
			stroke-width: 1;
		}

		.x-label,
		.y-label,
		.chart-note {
			font-size: 12px;
			fill: #374151;
			font-family: inherit;
		}

		.plot-line {
			fill: none;
			stroke: #f0c512;
			stroke-width: 4;
			stroke-linejoin: round;
			stroke-linecap: round;
		}

		.plot-dot {
			fill: #f0c512;
			stroke: #f0c512;
			stroke-width: 3;
		}

		.bar-vertical {
			fill: #343b8f;
			rx: 5;
		}

		.bar-horizontal {
			fill: #f4cc17;
			rx: 8;
		}

		.insights-card {
			background: #d7d9e4;
			border-radius: 10px;
			padding: 14px 16px;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
			border: 1px solid #9aa3c5;
		}

		.insights-title {
			font-size: 30px;
			font-weight: 600;
			color: #111827;
			margin: 0 0 6px;
			display: flex;
			align-items: center;
			gap: 8px;

		.alert-summary-card {
			border: 1px solid #e9ecef;
			transition: all 0.25s ease;
			box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
			position: relative;
			overflow: hidden;
		}

		.alert-summary-card:hover {
			transform: translateY(-4px);
			box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
		}

		.critical-card {
			background: linear-gradient(135deg, #ffe5e8, #ffd6db);
			border-left: 4px solid #dc3545;
		}

		.high-card {
			background: linear-gradient(135deg, #fff4d6, #ffe9ad);
			border-left: 4px solid #f0ad00;
		}

		.medium-card {
			background: linear-gradient(135deg, #dff6fb, #c8edf7);
			border-left: 4px solid #0dcaf0;
		}

		.low-card {
			background: linear-gradient(135deg, #f1f3f5, #e2e6ea);
			border-left: 4px solid #6c757d;
		}

		.alert-icon {
			width: 42px;
			height: 42px;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.1rem;
		}

		.critical-icon {
			background: rgba(220, 53, 69, 0.12);
			color: #dc3545;
		}

		.high-icon {
			background: rgba(255, 193, 7, 0.18);
			color: #c58b00;
		}

		.medium-icon {
			background: rgba(13, 202, 240, 0.16);
			color: #0aa2c0;
		}

		.low-icon {
			background: rgba(108, 117, 125, 0.14);
			color: #6c757d;
		}

		.mini-summary-box {
			background: #f8f9fa;
			border: 1px solid #e9ecef;
			box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4);
		}
		}

		.insights-title svg {
			width: 30px;
			height: 30px;
			color: #404c93;
		}

		.insights-list {
			list-style: none;
			margin: 0;
			padding: 0 0 0 2px;
			display: flex;
			flex-direction: column;
			gap: 2px;
		}

		.insights-list li {
			font-size: 33px;
			line-height: 1.22;
			color: #111827;
			display: flex;
			align-items: flex-start;
			gap: 8px;
		}

		.insights-list li:before {
			content: "•";
			color: #1f2937;
			font-weight: bold;
			margin-right: 4px;
			flex-shrink: 0;
			line-height: 1.1;
		}

		@media (max-width: 1680px) {
			.dashboard-title {
				font-size: 28px;
			}

			.insights-title {
				font-size: 22px;
			}

			.insights-title svg {
				width: 22px;
				height: 22px;
			}

			.insights-list li {
				font-size: 28px;
			}
		}

		@media (max-width: 1440px) {
			.layout {
				min-width: 1040px;
			}

			.main {
				padding: 18px;
			}

			.dashboard-title {
				font-size: 28px;
			}

			.chart-title {
				font-size: 18px;
			}

			.insights-list li {
				font-size: 18px;
			}
		}

		@media (max-width: 1200px) {
			.stats-grid {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}

			.charts-grid {
				grid-template-columns: 1fr;
			}
		}

		@media (max-width: 720px) {
			.sidebar-link,
			.admin-info h6,
			.logout-btn {
				font-size: 14px;
			}

			.layout {
				min-width: 900px;
			}

			.sidebar {
				width: 220px;
				padding: 10px 8px;
			}

			.main {
				margin-left: 220px;
			}

			.brand-title {
				font-size: 18px;
			}
		}
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
						<h4 class="brand-title mb-0">SVMS Admin</h4>
						<small class="brand-subtitle">Smart Visitor Monitoring System</small>
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
					<a href="/admin/visitor" class="sidebar-link {{ request()->is('admin/visitor*') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-people-fill"></i></span>
						<span class="sidebar-text">Visitor Monitoring</span>
					</a>
					<a href="/admin/alerts" class="sidebar-link {{ request()->is('admin/alerts*') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-exclamation-triangle-fill"></i></span>
						<span class="sidebar-text">Alerts</span>
						<span class="sidebar-badge">5</span>
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
			<div class="container-fluid pt-0 pb-4">

				<div class="d-flex justify-content-between align-items-center mb-4">
					<h2 class="fw-bold mb-0">Dashboard Overview</h2>
					<div class="text-muted small">Last updated: just now</div>
				</div>

				<div class="row g-3 mb-4">
					<div class="col-md-6 col-xl-3">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<div class="text-muted small mb-2">Total Visitors Today</div>
								<h2 class="fw-bold mb-0">{{ $totalVisitorsToday ?? 3 }}</h2>
							</div>
						</div>
					</div>

					<div class="col-md-6 col-xl-3">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<div class="text-muted small mb-2">Currently Inside</div>
								<h2 class="fw-bold mb-0">{{ $currentlyInside ?? 3 }}</h2>
							</div>
						</div>
					</div>

					<div class="col-md-6 col-xl-3">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<div class="text-muted small mb-2">Active Offices</div>
								<h2 class="fw-bold mb-0">{{ $activeOffices ?? 8 }}</h2>
							</div>
						</div>
					</div>

					<div class="col-md-6 col-xl-3">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<div class="text-muted small mb-2">Average Duration</div>
								<h2 class="fw-bold mb-0">{{ $averageDuration ?? '45m' }}</h2>
							</div>
						</div>
					</div>
				</div>

				<div class="row g-4 mb-4">
					<div class="col-lg-8">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center mb-3">
									<h5 class="fw-semibold mb-0">Alerts Summary</h5>
									<a href="/admin/alerts" class="btn btn-sm btn-outline-primary rounded-3">
										View All Alerts
									</a>
								</div>

								<div class="row g-3">
									<div class="col-md-6 col-xl-3">
										<a href="{{ url('/admin/alerts') . '?severity=Critical' }}" class="text-decoration-none">
											<div class="alert-summary-card critical-card p-3 rounded-4 h-100">
												<div class="d-flex justify-content-between align-items-start mb-2">
													<div>
														<div class="small text-muted fw-medium">Critical Alerts</div>
														<h2 class="fw-bold text-danger mb-0">{{ $criticalAlerts ?? 2 }}</h2>
													</div>
													<div class="alert-icon critical-icon">
														<i class="bi bi-exclamation-octagon-fill"></i>
													</div>
												</div>

												<div class="small text-danger fw-semibold mb-1">
													<i class="bi bi-arrow-up-short"></i> +1 from yesterday
												</div>
												<div class="small text-muted mb-1">
													Top Issue: Unauthorized
												</div>
												<div class="small text-muted mb-2">
													Last alert: 5 mins ago
												</div>

												<div class="progress rounded-pill" style="height: 6px;">
													<div class="progress-bar bg-danger" style="width: 75%"></div>
												</div>
											</div>
										</a>
									</div>

									<div class="col-md-6 col-xl-3">
										<a href="{{ url('/admin/alerts') . '?severity=High' }}" class="text-decoration-none">
											<div class="alert-summary-card high-card p-3 rounded-4 h-100">
												<div class="d-flex justify-content-between align-items-start mb-2">
													<div>
														<div class="small text-muted fw-medium">High Alerts</div>
														<h2 class="fw-bold text-warning mb-0">{{ $highAlerts ?? 5 }}</h2>
													</div>
													<div class="alert-icon high-icon">
														<i class="bi bi-lightning-charge-fill"></i>
													</div>
												</div>

												<div class="small text-warning fw-semibold mb-1">
													<i class="bi bi-arrow-down-short"></i> -2 this week
												</div>
												<div class="small text-muted mb-1">
													Top Issue: Wrong Office
												</div>
												<div class="small text-muted mb-2">
													Last alert: 12 mins ago
												</div>

												<div class="progress rounded-pill" style="height: 6px;">
													<div class="progress-bar bg-warning" style="width: 60%"></div>
												</div>
											</div>
										</a>
									</div>

									<div class="col-md-6 col-xl-3">
										<a href="{{ url('/admin/alerts') . '?severity=Medium' }}" class="text-decoration-none">
											<div class="alert-summary-card medium-card p-3 rounded-4 h-100">
												<div class="d-flex justify-content-between align-items-start mb-2">
													<div>
														<div class="small text-muted fw-medium">Medium Alerts</div>
														<h2 class="fw-bold text-info mb-0">{{ $mediumAlerts ?? 3 }}</h2>
													</div>
													<div class="alert-icon medium-icon">
														<i class="bi bi-info-circle-fill"></i>
													</div>
												</div>

												<div class="small text-info fw-semibold mb-1">
													<i class="bi bi-dash"></i> No change today
												</div>
												<div class="small text-muted mb-1">
													Top Issue: Overstay
												</div>
												<div class="small text-muted mb-2">
													Last alert: 20 mins ago
												</div>

												<div class="progress rounded-pill" style="height: 6px;">
													<div class="progress-bar bg-info" style="width: 40%"></div>
												</div>
											</div>
										</a>
									</div>

									<div class="col-md-6 col-xl-3">
										<a href="{{ url('/admin/alerts') . '?severity=Low' }}" class="text-decoration-none">
											<div class="alert-summary-card low-card p-3 rounded-4 h-100">
												<div class="d-flex justify-content-between align-items-start mb-2">
													<div>
														<div class="small text-muted fw-medium">Low Alerts</div>
														<h2 class="fw-bold text-secondary mb-0">{{ $lowAlerts ?? 1 }}</h2>
													</div>
													<div class="alert-icon low-icon">
														<i class="bi bi-bell-fill"></i>
													</div>
												</div>

												<div class="small text-secondary fw-semibold mb-1">
													<i class="bi bi-arrow-down-short"></i> -1 from yesterday
												</div>
												<div class="small text-muted mb-1">
													Top Issue: Minor Delay
												</div>
												<div class="small text-muted mb-2">
													Last alert: 35 mins ago
												</div>

												<div class="progress rounded-pill" style="height: 6px;">
													<div class="progress-bar bg-secondary" style="width: 20%"></div>
												</div>
											</div>
										</a>
									</div>
								</div>

								<div class="row mt-4 g-3">
									<div class="col-md-4">
										<div class="mini-summary-box p-3 rounded-4">
											<div class="small text-muted">Total Alerts Today</div>
											<div class="fw-bold fs-4">{{ ($criticalAlerts ?? 2) + ($highAlerts ?? 5) + ($mediumAlerts ?? 3) + ($lowAlerts ?? 1) }}</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="mini-summary-box p-3 rounded-4">
											<div class="small text-muted">Unresolved Alerts</div>
											<div class="fw-bold fs-4 text-danger">{{ $unresolvedAlerts ?? 6 }}</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="mini-summary-box p-3 rounded-4">
											<div class="small text-muted">Most Common Alert</div>
											<div class="fw-bold fs-6">Wrong Office</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-lg-4">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<h5 class="fw-semibold mb-3">Filters</h5>
								<form method="GET" action="/admin/dashboard">
									<div class="mb-3">
										<label class="form-label">Date Range</label>
										<select name="date_filter" class="form-select">
											<option value="">All</option>
											<option value="today">Today</option>
											<option value="week">This Week</option>
											<option value="month">This Month</option>
										</select>
									</div>

									<div class="mb-3">
										<label class="form-label">Office</label>
										<select name="office" class="form-select">
											<option value="">All Offices</option>
											<option value="Registrar">Registrar</option>
											<option value="Cashier">Cashier</option>
											<option value="HR">HR</option>
											<option value="IT Department">IT Department</option>
										</select>
									</div>

									<div class="mb-3">
										<label class="form-label">Visitor Type</label>
										<select name="visitor_type" class="form-select">
											<option value="">All Types</option>
											<option value="Student">Student</option>
											<option value="Parent">Parent</option>
											<option value="Guest">Guest</option>
											<option value="Supplier">Supplier</option>
										</select>
									</div>

									<div class="mb-3">
										<label class="form-label">Status</label>
										<select name="status" class="form-select">
											<option value="">All Status</option>
											<option value="Inside">Inside</option>
											<option value="In Transit">In Transit</option>
											<option value="Completed">Completed</option>
											<option value="Exited">Exited</option>
										</select>
									</div>

									<div class="d-flex gap-2">
										<a href="/admin/dashboard" class="btn btn-outline-secondary w-50">Reset</a>
										<button type="submit" class="btn btn-primary w-50">Apply</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

				<div class="row g-4 mb-4">
					<div class="col-lg-7">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<h4 class="fw-semibold text-center mb-3">7-Day Visitor Trend</h4>
								<div class="bg-light rounded-4 border d-flex align-items-center justify-content-center" style="height: 300px;">
									<span class="text-muted">Line Chart Here</span>
								</div>
							</div>
						</div>
					</div>

					<div class="col-lg-5">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<h4 class="fw-semibold text-center mb-3">Visitors by Status</h4>
								<div class="bg-light rounded-4 border d-flex align-items-center justify-content-center" style="height: 300px;">
									<span class="text-muted">Pie / Donut Chart Here</span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row g-4 mb-4">
					<div class="col-lg-5">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center mb-3">
									<h5 class="fw-semibold mb-0">Real-Time Visitor List</h5>
									<span class="badge bg-success">Live</span>
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
											@php
												$liveVisitors = $liveVisitors ?? [
													['name' => 'Juan Dela Cruz', 'status' => 'Inside', 'location' => 'Registrar', 'time_in' => '8:10 AM'],
													['name' => 'Maria Santos', 'status' => 'In Transit', 'location' => 'Cashier', 'time_in' => '8:25 AM'],
													['name' => 'Pedro Reyes', 'status' => 'Inside', 'location' => 'HR', 'time_in' => '9:00 AM'],
												];
											@endphp

											@forelse($liveVisitors as $visitor)
												<tr>
													<td class="fw-medium">{{ $visitor['name'] }}</td>
													<td>
														@if($visitor['status'] === 'Inside')
															<span class="badge bg-success">Inside</span>
														@elseif($visitor['status'] === 'In Transit')
															<span class="badge bg-primary">In Transit</span>
														@elseif($visitor['status'] === 'Completed')
															<span class="badge bg-dark">Completed</span>
														@else
															<span class="badge bg-secondary">{{ $visitor['status'] }}</span>
														@endif
													</td>
													<td>{{ $visitor['location'] }}</td>
													<td>{{ $visitor['time_in'] }}</td>
												</tr>
											@empty
												<tr>
													<td colspan="4" class="text-center text-muted">No active visitors right now.</td>
												</tr>
											@endforelse
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>

					<div class="col-lg-7">
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
											@php
												$recentAlerts = $recentAlerts ?? [
													['time' => '8:15 AM', 'visitor' => 'Juan Dela Cruz', 'type' => 'Wrong Office', 'severity' => 'High', 'status' => 'Unresolved'],
													['time' => '8:40 AM', 'visitor' => 'Maria Santos', 'type' => 'Unauthorized', 'severity' => 'Critical', 'status' => 'Unresolved'],
													['time' => '9:05 AM', 'visitor' => 'Carlo Reyes', 'type' => 'Overstay', 'severity' => 'Medium', 'status' => 'Resolved'],
												];
											@endphp

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
														<a href="#" class="btn btn-sm btn-outline-dark">View</a>
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
					<div class="col-lg-7">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<h4 class="fw-semibold text-center mb-3">Visitors by Hour</h4>
								<div class="bg-light rounded-4 border d-flex align-items-center justify-content-center" style="height: 280px;">
									<span class="text-muted">Bar Chart Here</span>
								</div>
							</div>
						</div>
					</div>

					<div class="col-lg-5">
						<div class="card shadow-sm border-0 rounded-4 h-100">
							<div class="card-body">
								<h4 class="fw-semibold text-center mb-3">Visitors by Office</h4>
								<div class="bg-light rounded-4 border d-flex align-items-center justify-content-center" style="height: 280px;">
									<span class="text-muted">Horizontal Bar Chart Here</span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12">
						<div class="card shadow-sm border-0 rounded-4">
							<div class="card-body">
								<h4 class="fw-bold mb-3">Key Insights</h4>
								<ul class="mb-0 fs-5">
									<li>Peak visitor hours are between 10:00 AM and 2:00 PM</li>
									<li>Registrar receives the most visitors today</li>
									<li>2 unresolved alerts need immediate attention</li>
									<li>Cashier has the longest average visit duration</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

			</div>
		</main>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script>
		const userMenuGroup = document.getElementById('userMenuGroup');
		const userMenuToggle = document.getElementById('userMenuToggle');

		if (userMenuGroup && userMenuToggle) {
			userMenuToggle.addEventListener('click', () => {
				const isOpen = userMenuGroup.classList.toggle('open');
				userMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		}

		const trendCtx = document.getElementById('visitorTrendChart')?.getContext('2d');
		if (trendCtx) {
			new Chart(trendCtx, {
				type: 'line',
				data: {
					labels: ['Jan 19', 'Jan 20', 'Jan 25', 'Jan 26', 'Jan 27', 'Jan 28'],
					datasets: [{
						label: 'Visitors',
						data: [45, 30, 25, 50, 22, 36],
						borderColor: '#f4c400',
						backgroundColor: '#f4c400',
						tension: 0.4,
						fill: false,
						pointRadius: 5,
						pointHoverRadius: 6
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: false }
					},
					scales: {
						y: {
							beginAtZero: true
						}
					}
				}
			});
		}

		const statusCtx = document.getElementById('visitorStatusChart')?.getContext('2d');
		if (statusCtx) {
			new Chart(statusCtx, {
				type: 'pie',
				data: {
					labels: ['In Transit', 'Arrived'],
					datasets: [{
						data: [33, 67],
						backgroundColor: ['#3f4aa0', '#9aa3e5'],
						borderWidth: 0
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'right'
						}
					}
				}
			});
		}

		const hourCtx = document.getElementById('visitorHourChart')?.getContext('2d');
		if (hourCtx) {
			new Chart(hourCtx, {
				type: 'bar',
				data: {
					labels: ['8:00', '9:00', '10:00', '11:00', '12:00', '1:00', '2:00', '3:00', '4:00', '5:00', '6:00', '7:00'],
					datasets: [{
						label: 'Visitors',
						data: [13, 12, 17, 13, 18, 5, 10, 5, 8, 18, 13, 6],
						backgroundColor: '#3f4aa0',
						borderRadius: 6
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: false }
					},
					scales: {
						y: {
							beginAtZero: true
						}
					}
				}
			});
		}

		const officeCtx = document.getElementById('visitorOfficeChart')?.getContext('2d');
		if (officeCtx) {
			new Chart(officeCtx, {
				type: 'bar',
				data: {
					labels: ['HR', 'Finance Department', 'IT Department'],
					datasets: [{
						label: 'Visitors',
						data: [1, 1, 1],
						backgroundColor: '#f4c400',
						borderRadius: 6
					}]
				},
				options: {
					indexAxis: 'y',
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: false }
					},
					scales: {
						x: {
							beginAtZero: true,
							ticks: {
								stepSize: 1
							}
						}
					}
				}
			});
		}
	</script>
</body>
</html>
