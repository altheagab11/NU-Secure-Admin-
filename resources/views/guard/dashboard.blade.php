<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Guard Dashboard</title>
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
			background: #f7f8ff;
			padding: 24px 32px;
			overflow-y: auto;
			margin-left: 260px;
			min-height: 100vh;
		}

		.dashboard-title {
			margin: 0 0 18px;
			font-size: 28px;
			font-weight: 700;
			color: #0f172a;
		}

		.summary-grid {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 16px;
			margin-bottom: 18px;
		}

		.summary-card {
			position: relative;
			background: #ffffff;
			border: 1px solid #dfe4eb;
			border-radius: 12px;
			padding: 16px 18px;
			box-shadow: 0 2px 5px rgba(15, 23, 42, 0.12);
			min-height: 106px;
		}

		.summary-card .label {
			margin: 0 0 10px;
			font-size: 16px;
			font-weight: 600;
			color: #4b5563;
		}

		.summary-card .value {
			margin: 0;
			font-size: 28px;
			font-weight: 700;
			color: #111827;
			line-height: 1.2;
		}

		.summary-card .meta {
			margin: 8px 0 0;
			font-size: 14px;
			color: #374151;
		}

		.summary-icon {
			position: absolute;
			top: 14px;
			right: 18px;
			width: 36px;
			height: 36px;
			border-radius: 7px;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.summary-icon svg {
			width: 22px;
			height: 22px;
		}

		.summary-card.clock {
			background: #3b4497;
			border-color: #3b4497;
			color: #f8faff;
		}

		.summary-card.clock .label,
		.summary-card.clock .value,
		.summary-card.clock .meta {
			color: #f8faff;
		}

		.summary-card.visitors .summary-icon {
			background: #d8f6e5;
			color: #22a86f;
		}

		.summary-card.clock .summary-icon {
			background: #d8e5ff;
			color: #3b4497;
		}

		.summary-card.alerts .summary-icon {
			background: #ffe24f;
			color: #24316f;
		}

		.action-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 16px;
			margin-bottom: 14px;
		}

		.action-card {
			position: relative;
			padding: 18px;
			border-radius: 12px;
			border: 1px solid #dce2ea;
			box-shadow: 0 2px 5px rgba(15, 23, 42, 0.12);
			min-height: 132px;
			display: flex;
			flex-direction: column;
			justify-content: flex-end;
		}

		.action-card-link,
		.alert-strip-link {
			display: block;
			text-decoration: none;
			color: inherit;
		}

		.action-card.primary {
			background: #3b4497;
			border-color: #3b4497;
		}

		.action-card.secondary {
			background: #ffffff;
		}

		.action-card-title {
			margin: 0 0 6px;
			font-size: 16px;
			font-weight: 500;
			line-height: 1.2;
			color: #4b5563;
		}

		.action-card.primary .action-card-title {
			color: #f7f8ff;
		}

		.action-card-subtitle {
			margin: 0;
			font-size: 13px;
			line-height: 1.35;
			color: #6b7280;
		}

		.action-card.primary .action-card-subtitle {
			color: #d8defe;
		}

		.action-icon,
		.action-arrow {
			position: absolute;
			top: 12px;
		}

		.action-icon {
			left: 16px;
			width: 36px;
			height: 36px;
			border-radius: 6px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #ffe24f;
			color: #31408c;
		}

		.action-icon svg {
			width: 22px;
			height: 22px;
		}

		.action-card.secondary .action-icon {
			background: #e5e7eb;
			color: #1f2937;
		}

		.action-arrow {
			right: 14px;
			color: inherit;
			opacity: 0.8;
		}

		.action-arrow svg {
			width: 22px;
			height: 22px;
		}

		.action-card.primary .action-arrow {
			color: #f8faff;
		}

		.action-card-inner {
			padding-top: 26px;
		}

		.alert-strip {
			position: relative;
			display: flex;
			align-items: center;
			gap: 16px;
			background: #3b4497;
			color: #f8faff;
			border: 1px solid #3b4497;
			border-radius: 12px;
			padding: 12px 16px;
			box-shadow: 0 2px 5px rgba(15, 23, 42, 0.12);
			margin-bottom: 16px;
		}

		.alert-strip .action-icon {
			position: static;
			flex-shrink: 0;
		}

		.alert-strip .copy {
			line-height: 1.35;
		}

		.alert-strip-title {
			margin: 0;
			font-size: 16px;
			font-weight: 500;
		}

		.alert-strip-subtitle {
			margin: 4px 0 0;
			font-size: 14px;
			color: #d8defe;
		}

		.alert-strip .action-arrow {
			position: absolute;
			top: 50%;
			right: 14px;
			transform: translateY(-50%);
			color: #f8faff;
		}

		.active-visitor-card {
			background: #ffffff;
			border: 1px solid #dfe4eb;
			border-radius: 12px;
			box-shadow: 0 2px 5px rgba(15, 23, 42, 0.12);
			overflow: hidden;
		}

		.active-visitor-header {
			padding: 16px 20px;
			font-size: 16px;
			font-weight: 500;
			color: #4b5563;
			border-bottom: 1px solid #e5e7eb;
		}

		.visitor-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 14px;
			padding: 16px 20px;
			border-bottom: 1px solid #eef2f7;
		}

		.visitor-row:last-child {
			border-bottom: 0;
		}

		.visitor-info {
			display: flex;
			align-items: center;
			gap: 14px;
			min-width: 0;
		}

		.visitor-avatar {
			width: 44px;
			height: 44px;
			flex-shrink: 0;
			border-radius: 999px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #111827;
		}

		.visitor-avatar svg {
			width: 34px;
			height: 34px;
		}

		.visitor-copy {
			min-width: 0;
		}

		.visitor-name {
			margin: 0;
			font-size: 16px;
			font-weight: 500;
			color: #4b5563;
		}

		.visitor-meta {
			margin: 4px 0 0;
			font-size: 14px;
			color: #6b7280;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.visitor-status {
			padding: 7px 14px;
			font-size: 12px;
			font-weight: 600;
			border-radius: 6px;
			text-transform: none;
			flex-shrink: 0;
			border: 1px solid transparent;
		}

		.visitor-status.arrived {
			background: #cdf5e5;
			color: #099169;
		}

		.visitor-status.transit {
			background: #cbeffb;
			color: #0f89a8;
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

		@media (max-width: 980px) {
			.summary-grid {
			.submenu-link,
				grid-template-columns: 1fr;
			}

			.action-grid {
				grid-template-columns: 1fr;
			}

			.visitor-row {
				align-items: flex-start;
			}
		}

		@media (max-width: 480px) {
			.menu-item,
			.admin-row,
			.logout-btn {
				font-size: 16px;
			}

			.brand-title span:first-child {
				font-size: 22px;
			}

			.brand-title span:last-child {
				font-size: 20px;
			}

			.main {
				padding: 16px;
			}

			.dashboard-title {
				font-size: 24px;
			}

			.summary-card,
			.action-card,
			.alert-strip,
			.active-visitor-header,
			.visitor-row {
				padding-left: 14px;
				padding-right: 14px;
			}

			.visitor-row {
				flex-direction: column;
				align-items: stretch;
			}

			.visitor-status {
				align-self: flex-start;
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
			<h1 class="dashboard-title">Guard Dashboard</h1>

			<section class="summary-grid" aria-label="Guard Summary">
				<article class="summary-card visitors">
					<div class="summary-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M16 11a3 3 0 1 0-2.999-3A3 3 0 0 0 16 11Zm-8 0a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm0 2c-2.2 0-4 .9-4 2v2h8v-2c0-1.1-1.8-2-4-2Zm8 0c-.7 0-1.4.1-2 .3 1.2.7 2 1.6 2 2.7V17h4v-1c0-1.6-1.8-3-4-3Z" fill="currentColor"/>
						</svg>
					</div>
					<p class="label">Active Visitor</p>
					<p class="value">3</p>
				</article>

				<article class="summary-card clock">
					<div class="summary-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>
							<path d="M12 8v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<p class="label">Current Time</p>
					<p class="value" id="currentTimeValue">09:18 PM</p>
				</article>

				<article class="summary-card alerts">
					<div class="summary-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 17H5.8a1 1 0 0 1-.8-1.6L7 12.7V10a5 5 0 1 1 10 0v2.7l2 2.7a1 1 0 0 1-.8 1.6H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M10 20a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
					</div>
					<p class="label">Active Alerts</p>
					<p class="meta">1 overstay • 1 ready to exit</p>
				</article>
			</section>

			<section class="action-grid" aria-label="Primary Actions">
				<a href="/guard/register?type=normal" class="action-card-link" aria-label="Open Normal Visitor registration">
				<article class="action-card primary">
					<div class="action-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
							<path d="M18 6h4M20 4v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
					</div>
					<div class="action-arrow" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="action-card-inner">
						<p class="action-card-title">Register New Visitor</p>
						<p class="action-card-subtitle">Scan face and ID, fill form, generate QR ticket</p>
					</div>
				</article>
				</a>

				<a href="/guard/exit" class="action-card-link" aria-label="Open Exit Scan">
				<article class="action-card secondary">
					<div class="action-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M4 7h12M4 17h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<path d="m12 3 4 4-4 4M12 13l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="action-arrow" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="action-card-inner">
						<p class="action-card-title">Exit Scan</p>
						<p class="action-card-subtitle">Scan visitor QR code to process exit</p>
					</div>
				</article>
				</a>
			</section>

			<a href="/guard/alert" class="alert-strip-link" aria-label="Open Active Alerts">
			<section class="alert-strip" aria-label="Active Alert Banner">
				<div class="action-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M15 17H5.8a1 1 0 0 1-.8-1.6L7 12.7V10a5 5 0 1 1 10 0v2.7l2 2.7a1 1 0 0 1-.8 1.6H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M10 20a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</div>
				<div class="copy">
					<p class="alert-strip-title">Active Alerts</p>
					<p class="alert-strip-subtitle">1 ready to exit</p>
				</div>
				<div class="action-arrow" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
			</section>
			</a>

			<section class="active-visitor-card" aria-label="Active Visitors Inside Campus">
				<div class="active-visitor-header">Active Visitors Inside Campus</div>

				<article class="visitor-row">
					<div class="visitor-info">
						<div class="visitor-avatar" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
							</svg>
						</div>
						<div class="visitor-copy">
							<p class="visitor-name">Robert Kim</p>
							<p class="visitor-meta">Finance Department • 09:23 AM</p>
						</div>
					</div>
					<span class="visitor-status arrived">Arrived</span>
				</article>

				<article class="visitor-row">
					<div class="visitor-info">
						<div class="visitor-avatar" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
							</svg>
						</div>
						<div class="visitor-copy">
							<p class="visitor-name">Maria Garcia</p>
							<p class="visitor-meta">IT Department • 11:53 AM</p>
						</div>
					</div>
					<span class="visitor-status transit">In Transit</span>
				</article>

				<article class="visitor-row">
					<div class="visitor-info">
						<div class="visitor-avatar" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
							</svg>
						</div>
						<div class="visitor-copy">
							<p class="visitor-name">John Anderson</p>
							<p class="visitor-meta">Human Resources • 10:23 AM</p>
						</div>
					</div>
					<span class="visitor-status arrived">Arrived</span>
				</article>
			</section>
		</main>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		const timeTarget = document.getElementById('currentTimeValue');
		if (timeTarget) {
			const formatter = new Intl.DateTimeFormat('en-US', {
				hour: '2-digit',
				minute: '2-digit',
				hour12: true
			});

			const updateClock = () => {
				timeTarget.textContent = formatter.format(new Date());
			};

			updateClock();
			setInterval(updateClock, 30000);
		}
	</script>
</body>
</html>