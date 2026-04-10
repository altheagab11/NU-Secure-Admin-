<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Active Alerts</title>
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

		.page-title {
			margin: 0 0 18px;
			font-size: 28px;
			font-weight: 700;
			color: #0f172a;
		}

		.alert-summary {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 16px;
			max-width: 560px;
			margin: 0 auto 20px;
		}

		.summary-card {
			position: relative;
			background: #ffffff;
			border: 1px solid #dfe4eb;
			border-radius: 12px;
			padding: 16px 22px 12px;
			box-shadow: 0 2px 5px rgba(15, 23, 42, 0.14);
		}

		.summary-title {
			margin: 0;
			font-size: 16px;
			font-weight: 500;
			color: #111827;
		}

		.summary-value {
			margin: 10px 0 2px;
			font-size: 28px;
			font-weight: 500;
			color: #111827;
			line-height: 1.15;
		}

		.summary-subtitle {
			margin: 0;
			font-size: 14px;
			color: #4b5563;
		}

		.summary-icon {
			position: absolute;
			top: 14px;
			right: 16px;
			width: 30px;
			height: 30px;
			border-radius: 7px;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.summary-icon svg {
			width: 18px;
			height: 18px;
		}

		.summary-card.wrong .summary-icon {
			background: #ffdfe1;
			color: #ef4444;
		}

		.summary-card.completed .summary-icon {
			background: #cef3df;
			color: #16a34a;
		}

		.completed-card {
			background: #ffffff;
			border: 1px solid #dfe4eb;
			border-radius: 12px;
			box-shadow: 0 2px 5px rgba(15, 23, 42, 0.14);
			padding: 18px 24px 20px;
		}

		.completed-header {
			display: flex;
			align-items: center;
			gap: 8px;
			margin-bottom: 6px;
			color: #0f9f58;
		}

		.completed-header svg {
			width: 18px;
			height: 18px;
		}

		.completed-title {
			margin: 0;
			font-size: 16px;
			font-weight: 500;
		}

		.completed-subtitle {
			margin: 0 0 16px;
			font-size: 14px;
			color: #374151;
		}

		.completed-item {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 16px;
			background: #cef3df;
			border: 1px solid #4ade80;
			border-radius: 10px;
			padding: 12px 14px;
		}

		.item-left {
			display: flex;
			align-items: center;
			gap: 12px;
			min-width: 0;
		}

		.item-avatar {
			width: 38px;
			height: 38px;
			flex-shrink: 0;
			color: #111827;
		}

		.item-avatar svg {
			width: 38px;
			height: 38px;
		}

		.item-copy {
			min-width: 0;
		}

		.item-name {
			margin: 0;
			font-size: 16px;
			font-weight: 500;
			color: #1f2937;
		}

		.item-meta,
		.item-time {
			margin: 2px 0 0;
			font-size: 13px;
			color: #1f2937;
		}

		.item-time {
			color: #374151;
		}

		.item-tag {
			padding: 7px 12px;
			font-size: 12px;
			font-weight: 500;
			border: 1px solid #22c55e;
			color: #0f9f58;
			background: #bbf7d0;
			border-radius: 6px;
			white-space: nowrap;
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

		@media (max-width: 900px) {
			.alert-summary {
				grid-template-columns: 1fr;
				max-width: none;
			}

			.completed-item {
				flex-direction: column;
				align-items: stretch;
			}

			.item-tag {
				align-self: flex-start;
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

			.completed-card {
				padding: 14px;
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
				<a href="/guard/dashboard" class="menu-item">
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

				<a href="/guard/alert" class="menu-item active">
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
			<h1 class="page-title">Active Alerts</h1>

			<section class="alert-summary" aria-label="Alert Summary">
				<article class="summary-card wrong">
					<div class="summary-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
							<path d="m9 9 6 6M15 9l-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
					</div>
					<p class="summary-title">Wrong Office</p>
					<p class="summary-value">0</p>
					<p class="summary-subtitle">Active alerts</p>
				</article>

				<article class="summary-card completed">
					<div class="summary-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
							<path d="m8 12 3 3 5-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<p class="summary-title">Completed</p>
					<p class="summary-value">1</p>
					<p class="summary-subtitle">Ready to exit</p>
				</article>
			</section>

			<section class="completed-card" aria-label="Completed Visitors">
				<div class="completed-header">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
						<path d="m8 12 3 3 5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<p class="completed-title">Completed Visitors</p>
				</div>
				<p class="completed-subtitle">Visitors who have completed their business and are ready to exit</p>

				<article class="completed-item">
					<div class="item-left">
						<div class="item-avatar" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
							</svg>
						</div>
						<div class="item-copy">
							<p class="item-name">Robert Kim</p>
							<p class="item-meta">Finance Department • ID123456</p>
							<p class="item-time">Completed at: 5:00 pm</p>
						</div>
					</div>
					<span class="item-tag">Ready to Exit</span>
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
	</script>
</body>
</html>