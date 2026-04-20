<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Guard Dashboard</title>
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

		.main {
			flex: 1;
			background: #f7f8ff;
			padding: 24px 32px;
			overflow-y: auto;
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
		<aside class="sidebar">
			<div class="brand-row">
				<div class="brand-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
					</svg>
				</div>
				<div>
					<p class="brand-title"><span>SVMS</span><span>Guard</span></p>
					<p class="brand-subtitle">Smart Visitor Monitoring System</p>
				</div>
			</div>

			<nav class="menu" aria-label="Sidebar Navigation">
				<a href="/guard/dashboard" class="menu-item active">
					<span class="inner">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
							<rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
							<rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
							<rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
						</svg>
						Dashboard
					</span>
				</a>

				<div class="menu-group" id="registerMenuGroup">
					<button type="button" class="menu-item menu-toggle" id="registerMenuToggle" aria-expanded="false" aria-controls="registerSubmenu">
						<span class="inner">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
								<path d="M5 5h5M7.5 2.5v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							Register
						</span>
						<svg class="caret" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
					<div class="submenu" id="registerSubmenu">
						<a href="/guard/register?type=normal" class="submenu-item">Normal Visitor</a>
						<a href="/guard/register?type=enrollee" class="submenu-item">Enrollee</a>
						<a href="/guard/register?type=contractor" class="submenu-item">Contractor</a>
					</div>
				</div>

				<a href="/guard/exit" class="menu-item">
					<span class="inner">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M4 4h3v3M17 4h3v3M4 17h3v3M17 17h3v3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M9 8h2v2H9zM13 8h2v2h-2zM9 12h2v2H9zM13 12h2v2h-2z" fill="currentColor"/>
						</svg>
						Exit Scan
					</span>
				</a>

				<a href="/guard/alert" class="menu-item">
					<span class="inner">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 17H5.8a1 1 0 0 1-.8-1.6L7 12.7V10a5 5 0 1 1 10 0v2.7l2 2.7a1 1 0 0 1-.8 1.6H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M10 20a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
						Active Alerts
					</span>
				</a>
			</nav>

			<div class="spacer" aria-hidden="true"></div>

			<div class="bottom">
				<div class="admin-row">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
					</svg>
					<span>Officer Martinez</span>
				</div>

				<div class="logout-wrap">
					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<button type="submit" class="logout-btn">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 7 20 12 15 17M20 12H9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M11 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
						</svg>
						Logout
						</button>
					</form>
				</div>
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

	<script>
		const registerMenuGroup = document.getElementById('registerMenuGroup');
		const registerMenuToggle = document.getElementById('registerMenuToggle');

		if (registerMenuGroup && registerMenuToggle) {
			registerMenuToggle.addEventListener('click', () => {
				const isOpen = registerMenuGroup.classList.toggle('open');
				registerMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		}

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