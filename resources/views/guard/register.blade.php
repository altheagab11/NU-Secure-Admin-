<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Register Visitor</title>
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
			margin: 0;
			font-size: 28px;
			font-weight: 700;
			color: #0f172a;
		}

		.register-flow {
			max-width: 720px;
			margin: 0 auto;
		}

		.flow-head {
			display: flex;
			justify-content: center;
			align-items: flex-start;
			margin: 6px 0 14px;
		}

		.flow-step-meta {
			margin-top: 6px;
			text-align: left;
		}

		.flow-step-meta p {
			margin: 0;
		}

		.flow-step-name {
			font-size: 28px;
			font-weight: 700;
			color: #1f2937;
			line-height: 1;
		}

		.flow-step-count {
			font-size: 14px;
			color: #475569;
			margin-top: 6px;
		}

		.scanner-card {
			width: 100%;
			background: #ffffff;
			border: 1px solid #d9dde4;
			border-radius: 14px;
			padding: 16px;
			box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
		}

		.scanner-zone {
			background: #d9dee6;
			border-radius: 10px;
			padding: 44px 24px;
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 300px;
		}

		.id-frame {
			width: 250px;
			height: 190px;
			border: 5px solid #3a4aa0;
			border-radius: 22px;
			background: #f7f9ff;
			display: flex;
			align-items: center;
			justify-content: center;
			position: relative;
		}

		.id-frame::before,
		.id-frame::after {
			content: '';
			position: absolute;
			left: 24px;
			right: 24px;
			height: 6px;
			background: #f7f9ff;
		}

		.id-frame::before {
			top: -5px;
		}

		.id-frame::after {
			bottom: -5px;
		}

		.id-hint {
			text-align: center;
		}

		.id-icon {
			width: 64px;
			height: 64px;
			border-radius: 20px;
			background: #c8d9f0;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 10px;
		}

		.id-icon svg {
			width: 32px;
			height: 32px;
			color: #101828;
		}

		.id-hint-title {
			margin: 0;
			font-size: 18px;
			font-weight: 500;
			color: #111827;
		}

		.id-hint-subtitle {
			margin: 6px 0 0;
			font-size: 13px;
			color: #64748b;
		}

		.scan-action {
			margin: 16px auto 4px;
			width: min(100%, 380px);
			height: 56px;
			border: 0;
			border-radius: 10px;
			background: #3e4ba0;
			color: #ffffff;
			font-size: 16px;
			font-weight: 500;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			cursor: pointer;
		}

		.scan-action svg {
			width: 20px;
			height: 20px;
		}

		.id-types {
			margin: 14px auto 0;
			width: min(100%, 380px);
			background: #d8e0ec;
			border: 1px solid #b8c8e2;
			border-radius: 10px;
			padding: 10px 12px;
			color: #3e4ba0;
		}

		.id-types-title {
			margin: 0 0 8px;
			font-size: 12px;
			font-weight: 500;
		}

		.id-types-list {
			margin: 0;
			padding: 0;
			list-style: none;
		}

		.id-types-list li {
			font-size: 11px;
			line-height: 1.5;
			margin-bottom: 4px;
		}

		.id-types-list li:last-child {
			margin-bottom: 0;
		}

		.type-placeholder {
			max-width: 760px;
			margin: 16px auto 0;
			background: #ffffff;
			border: 1px solid #d9dde4;
			border-radius: 16px;
			padding: 24px;
		}

		.type-placeholder h2 {
			margin: 0 0 8px;
			font-size: 24px;
			color: #0f172a;
		}

		.type-placeholder p {
			margin: 0;
			color: #475569;
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

			.main {
				padding: 18px 14px;
			}

			.flow-head {
				flex-direction: column;
				gap: 8px;
			}

			.flow-step-name {
				font-size: 24px;
			}

			.scanner-zone {
				padding: 24px 8px;
			}

			.id-frame {
				width: 200px;
				height: 150px;
			}

			.id-hint-title {
				font-size: 16px;
			}

			.scan-action {
				height: 50px;
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

				<div class="menu-group open" id="registerMenuGroup">
					<button type="button" class="menu-item menu-toggle active" id="registerMenuToggle" aria-expanded="true" aria-controls="registerSubmenu">
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
						<a href="/guard/register?type=normal" class="submenu-item {{ request('type', 'normal') === 'normal' ? 'active' : '' }}">Normal Visitor</a>
						<a href="/guard/register?type=enrollee" class="submenu-item {{ request('type') === 'enrollee' ? 'active' : '' }}">Enrollee</a>
						<a href="/guard/register?type=contractor" class="submenu-item {{ request('type') === 'contractor' ? 'active' : '' }}">Contractor</a>
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
			@php($registerType = request('type', 'normal'))
			@if ($registerType === 'normal')
				<h1 class="page-title">Register Visitor</h1>
				<section class="register-flow">
					<div class="flow-head">
						<div class="flow-step-meta">
							<p class="flow-step-name">ID Scan</p>
							<p class="flow-step-count">Step 1 of 3</p>
						</div>
					</div>

					<div class="scanner-card">
						<div class="scanner-zone">
							<div class="id-frame" aria-hidden="true">
								<div class="id-hint">
									<span class="id-icon">
										<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<rect x="3" y="6" width="18" height="12" rx="2" stroke="currentColor" stroke-width="2"/>
											<circle cx="9" cy="12" r="2.2" fill="currentColor"/>
											<path d="M14 10h4M14 14h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
										</svg>
									</span>
									<p class="id-hint-title">Place ID card on scanner</p>
									<p class="id-hint-subtitle">Ensure card is flat and fully visible</p>
								</div>
							</div>
						</div>

						<button type="button" class="scan-action">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M7 4v3M17 4v3M4 8h16M6 20h12a2 2 0 0 0 2-2V8H4v10a2 2 0 0 0 2 2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
								<rect x="9" y="11" width="6" height="5" rx="1" fill="currentColor"/>
							</svg>
							Scan ID Card
						</button>

						<div class="id-types">
							<p class="id-types-title">Supported ID Types:</p>
							<ul class="id-types-list">
								<li>- National ID / Driver's License</li>
								<li>- Company ID / Employee Badge</li>
								<li>- Passport (Photo page)</li>
							</ul>
						</div>
					</div>
				</section>
			@elseif ($registerType === 'enrollee')
				<h1 class="page-title">Register Visitor</h1>
				<section class="type-placeholder" aria-label="Enrollee registration">
					<h2>Enrollee</h2>
					<p>Enrollee registration form content can be placed here.</p>
				</section>
			@elseif ($registerType === 'contractor')
				<h1 class="page-title">Register Visitor</h1>
				<section class="type-placeholder" aria-label="Contractor registration">
					<h2>Contractor</h2>
					<p>Contractor registration form content can be placed here.</p>
				</section>
			@endif
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
