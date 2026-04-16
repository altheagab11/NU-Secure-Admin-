<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
php	<title>Admin Dashboard</title>
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
			font-size: 48px;
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
				font-size: 40px;
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
				font-size: 32px;
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
						<svg class="chart-svg" viewBox="0 0 420 230" xmlns="http://www.w3.org/2000/svg">
							<line class="axis" x1="40" y1="15" x2="40" y2="185" />
							<line class="axis" x1="40" y1="185" x2="385" y2="185" />
							<line class="grid-line" x1="40" y1="45" x2="385" y2="45" />
							<line class="grid-line" x1="40" y1="80" x2="385" y2="80" />
							<line class="grid-line" x1="40" y1="115" x2="385" y2="115" />
							<line class="grid-line" x1="40" y1="150" x2="385" y2="150" />
							<text class="y-label" x="20" y="188">0</text>
							<text class="y-label" x="15" y="152">20</text>
							<text class="y-label" x="15" y="117">30</text>
							<text class="y-label" x="15" y="82">40</text>
							<text class="y-label" x="15" y="48">50</text>
							<path class="plot-line" d="M48 58 C70 76 88 95 105 108 C124 120 146 131 165 121 C185 112 185 58 205 45 C220 35 236 45 245 60 C253 76 258 113 246 130 C232 149 219 152 200 150 C172 146 144 120 121 120 C98 120 73 130 48 140" opacity="0"/>
							<polyline class="plot-line" points="48,62 108,105 168,120 228,45 288,135 385,88" />
							<circle class="plot-dot" cx="48" cy="62" r="5" />
							<circle class="plot-dot" cx="108" cy="105" r="5" />
							<circle class="plot-dot" cx="168" cy="120" r="5" />
							<circle class="plot-dot" cx="228" cy="45" r="5" />
							<circle class="plot-dot" cx="288" cy="135" r="5" />
							<circle class="plot-dot" cx="385" cy="88" r="5" />
							<text class="x-label" x="29" y="212">Jan 19</text>
							<text class="x-label" x="91" y="212">Jan 20</text>
							<text class="x-label" x="152" y="212">Jan 25</text>
							<text class="x-label" x="212" y="212">Jan 26</text>
							<text class="x-label" x="272" y="212">Jan 27</text>
							<text class="x-label" x="370" y="212">Jan 28</text>
						</svg>
					</div>
				</div>

				<div class="chart-card">
					<p class="chart-title">Visitors by Status</p>
					<div class="chart-body" role="img" aria-label="Pie chart showing visitors by status">
						<svg class="chart-svg" viewBox="0 0 420 230" xmlns="http://www.w3.org/2000/svg">
							<path d="M210 115 L210 45 A70 70 0 0 1 270.62 150 Z" fill="#343b8f" />
							<path d="M210 115 L270.62 150 A70 70 0 1 1 210 45 Z" fill="#8d97d8" />
							<circle cx="210" cy="115" r="70" fill="none" stroke="#f4f5f6" stroke-width="2" />
							<text class="chart-note" x="45" y="118" fill="#6a79d8">Arrived: 33%</text>
							<text class="chart-note" x="268" y="74" fill="#343b8f">In Transit: 33%</text>
						</svg>
					</div>
				</div>

				<div class="chart-card">
					<p class="chart-title">Visitors by Hour</p>
					<div class="chart-body" role="img" aria-label="Vertical bar chart showing visitors by hour">
						<svg class="chart-svg" viewBox="0 0 420 230" xmlns="http://www.w3.org/2000/svg">
							<line class="axis" x1="34" y1="14" x2="34" y2="186" />
							<line class="axis" x1="34" y1="186" x2="392" y2="186" />
							<line class="grid-line" x1="34" y1="40" x2="392" y2="40" />
							<line class="grid-line" x1="34" y1="76" x2="392" y2="76" />
							<line class="grid-line" x1="34" y1="112" x2="392" y2="112" />
							<line class="grid-line" x1="34" y1="148" x2="392" y2="148" />
							<text class="y-label" x="12" y="189">0</text>
							<text class="y-label" x="8" y="151">5</text>
							<text class="y-label" x="4" y="115">10</text>
							<text class="y-label" x="4" y="79">15</text>
							<text class="y-label" x="4" y="43">20</text>
							<rect class="bar-vertical" x="50" y="74" width="20" height="112" />
							<rect class="bar-vertical" x="80" y="86" width="20" height="100" />
							<rect class="bar-vertical" x="110" y="50" width="20" height="136" />
							<rect class="bar-vertical" x="140" y="74" width="20" height="112" />
							<rect class="bar-vertical" x="170" y="130" width="20" height="56" />
							<rect class="bar-vertical" x="200" y="92" width="20" height="94" />
							<rect class="bar-vertical" x="230" y="130" width="20" height="56" />
							<rect class="bar-vertical" x="260" y="106" width="20" height="80" />
							<rect class="bar-vertical" x="290" y="50" width="20" height="136" />
							<rect class="bar-vertical" x="320" y="74" width="20" height="112" />
							<rect class="bar-vertical" x="350" y="122" width="20" height="64" />
							<text class="x-label" x="48" y="208">8:00</text>
							<text class="x-label" x="78" y="208">9:00</text>
							<text class="x-label" x="108" y="208">10:00</text>
							<text class="x-label" x="138" y="208">11:00</text>
							<text class="x-label" x="168" y="208">12:00</text>
							<text class="x-label" x="198" y="208">1:00</text>
							<text class="x-label" x="228" y="208">2:00</text>
							<text class="x-label" x="258" y="208">3:00</text>
							<text class="x-label" x="288" y="208">4:00</text>
							<text class="x-label" x="318" y="208">5:00</text>
							<text class="x-label" x="348" y="208">6:00</text>
							<text class="x-label" x="378" y="208">7:00</text>
						</svg>
					</div>
				</div>

				<div class="chart-card">
					<p class="chart-title">Visitors by Office</p>
					<div class="chart-body" role="img" aria-label="Horizontal bar chart showing visitors by office">
						<svg class="chart-svg" viewBox="0 0 420 230" xmlns="http://www.w3.org/2000/svg">
							<line class="axis" x1="78" y1="22" x2="78" y2="190" />
							<line class="axis" x1="78" y1="190" x2="390" y2="190" />
							<line class="grid-line" x1="156" y1="22" x2="156" y2="190" />
							<line class="grid-line" x1="234" y1="22" x2="234" y2="190" />
							<line class="grid-line" x1="312" y1="22" x2="312" y2="190" />
							<text class="y-label" x="42" y="56">HR</text>
							<text class="y-label" x="10" y="107">Finance</text>
							<text class="y-label" x="18" y="121">Department</text>
							<text class="y-label" x="18" y="162">IT Department</text>
							<rect class="bar-horizontal" x="78" y="31" width="312" height="34" rx="8" />
							<rect class="bar-horizontal" x="78" y="82" width="312" height="34" rx="8" />
							<rect class="bar-horizontal" x="78" y="133" width="312" height="34" rx="8" />
							<text class="x-label" x="74" y="209">0</text>
							<text class="x-label" x="150" y="209">0.25</text>
							<text class="x-label" x="228" y="209">0.5</text>
							<text class="x-label" x="304" y="209">0.75</text>
							<text class="x-label" x="387" y="209">1</text>
						</svg>
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

	<script>
		const userMenuGroup = document.getElementById('userMenuGroup');
		const userMenuToggle = document.getElementById('userMenuToggle');

		if (userMenuGroup && userMenuToggle) {
			userMenuToggle.addEventListener('click', () => {
				const isOpen = userMenuGroup.classList.toggle('open');
				userMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		}
	</script>
</body>
</html>
