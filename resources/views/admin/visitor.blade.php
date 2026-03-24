<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Visitor Monitoring</title>
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

		.filters-card,
		.table-card,
		.panel-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		}

		.filters-card {
			padding: 20px;
			margin-top: 16px;
		}

		.filters-row {
			display: grid;
			grid-template-columns: 1.5fr 0.8fr 0.8fr;
			gap: 12px;
		}

		.filter-input,
		.filter-select {
			height: 42px;
			border: 0;
			outline: none;
			background: #e5e7eb;
			border-radius: 6px;
			padding: 0 14px;
			font-size: 14px;
			color: #334155;
		}

		.filter-input::placeholder {
			color: #94a3b8;
		}

		.search-wrap {
			position: relative;
		}

		.search-wrap svg {
			position: absolute;
			left: 14px;
			top: 50%;
			transform: translateY(-50%);
			width: 16px;
			height: 16px;
			color: #4256b4;
		}

		.search-wrap .filter-input {
			padding-left: 38px;
		}

		.filters-count {
			margin: 12px 0 0;
			font-size: 14px;
			color: #111827;
		}

		.table-card {
			margin-top: 22px;
			overflow: hidden;
		}

		.visitor-table {
			width: 100%;
			border-collapse: collapse;
		}

		.visitor-table th {
			text-align: left;
			font-size: 15px;
			color: #1f2937;
			font-weight: 600;
			padding: 14px 16px;
			background: #ffffff;
		}

		.visitor-table td {
			font-size: 14px;
			color: #2f3640;
			padding: 14px 16px;
			border-top: 1px solid #d5d8de;
			vertical-align: middle;
		}

		.visitor-cell {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.avatar-icon {
			width: 18px;
			height: 18px;
			color: #000;
			flex-shrink: 0;
		}

		.visitor-name {
			font-size: 14px;
			font-weight: 700;
			line-height: 1.1;
		}

		.visitor-id {
			font-size: 12px;
			color: #111827;
		}

		.status-pill {
			display: inline-block;
			padding: 6px 10px;
			border-radius: 6px;
			font-size: 11px;
			line-height: 1;
		}

		.status-arrived {
			background: #c7f0dd;
			color: #15803d;
		}

		.status-transit {
			background: #bfeef7;
			color: #0e7490;
		}

		.action-link {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			text-decoration: none;
			color: #273b9e;
			font-size: 14px;
			font-weight: 600;
		}

		.action-link svg {
			width: 16px;
			height: 16px;
		}

		.bottom-grid {
			display: grid;
			grid-template-columns: 1.8fr 1fr;
			gap: 12px;
			margin-top: 22px;
		}

		.panel-card {
			overflow: hidden;
		}

		.panel-title {
			margin: 0;
			padding: 14px 16px;
			font-size: 20px;
			font-weight: 600;
			color: #1f2937;
			border-bottom: 1px solid #d5d8de;
		}

		.recent-list,
		.office-list {
			list-style: none;
			margin: 0;
			padding: 0;
		}

		.recent-item {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			padding: 14px 16px;
			border-bottom: 1px solid #d5d8de;
		}

		.recent-item:last-child {
			border-bottom: 0;
		}

		.recent-left {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.recent-meta {
			font-size: 12px;
			color: #4b5563;
			margin-top: 2px;
		}

		.office-item {
			padding: 12px 16px;
			border-bottom: 1px solid #d5d8de;
		}

		.office-item:last-child {
			border-bottom: 0;
		}

		.office-row {
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-size: 12px;
			color: #334155;
			margin-bottom: 6px;
		}

		.office-label {
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		.office-label svg {
			width: 12px;
			height: 12px;
			color: #5b66a7;
		}

		.bar-track {
			height: 4px;
			background: #e5e7eb;
			border-radius: 999px;
			overflow: hidden;
		}

		.bar-fill {
			height: 100%;
			background: #111827;
			border-radius: 999px;
		}

		.bar-fill.w-33 {
			width: 33%;
		}

		@media (max-width: 1180px) {
			.filters-row {
				grid-template-columns: 1fr;
			}

			.bottom-grid {
				grid-template-columns: 1fr;
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

				<a href="/admin/visitor" class="menu-item active">
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

				<a href="/admin/user" class="menu-item">
					<span class="inner">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M4 21h16M7 21V6h10v15M10 9h1M13 9h1M10 12h1M13 12h1M10 15h1M13 15h1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
						User Management
					</span>
				</a>
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
			<h1 class="page-title">Visitor Monitoring</h1>

			<div class="filters-card">
				<div class="filters-row">
					<div class="search-wrap">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2.5"/>
							<path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
						</svg>
						<input class="filter-input" type="text" placeholder="" aria-label="Search visitor">
					</div>
					<select class="filter-select" aria-label="Filter by office">
						<option value=""></option>
					</select>
					<select class="filter-select" aria-label="Filter by status">
						<option value=""></option>
					</select>
				</div>
				<p class="filters-count">Showing 3 of 3 visitors</p>
			</div>

			<div class="table-card">
				<table class="visitor-table" aria-label="Visitors table">
					<thead>
						<tr>
							<th>Visitor</th>
							<th>Control #</th>
							<th>Destination</th>
							<th>Time In</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<div class="visitor-cell">
									<svg class="avatar-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z"/>
									</svg>
									<div>
										<div class="visitor-name">John Anderson</div>
										<div class="visitor-id">ID123456</div>
									</div>
								</div>
							</td>
							<td>SVMS-250125-0001</td>
							<td>Human Resources</td>
							<td>Jan 25, 2026<br>10:26 AM</td>
							<td><span class="status-pill status-arrived">Arrived</span></td>
							<td>
								<a href="#" class="action-link">
									<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z" stroke="currentColor" stroke-width="2"/>
										<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
									</svg>
									View
								</a>
							</td>
						</tr>

						<tr>
							<td>
								<div class="visitor-cell">
									<svg class="avatar-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z"/>
									</svg>
									<div>
										<div class="visitor-name">Maria Garcia</div>
										<div class="visitor-id">ID123467</div>
									</div>
								</div>
							</td>
							<td>SVMS-250125-0002</td>
							<td>IT Department</td>
							<td>Jan 25, 2026<br>11:56 AM</td>
							<td><span class="status-pill status-transit">In Transit</span></td>
							<td>
								<a href="#" class="action-link">
									<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z" stroke="currentColor" stroke-width="2"/>
										<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
									</svg>
									View
								</a>
							</td>
						</tr>

						<tr>
							<td>
								<div class="visitor-cell">
									<svg class="avatar-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z"/>
									</svg>
									<div>
										<div class="visitor-name">Robert Kim</div>
										<div class="visitor-id">ID123489</div>
									</div>
								</div>
							</td>
							<td>SVMS-250125-0003</td>
							<td>Finance Department</td>
							<td>Jan 25, 2026<br>9:26 AM</td>
							<td><span class="status-pill status-arrived">Arrived</span></td>
							<td>
								<a href="#" class="action-link">
									<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z" stroke="currentColor" stroke-width="2"/>
										<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
									</svg>
									View
								</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="bottom-grid">
				<section class="panel-card">
					<h2 class="panel-title">Recent Visitors</h2>
					<ul class="recent-list">
						<li class="recent-item">
							<div class="recent-left">
								<svg class="avatar-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z"/>
								</svg>
								<div>
									<div>Robert Kim</div>
									<div class="recent-meta">Finance Department • 09:23 AM</div>
								</div>
							</div>
							<span class="status-pill status-arrived">Arrived</span>
						</li>

						<li class="recent-item">
							<div class="recent-left">
								<svg class="avatar-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z"/>
								</svg>
								<div>
									<div>Maria Garcia</div>
									<div class="recent-meta">IT Department • 11:53 AM</div>
								</div>
							</div>
							<span class="status-pill status-transit">In Transit</span>
						</li>

						<li class="recent-item">
							<div class="recent-left">
								<svg class="avatar-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z"/>
								</svg>
								<div>
									<div>John Anderson</div>
									<div class="recent-meta">Human Resources • 10:23 AM</div>
								</div>
							</div>
							<span class="status-pill status-arrived">Arrived</span>
						</li>
					</ul>
				</section>

				<section class="panel-card">
					<h2 class="panel-title">Active by Office</h2>
					<ul class="office-list">
						<li class="office-item">
							<div class="office-row">
								<span class="office-label">
									<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M7 21h10M9 21V8h6v13M8 8h8M8 4h8v4H8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
									</svg>
									Human Resources
								</span>
								<span>1</span>
							</div>
							<div class="bar-track"><div class="bar-fill w-33"></div></div>
						</li>

						<li class="office-item">
							<div class="office-row">
								<span class="office-label">
									<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M7 21h10M9 21V8h6v13M8 8h8M8 4h8v4H8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
									</svg>
									IT Department
								</span>
								<span>1</span>
							</div>
							<div class="bar-track"><div class="bar-fill w-33"></div></div>
						</li>

						<li class="office-item">
							<div class="office-row">
								<span class="office-label">
									<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M7 21h10M9 21V8h6v13M8 8h8M8 4h8v4H8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
									</svg>
									Finance Department
								</span>
								<span>1</span>
							</div>
							<div class="bar-track"><div class="bar-fill w-33"></div></div>
						</li>
					</ul>
				</section>
			</div>
		</main>
	</div>
</body>
</html>
