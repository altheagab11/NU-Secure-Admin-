<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Alerts</title>
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

		.page-title {
			margin: 0;
			font-size: 28px;
			font-weight: 700;
			color: #0f172a;
		}

 		.alert-stats {
 			display: grid;
 			grid-template-columns: repeat(4, minmax(0, 1fr));
 			gap: 16px;
 			margin-top: 18px;
 		}

		.stat-card,
		.alerts-panel,
		.legend-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		}

		.stat-card {
			padding: 16px;
		}

		.stat-icon {
			width: 28px;
			height: 28px;
			border-radius: 6px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 10px;
		}

		.stat-icon svg {
			width: 18px;
			height: 18px;
		}

		.stat-number {
			margin: 0;
			font-size: 32px;
			line-height: 1;
			font-weight: 500;
			color: #111827;
		}

		.stat-label {
			margin: 6px 0 0;
			font-size: 14px;
			color: #1f2937;
		}

		.stat-card.unresolved {
			background: #fff8ef;
			border-color: #ffd9b0;
		}

		.stat-card.unresolved .stat-icon {
			background: #ffe9d4;
			color: #f97316;
		}

		.stat-card.resolved {
			background: #eefdf4;
			border-color: #bcefd2;
		}

		.stat-card.resolved .stat-icon {
			background: #d4f8e3;
			color: #22c55e;
		}

		.stat-card.total {
			background: #f8f9fc;
			border-color: #e4e7ef;
		}

		.stat-card.total .stat-icon {
			background: #eef1f7;
			color: #64748b;
		}

		/* Critical alerts */
		.stat-card.critical {
			background: #fff1f2;
			border-color: #ffccd5;
		}

		.stat-card.critical .stat-icon {
			background: #ffe1e6;
			color: #dc2626;
		}

		.alerts-panel {
			margin-top: 18px;
			overflow: hidden;
		}

		.panel-tabs {
			display: flex;
			gap: 30px;
			padding: 10px 16px;
			border-bottom: 1px solid #e5e7eb;
		}

		.tab-link {
			font-size: 14px;
			color: #1f2937;
			text-decoration: none;
		}

		.tab-link.active {
			color: #4b5cd1;
			text-decoration: underline;
			text-underline-offset: 2px;
		}

		.empty-state {
			height: 210px;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			gap: 10px;
			color: #c2c6ce;
		}

		.empty-state svg {
			width: 52px;
			height: 52px;
		}

		.empty-title {
			margin: 0;
			font-size: 32px;
			line-height: 1;
			font-weight: 500;
			color: #c8ccd3;
		}

		.empty-subtitle {
			margin: 0;
			font-size: 27px;
			color: #d2d6dd;
		}

		.legend-card {
			margin-top: 24px;
			padding: 14px 16px;
		}

		.legend-title {
			margin: 0 0 10px;
			font-size: 14px;
			font-weight: 500;
			color: #111827;
		}

		.legend-grid {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 10px;
		}

		.legend-item {
			display: flex;
			align-items: flex-start;
			gap: 10px;
		}

		.legend-icon {
			width: 28px;
			height: 28px;
			border-radius: 6px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}

		.legend-icon svg {
			width: 17px;
			height: 17px;
		}

		.legend-text {
			font-size: 13px;
			line-height: 1.2;
			color: #111827;
		}

		.legend-subtext {
			display: block;
			margin-top: 4px;
			font-size: 12px;
			color: #4b5563;
		}

		.legend-wrong {
			background: #ffe9d4;
			color: #f97316;
		}

		.legend-suspicious {
			background: #ffe1e1;
			color: #ef4444;
		}

		.legend-overstay {
			background: #feefc7;
			color: #f59e0b;
		}

		.legend-unauthorized {
			background: #f0ddff;
			color: #9333ea;
		}

		@media (max-width: 1180px) {
			.alert-stats {
				grid-template-columns: 1fr;
			}

			.legend-grid {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
		}
		@media (max-width: 1024px) {
			.sidebar {
				width: 100%;
				min-height: 100vh;
			}

			.main {
				display: none;
			}
		}

		@media (max-width: 480px) {
			.legend-grid {
				grid-template-columns: 1fr;
			}

			.panel-tabs {
				gap: 14px;
				padding: 10px 12px;
			}

			.tab-link {
				font-size: 13px;
			}

			.empty-title {
				font-size: 24px;
			}

			.empty-subtitle {
				font-size: 16px;
			}

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
		}

				/* compact table sizing for alerts */
				.alerts-table {
					font-size: 15px;
				}

				.alerts-table thead th {
					padding: 6px 6px !important;
					font-weight: 500 !important;
				}

				.alerts-table tbody td {
					padding: 8px 6px !important;
				}

				.table-wrap { overflow-x: auto; }
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
					<p class="brand-title"><span>SVMS</span><span>Admin</span></p>
					<p class="brand-subtitle">Smart Visitor Monitoring System</p>
				</div>
			</div>

			<nav class="menu" aria-label="Sidebar Navigation">
				<a href="/admin/dashboard" class="menu-item">
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

				<a href="/admin/visitor" class="menu-item">
					<span class="inner">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
						</svg>
						Visitor Monitoring
					</span>
				</a>

				<a href="/admin/alerts" class="menu-item active">
					<span class="inner">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="m12 3 10 18H2L12 3Zm0 6v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<circle cx="12" cy="17" r="1.2" fill="currentColor"/>
						</svg>
						Alerts
					</span>
				</a>

				<div class="menu-group" id="userMenuGroup">
					<button type="button" class="menu-item menu-toggle" id="userMenuToggle" aria-expanded="false" aria-controls="userSubmenu">
						<span class="inner">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M4 21h16M7 21V6h10v15M10 9h1M13 9h1M10 12h1M13 12h1M10 15h1M13 15h1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							User Management
						</span>
						<svg class="caret" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
					<div class="submenu" id="userSubmenu">
						<a href="/admin/user/guards" class="submenu-item">Guards</a>
						<a href="/admin/user/offices" class="submenu-item">Offices</a>
					</div>
				</div>
			</nav>

			<div class="spacer" aria-hidden="true"></div>

			<div class="bottom">
				<div class="admin-row">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
					</svg>
					<span>Admin Reyes</span>
				</div>

				<div class="logout-wrap">
					<button type="button" class="logout-btn">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 7 20 12 15 17M20 12H9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M11 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
						</svg>
						Logout
					</button>
				</div>
			</div>
		</aside>

		<main class="main">
			<h1 class="page-title">Security Alerts</h1>

			<div class="alert-stats">
				<div class="stat-card unresolved">
					<span class="stat-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="m12 3 10 18H2L12 3Zm0 6v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<circle cx="12" cy="17" r="1.2" fill="currentColor"/>
						</svg>
					</span>
					<p class="stat-number">{{ $unresolvedCount ?? 0 }}</p>
					<p class="stat-label">Unresolved Alerts</p>
				</div>

				<div class="stat-card resolved">
					<span class="stat-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
							<path d="m8.5 12.5 2.5 2.5 4.5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</span>
					<p class="stat-number">{{ $resolvedCount ?? 0 }}</p>
					<p class="stat-label">Resolved Alerts</p>
				</div>

				<div class="stat-card total">
					<span class="stat-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="m12 3 10 18H2L12 3Zm0 6v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<circle cx="12" cy="17" r="1.2" fill="currentColor"/>
						</svg>
					</span>
					<p class="stat-number">{{ $total ?? 0 }}</p>
					<p class="stat-label">Total Alerts</p>
				</div>

				<div class="stat-card critical">
					<span class="stat-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 2 2 22h20L12 2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M12 8v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
							<circle cx="12" cy="17" r="0.8" fill="currentColor"/>
						</svg>
					</span>
					<p class="stat-number">{{ isset($criticalCount) ? $criticalCount : 0 }}</p>
					<p class="stat-label">Critical Alerts</p>
				</div>
			</div>

			<section class="alerts-panel">
				<div class="panel-tabs">
					<a href="#" class="tab-link active" data-filter="unresolved" data-empty-subtitle="All alerts have been resolved">Unresolved Alerts ({{ $unresolvedCount ?? 0 }})</a>
					<a href="#" class="tab-link" data-filter="all" data-empty-subtitle="No security alerts to display">All Alerts ({{ $total ?? 0 }})</a>
					<a href="#" class="tab-link" data-filter="resolved" data-empty-subtitle="All alerts have been resolved">Resolved ({{ $resolvedCount ?? 0 }})</a>
				</div>
				<div class="table-wrap">
					<table class="alerts-table" style="width:100%; border-collapse:collapse;">
						<thead>
							<tr style="border-bottom:1px solid #e6edf6;">
								<th style="text-align:left; padding:10px 8px; font-weight:400;">Alert ID</th>
								<th style="text-align:left; padding:10px 8px; font-weight:400;">Date &amp; Time</th>
								<th style="text-align:left; padding:10px 8px; font-weight:400;">Visitor Name</th>
								<th style="text-align:left; padding:10px 8px; font-weight:400;">Pass No.</th>
								<th style="text-align:left; padding:10px 8px; font-weight:400;">Control No.</th>
								<th style="text-align:left; padding:10px 8px; font-weight:400;">Expected Office</th>
								<th style="text-align:left; padding:10px 8px; font-weight:400;">Scanned Office</th>
								<th style="text-align:left; padding:10px 8px; font-weight:400;">Alert Type</th>
								<th style="text-align:left; padding:10px 8px; font-weight:400;">Severity</th>
								<th style="text-align:left; padding:10px 8px; font-weight:400;">Status</th>
								<th style="text-align:left; padding:10px 8px; font-weight:400;">Actions</th>
							</tr>
						</thead>
						<tbody>
							@php $hasAlerts = count($alerts ?? []) > 0; @endphp
							@foreach(($alerts ?? []) as $alert)
								<tr style="border-bottom:1px solid #f1f5f9;" data-status="{{ strtolower($alert['status'] ?? 'unknown') }}">
									<td style="padding:10px 8px;">{{ $alert['alert_id'] ?? '' }}</td>
									<td style="padding:10px 8px;">
										@php $dt = isset($alert['created_at']) ? \Carbon\Carbon::parse($alert['created_at']) : null; @endphp
										@if($dt)
											<div style="line-height:1;">
												<div style="font-weight:normal;">{{ $dt->format('M d, Y') }}</div>
												<div style="color:#6b7280; font-size:13px;">{{ $dt->format('h:i A') }}</div>
											</div>
										@else
											-
										@endif
									</td>
									<td style="padding:10px 8px;">{{ ($alert['visitor']['first_name'] ?? '') . ' ' . ($alert['visitor']['last_name'] ?? '') }}</td>
									<td style="padding:10px 8px;">{{ $alert['visitor']['pass_number'] ?? '' }}</td>
									<td style="padding:10px 8px;">{{ $alert['visitor']['control_number'] ?? '' }}</td>
									<td style="padding:10px 8px;">{{ $alert['visit']['office']['office_name'] ?? ($alert['visit']['primary_office_id'] ?? '') }}</td>
									<td style="padding:10px 8px;">{{ $alert['office_scan']['office']['office_name'] ?? '' }}</td>
									<td style="padding:10px 8px;">{{ $alert['alert_type'] ?? '' }}</td>
									<td style="padding:10px 8px;">{{ $alert['severity'] ?? '' }}</td>
									<td style="padding:10px 8px;">{{ $alert['status'] ?? '' }}</td>
									<td style="padding:10px 8px;">
										<button style="background:#4b5cd1;color:#fff;padding:6px 10px;border-radius:8px;border:0;">View</button>
									</td>
								</tr>
							@endforeach
							<tr id="noResults" style="display: {{ $hasAlerts ? 'none' : 'table-row' }};">
								<td colspan="11" style="padding:16px; color:#7b8794; text-align:center;">No alerts found</td>
							</tr>
						</tbody>
					</table>
				</div>
			</section>

			<section class="legend-card">
				<h2 class="legend-title">Alert Types</h2>
				<div class="legend-grid">
					<div class="legend-item">
						<span class="legend-icon legend-wrong" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="m12 3 10 18H2L12 3Zm0 6v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								<circle cx="12" cy="17" r="1.2" fill="currentColor"/>
							</svg>
						</span>
						<span class="legend-text">Wrong Office<span class="legend-subtext">Incorrect destination</span></span>
					</div>

					<div class="legend-item">
						<span class="legend-icon legend-suspicious" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
								<path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
						</span>
						<span class="legend-text">Suspicious<span class="legend-subtext">Suspicious activity</span></span>
					</div>

					<div class="legend-item">
						<span class="legend-icon legend-overstay" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
								<path d="M12 7v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
						</span>
						<span class="legend-text">Overstay<span class="legend-subtext">Extended visit time</span></span>
					</div>

					<div class="legend-item">
						<span class="legend-icon legend-unauthorized" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="m12 3 10 18H2L12 3Zm0 6v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								<circle cx="12" cy="17" r="1.2" fill="currentColor"/>
							</svg>
						</span>
						<span class="legend-text">Unauthorized<span class="legend-subtext">Unauthorized access</span></span>
					</div>
				</div>
			</section>
		</main>
	</div>

	<script>
		const userMenuGroup = document.getElementById('userMenuGroup');
		const userMenuToggle = document.getElementById('userMenuToggle');
		const alertTabLinks = document.querySelectorAll('.panel-tabs .tab-link');
		const emptySubtitle = document.getElementById('emptySubtitle');

		// Helpers for filtering rows by status
		function applyFilter(filter) {
			const tbody = document.querySelector('.alerts-table tbody');
			if (!tbody) return;

			const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.id !== 'noResults');
			let visibleCount = 0;

			rows.forEach(row => {
				const status = (row.dataset.status || '').toLowerCase();
				if (filter === 'all' || status === filter) {
					row.style.display = 'table-row';
					visibleCount++;
				} else {
					row.style.display = 'none';
				}
			});

			const noResults = document.getElementById('noResults');
			if (noResults) {
				noResults.style.display = visibleCount === 0 ? 'table-row' : 'none';
			}
		}

		if (userMenuGroup && userMenuToggle) {
			userMenuToggle.addEventListener('click', () => {
				const isOpen = userMenuGroup.classList.toggle('open');
				userMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		}

		if (alertTabLinks.length) {
			alertTabLinks.forEach((tabLink) => {
				tabLink.addEventListener('click', (event) => {
					event.preventDefault();
					alertTabLinks.forEach((link) => link.classList.remove('active'));
					tabLink.classList.add('active');

					const filter = tabLink.dataset.filter || 'all';
					applyFilter(filter);

					if (emptySubtitle) {
						emptySubtitle.textContent = tabLink.dataset.emptySubtitle || 'No security alerts to display';
					}
				});
			});

			// Apply initial filter (show unresolved by default)
			const initial = document.querySelector('.panel-tabs .tab-link.active');
			const initFilter = initial ? (initial.dataset.filter || 'unresolved') : 'unresolved';
			applyFilter(initFilter);
		}
	</script>
</body>
</html>
