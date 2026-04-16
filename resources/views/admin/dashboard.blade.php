<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin Dashboard</title>
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
			background: #e5e7eb;
			color: #0f172a;
			overflow-x: auto;
		}

		.layout {
			display: grid;
			grid-template-columns: 280px minmax(0, 1fr);
			min-height: 100vh;
			min-width: 1180px;
		}

		.sidebar {
			width: 280px;
			background: var(--sidebar-bg);
			color: var(--text-white);
			padding: 12px 10px;
			display: flex;
			flex-direction: column;
			border-right: 1px solid rgba(0, 0, 0, 0.05);
			position: sticky;
			top: 0;
			height: 100vh;
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
			background: #ececec;
			padding: 20px 22px 24px;
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
				grid-template-columns: 254px minmax(0, 1fr);
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
			.menu-item,
			.admin-row,
			.logout-btn {
				font-size: 14px;
			}

			.layout {
				grid-template-columns: 220px minmax(0, 1fr);
				min-width: 900px;
			}

			.sidebar {
				padding: 10px 8px;
			}

			.brand-title span:first-child {
				font-size: 20px;
			}

			.brand-title span:last-child {
				font-size: 18px;
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
					<p class="brand-title"><span>SVMS</span><span>Admin</span></p>
					<p class="brand-subtitle">Smart Visitor Monitoring System</p>
				</div>
			</div>

			<nav class="menu" aria-label="Sidebar Navigation">
				<a href="/admin/dashboard" class="menu-item active">
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

				<a href="/admin/alerts" class="menu-item">
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
			<h1 class="dashboard-title">Dashboard Overview</h1>

			<!-- Stat Cards -->
			<div class="stats-grid">
				<div class="stat-card visitors">
					<span class="stat-trend" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 16l5-5 4 4 5-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
					<div class="stat-icon">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/></svg>
					</div>
					<div class="stat-number">3</div>
					<p class="stat-label">Total Visitors Today</p>
				</div>

				<div class="stat-card inside">
					<span class="stat-trend" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 16l5-5 4 4 5-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
					<div class="stat-icon">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="7.8" cy="9" r="3.4" stroke="currentColor" stroke-width="1.8"/><circle cx="16.2" cy="13" r="3.4" stroke="currentColor" stroke-width="1.8"/><path d="M2.8 20c.5-2.2 2.4-3.8 4.9-3.8M12.3 20c.5-2.2 2.4-3.8 4.9-3.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
					</div>
					<div class="stat-number">3</div>
					<p class="stat-label">Currently Inside</p>
				</div>

				<div class="stat-card offices">
					<span class="stat-trend" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 16l5-5 4 4 5-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
					<div class="stat-icon">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 21h16M7 21V7h10v14M10 10h1M13 10h1M10 13h1M13 13h1M10 16h1M13 16h1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
					</div>
					<div class="stat-number">8</div>
					<p class="stat-label">Active Offices</p>
				</div>

				<div class="stat-card duration">
					<span class="stat-trend" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 16l5-5 4 4 5-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
					<div class="stat-icon">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="13" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M12 13V9M12 13h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M9.5 3h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
					</div>
					<div class="stat-number">45m</div>
					<p class="stat-label">Average Duration</p>
				</div>
			</div>

			<!-- Charts Grid -->
			<div class="charts-grid">
				<div class="chart-card">
					<p class="chart-title">7-Day Visitor Trend</p>
					<div class="chart-body" role="img" aria-label="Line chart showing 7-day visitor trend">
						<canvas id="visitorTrendChart" aria-label="7-Day Visitor Trend"></canvas>
					</div>
				</div>

				<div class="chart-card">
					<p class="chart-title">Visitors by Status</p>
					<div class="chart-body" role="img" aria-label="Pie chart showing visitors by status">
						<canvas id="visitorStatusChart" aria-label="Visitors by Status"></canvas>
					</div>
				</div>

				<div class="chart-card">
					<p class="chart-title">Visitors by Hour</p>
					<div class="chart-body" role="img" aria-label="Vertical bar chart showing visitors by hour">
						<canvas id="visitorHourChart" aria-label="Visitors by Hour"></canvas>
					</div>
				</div>

				<div class="chart-card">
					<p class="chart-title">Visitors by Office</p>
					<div class="chart-body" role="img" aria-label="Horizontal bar chart showing visitors by office">
						<canvas id="visitorOfficeChart" aria-label="Visitors by Office"></canvas>
					</div>
				</div>
			</div>

			<!-- Key Insights -->
			<div class="insights-card">
				<h3 class="insights-title">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M4 18L10 12L14 15L20 8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M14 8H20V14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					Key Insights
				</h3>
				<ul class="insights-list">
					<li>Peak visitor hours are between 10:00 AM and 2:00 PM</li>
					<li>Human Resources receives the most visitors with 1 total visits</li>
					<li>Average visit duration is 45 minutes</li>
					<li>100% of today's visitors are currently inside campus</li>
				</ul>
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
