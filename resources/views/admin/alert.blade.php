<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
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

		.alert-filters-card {
			margin-top: 14px;
			padding: 12px;
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		}

		.alert-filters-row {
			display: grid;
			grid-template-columns: 1.2fr repeat(4, minmax(0, 1fr)) 1fr 1fr auto;
			gap: 10px;
			align-items: center;
		}

		.alert-filter-input,
		.alert-filter-select,
		.alert-filter-date {
			height: 38px;
			border: 1px solid #d6dde8;
			outline: none;
			background: #f3f4f6;
			border-radius: 8px;
			padding: 0 12px;
			font-size: 14px;
			color: #1f2a44;
		}

		.alert-filter-input::placeholder {
			color: #64748b;
		}

		.alert-filter-clear {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			height: 38px;
			padding: 0 14px;
			border-radius: 8px;
			text-decoration: none;
			font-size: 13px;
			font-weight: 700;
			color: #334155;
			background: #eef2ff;
			border: 1px solid #d6deef;
			white-space: nowrap;
		}

		.alert-filter-clear:hover {
			background: #e2e8f0;
		}

		.alert-filter-clear.disabled {
			opacity: 0.55;
			pointer-events: none;
		}

		.alert-filter-count {
			margin: 8px 2px 0;
			font-size: 13px;
			color: #334155;
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

			.alert-filters-row {
				grid-template-columns: repeat(2, minmax(0, 1fr));
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

				/* Alert details modal - card layout (requested UI) */
				.alert-modal {
					display: none;
					position: fixed;
					inset: 0;
					z-index: 60;
					background: rgba(15, 23, 42, 0.35);
					overflow-y: auto;
					padding: 14px;
				}

				.alert-modal-card {
					width: 940px;
					max-width: calc(100% - 24px);
					margin: 12px auto;
					background: #ffffff;
					border-radius: 16px;
					box-shadow: 0 10px 30px rgba(2, 6, 23, 0.2);
					overflow: hidden;
				}

				.alert-modal-header {
					display: flex;
					justify-content: space-between;
					align-items: center;
					padding: 16px 22px;
					border-bottom: 1px solid #e9edf3;
					background: #fff;
				}

				.alert-modal-title-wrap small {
					color: #64748b;
					font-size: 12px;
				}

				.alert-modal-title {
					margin: 0;
					font-size: 24px;
					line-height: 1.1;
					font-weight: 700;
					color: #0f172a;
				}

				.alert-modal-close {
					background: #eef2f7;
					border: 1px solid #d9e1ec;
					width: 34px;
					height: 34px;
					border-radius: 999px;
					font-size: 18px;
					line-height: 1;
					color: #64748b;
					cursor: pointer;
				}

				.alert-modal-body {
					padding: 18px 22px;
					background: #f8fafc;
					max-height: calc(100vh - 170px);
					overflow-y: auto;
				}

				.alert-info-card {
					background: #fff;
					border: 0;
					border-radius: 14px;
					box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
					margin-bottom: 14px;
					overflow: hidden;
				}

				.alert-info-card .card-title {
					margin: 0;
					font-size: 18px;
					font-weight: 700;
					padding: 14px 16px;
					border-bottom: 1px solid #eef2f7;
				}

				.alert-info-card .card-content {
					padding: 14px 16px;
				}

				.detail-grid {
					display: grid;
					grid-template-columns: repeat(2, minmax(0, 1fr));
					gap: 12px 16px;
				}

				.detail-grid .full {
					grid-column: 1 / -1;
				}

				.detail-label {
					font-size: 12px;
					color: #64748b;
					margin-bottom: 4px;
				}

				.detail-value {
					font-size: 14px;
					font-weight: 600;
					color: #0f172a;
					word-break: break-word;
				}

				.badge-pill {
					display: inline-flex;
					align-items: center;
					padding: 6px 10px;
					border-radius: 999px;
					font-size: 12px;
					font-weight: 700;
				}

				.badge-danger { background: #dc2626; color: #fff; }
				.badge-success { background: #16a34a; color: #fff; }
				.badge-warning { background: #f59e0b; color: #111827; }
				.badge-high { background: #f97316; color: #fff; }
				.badge-medium { background: #f59e0b; color: #111827; }
				.badge-low { background: #64748b; color: #fff; }
				.badge-critical { background: #dc2626; color: #fff; }

				.alert-modal-footer {
					padding: 14px 22px;
					background: #fff;
					display: flex;
					justify-content: flex-end;
					gap: 10px;
					border-top: 1px solid #e9edf3;
				}

				.modal-line {
					margin: 0 0 10px;
					font-size: 15px;
					line-height: 1.2;
					color: #475569;
				}

				.modal-line span {
					font-weight: 600;
					color: #0f172a;
				}

				.resolve-btn {
					background: #2563eb;
					color: #ffffff;
					border: 0;
					border-radius: 10px;
					padding: 10px 18px;
					font-size: 14px;
					font-weight: 700;
					cursor: pointer;
					box-shadow: 0 5px 15px rgba(37, 99, 235, 0.28);
				}

				.btn-secondary {
					background: #eef2f7;
					color: #334155;
					border: 1px solid #d9e1ec;
					border-radius: 10px;
					padding: 10px 18px;
					font-size: 14px;
					font-weight: 600;
					cursor: pointer;
				}

				.resolve-flow-modal {
					display: none;
					position: fixed;
					inset: 0;
					z-index: 70;
					background: rgba(15, 23, 42, 0.4);
					overflow-y: auto;
					padding: 14px;
				}

				.resolve-flow-card {
					width: 620px;
					max-width: calc(100% - 24px);
					margin: 24px auto;
					background: #ffffff;
					border-radius: 16px;
					box-shadow: 0 10px 30px rgba(2, 6, 23, 0.2);
					overflow: hidden;
				}

				.resolve-flow-body {
					padding: 18px 22px;
				}

				.resolve-summary {
					padding: 0 0 8px;
				}

				.resolve-summary .modal-line {
					margin-bottom: 6px;
					font-size: 14px;
				}

				.resolve-divider {
					border: 0;
					height: 1px;
					background: #e5e7eb;
					margin: 14px 0;
				}

				.resolve-notes-wrap {
					margin-top: 0;
					padding-top: 0;
					border-top: 0;
				}

				.resolve-notes-label {
					display: block;
					font-size: 14px;
					font-weight: 700;
					color: #111827;
					margin-bottom: 8px;
				}

				.resolve-notes-input {
					width: 100%;
					min-height: 92px;
					resize: vertical;
					border: 1px solid #d7e0eb;
					border-radius: 10px;
					padding: 10px 12px;
					font: inherit;
					font-size: 14px;
					line-height: 1.45;
					color: #0f172a;
					outline: none;
				}

				.resolve-notes-input:focus {
					border-color: #93c5fd;
					box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
				}

				.resolve-warning {
					margin: 8px 0 0;
					font-size: 12px;
					color: #6b7280;
				}

				.resolve-flow-footer {
					padding: 14px 22px;
					background: #ffffff;
					display: flex;
					justify-content: flex-end;
					gap: 12px;
					border-top: 1px solid #e9edf3;
				}

				.resolve-action-btn {
					border-radius: 999px;
					padding: 9px 18px;
					font-size: 14px;
					font-weight: 700;
					border: 0;
					cursor: pointer;
				}

				.resolve-action-btn.cancel {
					background: #6b7280;
					color: #fff;
				}

				.resolve-action-btn.confirm {
					background: #16a34a;
					color: #fff;
				}

				.resolve-severity-badge {
					display: inline-flex;
					align-items: center;
					padding: 4px 10px;
					border-radius: 999px;
					font-size: 12px;
					font-weight: 700;
					color: #fff;
					background: #f97316;
				}

				@media (max-width: 920px) {
					.alert-modal-title { font-size: 20px; }
					.alert-modal-card { width: 100%; max-width: calc(100% - 12px); }
					.detail-grid { grid-template-columns: 1fr; }
					.modal-section-title { font-size: 13px; }
					.modal-line { font-size: 14px; }
					.resolve-btn { font-size: 13px; padding: 9px 14px; }
					.alert-grid { grid-template-columns: 1fr; gap: 18px; }
					.alert-divider { display: none; }
					.resolve-flow-footer { flex-direction: column-reverse; align-items: stretch; }
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
			<h1 class="page-title">Security Alerts</h1>

			<div class="alert-stats">
				<div class="stat-card unresolved">
					<span class="stat-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="m12 3 10 18H2L12 3Zm0 6v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<circle cx="12" cy="17" r="1.2" fill="currentColor"/>
						</svg>
					</span>
					<p class="stat-number" id="unresolvedCount">{{ $unresolvedCount ?? 0 }}</p>
					<p class="stat-label">Unresolved Alerts</p>
				</div>

				<div class="stat-card resolved">
					<span class="stat-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
							<path d="m8.5 12.5 2.5 2.5 4.5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</span>
					<p class="stat-number" id="resolvedCount">{{ $resolvedCount ?? 0 }}</p>
					<p class="stat-label">Resolved Alerts</p>
				</div>

				<div class="stat-card total">
					<span class="stat-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="m12 3 10 18H2L12 3Zm0 6v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<circle cx="12" cy="17" r="1.2" fill="currentColor"/>
						</svg>
					</span>
					<p class="stat-number" id="totalCount">{{ $total ?? 0 }}</p>
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
					<p class="stat-number" id="criticalCount">{{ isset($criticalCount) ? $criticalCount : 0 }}</p>
					<p class="stat-label">Critical Alerts</p>
				</div>
			</div>

			<div class="alert-filters-card">
				<form method="GET" action="{{ url('/admin/alerts') }}">
					<div class="alert-filters-row">
						<input class="alert-filter-input" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search visitor..." aria-label="Search visitor">

						<select class="alert-filter-select" name="alert_type" aria-label="Filter by alert type" onchange="this.form.submit()">
							<option value="">Alert Type</option>
							@foreach(($alertTypeOptions ?? []) as $type)
								<option value="{{ $type }}" @selected(($filters['alert_type'] ?? '') === $type)>{{ $type }}</option>
							@endforeach
						</select>

						<select class="alert-filter-select" name="severity" aria-label="Filter by severity" onchange="this.form.submit()">
							<option value="">Severity</option>
							@foreach(($severityOptions ?? []) as $sev)
								<option value="{{ $sev }}" @selected(($filters['severity'] ?? '') === $sev)>{{ $sev }}</option>
							@endforeach
						</select>

						<select class="alert-filter-select" name="status" aria-label="Filter by status" onchange="this.form.submit()">
							<option value="">Status</option>
							@foreach(($statusOptions ?? []) as $st)
								<option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ $st }}</option>
							@endforeach
						</select>

						<select class="alert-filter-select" name="office" aria-label="Filter by office" onchange="this.form.submit()">
							<option value="">Office</option>
							@foreach(($officeOptions ?? []) as $office)
								<option value="{{ $office }}" @selected(($filters['office'] ?? '') === $office)>{{ $office }}</option>
							@endforeach
						</select>

						<input class="alert-filter-date" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" aria-label="Date from" onchange="this.form.submit()">
						<input class="alert-filter-date" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" aria-label="Date to" onchange="this.form.submit()">

						<a href="{{ url('/admin/alerts') }}" class="alert-filter-clear {{ !($hasActiveFilters ?? false) ? 'disabled' : '' }}">Clear</a>
					</div>
				</form>
				<p class="alert-filter-count">Showing {{ count($alerts ?? []) }} filtered alerts ({{ $total ?? 0 }} total)</p>
			</div>

			<section class="alerts-panel">
				<div class="panel-tabs">
					<a href="#" class="tab-link {{ !($hasActiveFilters ?? false) ? 'active' : '' }}" data-filter="unresolved" data-empty-subtitle="All alerts have been resolved">Unresolved Alerts ({{ $unresolvedCount ?? 0 }})</a>
					<a href="#" class="tab-link {{ ($hasActiveFilters ?? false) ? 'active' : '' }}" data-filter="all" data-empty-subtitle="No security alerts to display">All Alerts ({{ $total ?? 0 }})</a>
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
									<tr style="border-bottom:1px solid #f1f5f9;" data-status="{{ strtolower($alert['status'] ?? 'unknown') }}" data-alert-id="{{ $alert['alert_id'] ?? '' }}">
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
										<button class="view-btn" data-alert-id="{{ $alert['alert_id'] ?? '' }}" style="background:#4b5cd1;color:#fff;padding:6px 10px;border-radius:8px;border:0;">View</button>
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

	<!-- Alert Details Modal -->
	<div id="alertModal" class="alert-modal">
		<div class="alert-modal-card" role="dialog" aria-modal="true" aria-labelledby="alertDetailsTitle">
			<div class="alert-modal-header">
				<div class="alert-modal-title-wrap">
					<h3 id="alertDetailsTitle" class="alert-modal-title">Alert Details</h3>
					<small>View complete alert information</small>
				</div>
				<button id="closeAlertBtn" class="alert-modal-close" type="button" aria-label="Close">×</button>
			</div>

			<div class="alert-modal-body">
				<section class="alert-info-card">
					<h4 class="card-title">Alert Information</h4>
					<div class="card-content detail-grid">
						<div>
							<div class="detail-label">Alert ID</div>
							<div class="detail-value" id="m_alert_id">-</div>
						</div>
						<div>
							<div class="detail-label">Alert Type</div>
							<div class="detail-value" id="m_type">-</div>
						</div>
						<div>
							<div class="detail-label">Severity</div>
							<div><span id="m_severity" class="badge-pill badge-medium">Medium</span></div>
						</div>
						<div>
							<div class="detail-label">Status</div>
							<div><span id="m_status" class="badge-pill badge-danger">Unresolved</span></div>
						</div>
						<div class="full">
							<div class="detail-label">Message</div>
							<div class="detail-value" id="m_message">-</div>
						</div>
						<div class="full">
							<div class="detail-label">Created At</div>
							<div class="detail-value" id="m_created_at">-</div>
						</div>
					</div>
				</section>

				<section class="alert-info-card">
					<h4 class="card-title">Visitor Information</h4>
					<div class="card-content detail-grid">
						<div>
							<div class="detail-label">Visitor Name</div>
							<div class="detail-value" id="m_visitor_name">-</div>
						</div>
						<div>
							<div class="detail-label">Pass Number</div>
							<div class="detail-value" id="m_pass_no">-</div>
						</div>
						<div>
							<div class="detail-label">Control Number</div>
							<div class="detail-value" id="m_control_no">-</div>
						</div>
						<div>
							<div class="detail-label">Contact Number</div>
							<div class="detail-value" id="m_contact">-</div>
						</div>
					</div>
				</section>

				<section class="alert-info-card">
					<h4 class="card-title">Visit Information</h4>
					<div class="card-content detail-grid">
						<div><div class="detail-label">Visit ID</div><div class="detail-value" id="m_visit_id">-</div></div>
						<div><div class="detail-label">Visit Type</div><div class="detail-value" id="m_visit_type">-</div></div>
						<div><div class="detail-label">Purpose</div><div class="detail-value" id="m_purpose">-</div></div>
						<div><div class="detail-label">Entry Time</div><div class="detail-value" id="m_entry_time">-</div></div>
						<div><div class="detail-label">Exit Time</div><div class="detail-value" id="m_exit_time">-</div></div>
						<div><div class="detail-label">Duration Minutes</div><div class="detail-value" id="m_duration">-</div></div>
						<div class="full"><div class="detail-label">Assigned Primary Office</div><div class="detail-value" id="m_primary_office">-</div></div>
					</div>
				</section>

				<section class="alert-info-card">
					<h4 class="card-title">Scan Information</h4>
					<div class="card-content detail-grid">
						<div><div class="detail-label">Scan ID</div><div class="detail-value" id="m_scan_id">-</div></div>
						<div><div class="detail-label">Scanned Office</div><div class="detail-value" id="m_scanned_office">-</div></div>
						<div><div class="detail-label">Scanned By Guard/Staff</div><div class="detail-value" id="m_scanned_by">-</div></div>
						<div><div class="detail-label">Scan Time</div><div class="detail-value" id="m_scan_time">-</div></div>
						<div><div class="detail-label">Validation Status</div><div class="detail-value" id="m_validation_status">-</div></div>
						<div><div class="detail-label">Remarks</div><div class="detail-value" id="m_remarks">-</div></div>
					</div>
				</section>

				<section class="alert-info-card">
					<h4 class="card-title">Resolution Information</h4>
					<div class="card-content">
						<div id="m_unresolved_text" class="detail-value" style="font-style: italic; color:#64748b;">Not yet resolved</div>
						<div id="m_resolved_details" style="display:none;">
							<div class="detail-grid">
								<div><div class="detail-label">Resolved By</div><div class="detail-value" id="m_resolved_by">-</div></div>
								<div><div class="detail-label">Resolved At</div><div class="detail-value" id="m_resolved_at">-</div></div>
								<div class="full"><div class="detail-label">Resolution Notes</div><div class="detail-value" id="m_resolution_notes">-</div></div>
							</div>
						</div>
					</div>
				</section>
			</div>

			<div class="alert-modal-footer">
				<button class="btn-secondary js-close-alert" type="button">Close</button>
				<button id="resolveAlertBtn" class="resolve-btn" type="button">Resolve Alert</button>
			</div>
		</div>
	</div>

	<!-- Resolve Alert Modal -->
	<div id="resolveModal" class="resolve-flow-modal">
		<div class="resolve-flow-card" role="dialog" aria-modal="true" aria-labelledby="resolveAlertTitle">
			<div class="alert-modal-header">
				<h3 id="resolveAlertTitle" class="alert-modal-title" style="font-size:22px;">Resolve Alert</h3>
				<button id="closeResolveModalBtn" class="alert-modal-close" type="button" aria-label="Close">×</button>
			</div>

			<form onsubmit="return false;">
				<div class="resolve-flow-body">
					<div class="resolve-summary">
						<p class="modal-line"><strong>Alert ID:</strong> <span id="r_alert_id" class="value">-</span></p>
						<p class="modal-line"><strong>Visitor:</strong> <span id="r_visitor" class="value">-</span></p>
						<p class="modal-line"><strong>Type:</strong> <span id="r_alert_type" class="value">-</span></p>
						<p class="modal-line"><strong>Severity:</strong> <span id="r_severity" class="resolve-severity-badge">Medium</span></p>
					</div>

					<hr class="resolve-divider">

					<div class="resolve-notes-wrap">
						<label for="resolveNotes" class="resolve-notes-label">Resolution Notes</label>
						<textarea id="resolveNotes" class="resolve-notes-input" rows="4" placeholder="Enter how the alert was resolved..."></textarea>
						<p class="resolve-warning">⚠️ Please describe how this alert was resolved.</p>
					</div>
				</div>

				<div class="resolve-flow-footer">
					<button id="cancelResolveBtn" class="resolve-action-btn cancel" type="button">Cancel</button>
					<button id="confirmResolveBtn" class="resolve-action-btn confirm" type="button">Resolve</button>
				</div>
			</form>
		</div>
	</div>

	<script>
		// Export alerts to JS for modal/detail interactions
		const ALERTS = @json($alerts ?? []);

		const userMenuGroup = document.getElementById('userMenuGroup');
		const userMenuToggle = document.getElementById('userMenuToggle');
		const alertTabLinks = document.querySelectorAll('.panel-tabs .tab-link');
		const emptySubtitle = document.getElementById('emptySubtitle');
		const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
		let pendingResolveAlertId = null;

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

		// Modal handling -------------------------------------------------
		function formatDateTime(iso) {
			if (!iso) return ['-', ''];
			const d = new Date(iso);
			const optsDate = { month: 'short', day: '2-digit', year: 'numeric' };
			const date = d.toLocaleDateString(undefined, optsDate);
			const time = d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
			return [date, time];
		}

		function getSeverityClass(severity) {
			const s = String(severity || '').toLowerCase();
			if (s === 'critical') return 'badge-pill badge-critical';
			if (s === 'high') return 'badge-pill badge-high';
			if (s === 'medium') return 'badge-pill badge-medium';
			if (s === 'low') return 'badge-pill badge-low';
			return 'badge-pill badge-medium';
		}

		function getStatusClass(status) {
			return String(status || '').toLowerCase() === 'resolved'
				? 'badge-pill badge-success'
				: 'badge-pill badge-danger';
		}

		function getResolveSeverityBg(severity) {
			const s = String(severity || '').toLowerCase();
			if (s === 'critical') return '#dc2626';
			if (s === 'high') return '#f97316';
			if (s === 'medium') return '#f59e0b';
			if (s === 'low') return '#64748b';
			return '#f59e0b';
		}

		function openAlertModal(alertId) {
			const alert = ALERTS.find(a => String(a.alert_id) === String(alertId));
			if (!alert) return;

			const modal = document.getElementById('alertModal');
			if (!modal) return;

			// Populate alert information
			const [date, time] = formatDateTime(alert.created_at);
			modal.querySelector('#m_alert_id').textContent = alert.alert_id || '-';
			modal.querySelector('#m_type').textContent = alert.alert_type || '-';
			modal.querySelector('#m_message').textContent = alert.message || 'Visitor scanned at wrong office';
			modal.querySelector('#m_created_at').textContent = `${date} ${time}`.trim();

			const severityValue = alert.severity || 'Medium';
			const severityEl = modal.querySelector('#m_severity');
			severityEl.textContent = severityValue;
			severityEl.className = getSeverityClass(severityValue);

			const statusValue = alert.status || 'Unresolved';
			const statusEl = modal.querySelector('#m_status');
			statusEl.textContent = statusValue;
			statusEl.className = getStatusClass(statusValue);

			// Visitor
			modal.querySelector('#m_visitor_name').textContent = ((alert.visitor && (alert.visitor.first_name || alert.visitor.last_name)) ? ((alert.visitor.first_name || '') + ' ' + (alert.visitor.last_name || '')).trim() : '-');
			modal.querySelector('#m_pass_no').textContent = (alert.visitor && alert.visitor.pass_number) ? alert.visitor.pass_number : (alert.pass_number || '-');
			modal.querySelector('#m_control_no').textContent = (alert.visitor && alert.visitor.control_number) ? alert.visitor.control_number : (alert.control_number || '-');
			modal.querySelector('#m_contact').textContent = alert.visitor && alert.visitor.contact_no ? alert.visitor.contact_no : '-';

			// Visit
			const visitType = (alert.visit && alert.visit.visit_type)
				? (Array.isArray(alert.visit.visit_type)
					? (alert.visit.visit_type[0] && alert.visit.visit_type[0].visit_type_name)
					: alert.visit.visit_type.visit_type_name)
				: null;

			const visitDuration = (alert.visit && alert.visit.duration_minutes !== undefined && alert.visit.duration_minutes !== null)
				? `${alert.visit.duration_minutes} mins`
				: '-';

			modal.querySelector('#m_visit_id').textContent = alert.visit && alert.visit.visit_id ? alert.visit.visit_id : (alert.visit_id || '-');
			modal.querySelector('#m_visit_type').textContent = visitType || (alert.visit && alert.visit.type ? alert.visit.type : (alert.visit_type || '-'));
			modal.querySelector('#m_purpose').textContent = alert.visit && alert.visit.purpose_reason ? alert.visit.purpose_reason : (alert.visit && alert.visit.purpose ? alert.visit.purpose : (alert.purpose || '-'));

			const [entryDate, entryTime] = formatDateTime(alert.visit && alert.visit.entry_time ? alert.visit.entry_time : null);
			modal.querySelector('#m_entry_time').textContent = entryDate === '-' ? '-' : `${entryDate} ${entryTime}`;

			const [exitDate, exitTime] = formatDateTime(alert.visit && alert.visit.exit_time ? alert.visit.exit_time : null);
			modal.querySelector('#m_exit_time').textContent = exitDate === '-' ? '-' : `${exitDate} ${exitTime}`;
			modal.querySelector('#m_duration').textContent = visitDuration;
			modal.querySelector('#m_primary_office').textContent = (alert.visit && alert.visit.office && alert.visit.office.office_name) ? alert.visit.office.office_name : (alert.visit && alert.visit.primary_office_id ? alert.visit.primary_office_id : '-');

			// Scan
			const scannedBy = (alert.office_scan && alert.office_scan.users)
				? (Array.isArray(alert.office_scan.users)
					? `${alert.office_scan.users[0]?.first_name || ''} ${alert.office_scan.users[0]?.last_name || ''}`.trim()
					: `${alert.office_scan.users.first_name || ''} ${alert.office_scan.users.last_name || ''}`.trim())
				: null;

			const validationStatus = (alert.office_scan && alert.office_scan.validation_status)
				? (Array.isArray(alert.office_scan.validation_status)
					? (alert.office_scan.validation_status[0]?.status_name || null)
					: (alert.office_scan.validation_status.status_name || alert.office_scan.validation_status))
				: null;

			modal.querySelector('#m_scan_id').textContent = alert.office_scan && alert.office_scan.scan_id ? alert.office_scan.scan_id : (alert.scan_id || '-');
			modal.querySelector('#m_scanned_office').textContent = alert.office_scan && alert.office_scan.office && alert.office_scan.office.office_name ? alert.office_scan.office.office_name : (alert.scanned_office || '-');
			modal.querySelector('#m_scanned_by').textContent = scannedBy || (alert.office_scan && alert.office_scan.scanned_by ? alert.office_scan.scanned_by : (alert.scanned_by || '-'));
			modal.querySelector('#m_scan_time').textContent = alert.office_scan && alert.office_scan.scan_time ? alert.office_scan.scan_time : time;
			modal.querySelector('#m_validation_status').textContent = validationStatus || (alert.validation_status || 'Invalid');
			modal.querySelector('#m_remarks').textContent = alert.office_scan && alert.office_scan.remarks ? alert.office_scan.remarks : (alert.remarks || 'Visitor entered incorrect office');

			// Resolution
			const resolvedByObj = alert.resolved_by
				? (Array.isArray(alert.resolved_by) ? alert.resolved_by[0] : alert.resolved_by)
				: null;
			const resolvedBy = (resolvedByObj && (resolvedByObj.first_name || resolvedByObj.last_name))
				? `${resolvedByObj.first_name || ''} ${resolvedByObj.last_name || ''}`.trim()
				: (alert.resolved_by_name || '-');
			const [resolvedDate, resolvedTime] = formatDateTime(alert.resolved_at || null);
			const resolvedAtText = resolvedDate === '-' ? '-' : `${resolvedDate} ${resolvedTime}`;
			const resolutionNotes = alert.resolution_notes || alert.resolution_note || alert.notes || 'Not yet resolved';
			const isResolved = String(alert.status || '').toLowerCase() === 'resolved';

			const unresolvedTextEl = modal.querySelector('#m_unresolved_text');
			const resolvedDetailsEl = modal.querySelector('#m_resolved_details');
			if (unresolvedTextEl && resolvedDetailsEl) {
				unresolvedTextEl.style.display = isResolved ? 'none' : 'block';
				resolvedDetailsEl.style.display = isResolved ? 'block' : 'none';
			}

			modal.querySelector('#m_resolved_by').textContent = resolvedBy;
			modal.querySelector('#m_resolved_at').textContent = resolvedAtText;
			modal.querySelector('#m_resolution_notes').textContent = isResolved ? resolutionNotes : '-';

			// Attach resolve button dataset
			const resolveBtn = modal.querySelector('#resolveAlertBtn');
			resolveBtn.dataset.alertId = alert.alert_id;

			modal.style.display = 'block';
		}

		function closeAlertModal() {
			const modal = document.getElementById('alertModal');
			if (modal) modal.style.display = 'none';
		}

		function openResolveModal(alertId) {
			const alert = ALERTS.find(a => String(a.alert_id) === String(alertId));
			if (!alert) return;

			const resolveModal = document.getElementById('resolveModal');
			if (!resolveModal) return;

			const visitorName = ((alert.visitor && (alert.visitor.first_name || alert.visitor.last_name))
				? `${alert.visitor.first_name || ''} ${alert.visitor.last_name || ''}`.trim()
				: '-');

			const severity = alert.severity ? String(alert.severity) : 'Medium';

			resolveModal.querySelector('#r_alert_id').textContent = alert.alert_id || '-';
			resolveModal.querySelector('#r_visitor').textContent = visitorName;
			resolveModal.querySelector('#r_alert_type').textContent = alert.alert_type || '-';
			const severityEl = resolveModal.querySelector('#r_severity');
			severityEl.textContent = severity;
			severityEl.style.backgroundColor = getResolveSeverityBg(severity);
			severityEl.style.color = severity.toLowerCase() === 'medium' ? '#111827' : '#ffffff';
			resolveModal.querySelector('#resolveNotes').value = '';

			pendingResolveAlertId = alert.alert_id;
			resolveModal.style.display = 'block';
		}

		function closeResolveModal() {
			const resolveModal = document.getElementById('resolveModal');
			if (resolveModal) resolveModal.style.display = 'none';
			pendingResolveAlertId = null;
		}

		async function resolveAlertClient(alertId, notes) {
			if (!notes) {
				alert('Please add Resolution Notes before resolving this alert.');
				return;
			}

			// update ALERTS array
			const idx = ALERTS.findIndex(a => String(a.alert_id) === String(alertId));
			if (idx === -1) return;
			const previousStatus = String(ALERTS[idx].status || '').toLowerCase();

			try {
				const response = await fetch(`/admin/alerts/${encodeURIComponent(alertId)}/resolve`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						'X-CSRF-TOKEN': csrfToken,
					},
					body: JSON.stringify({ resolution_notes: notes }),
				});

				const payload = await response.json().catch(() => ({}));
				if (!response.ok) {
					throw new Error(payload.message || 'Failed to resolve alert.');
				}

				const updated = payload.alert || {};
				ALERTS[idx] = {
					...ALERTS[idx],
					...updated,
					status: updated.status || 'Resolved',
					resolved_at: updated.resolved_at || new Date().toISOString(),
					resolution_notes: updated.resolution_notes || notes,
				};

				if (Object.prototype.hasOwnProperty.call(updated, 'resolved_by')) {
					ALERTS[idx].resolved_by = updated.resolved_by;
				}
			} catch (error) {
				alert(error.message || 'Unable to resolve alert at the moment.');
				return;
			}

			// update DOM row
			const row = document.querySelector(`tr[data-alert-id="${alertId}"]`);
			if (row) {
				row.dataset.status = 'resolved';
				const statusTd = row.querySelector('td:nth-last-child(2)');
				if (statusTd) statusTd.textContent = 'Resolved';
			}

			// update counts
			const unresolvedEl = document.getElementById('unresolvedCount');
			const resolvedEl = document.getElementById('resolvedCount');
			const totalEl = document.getElementById('totalCount');
			const criticalEl = document.getElementById('criticalCount');

			const prevUn = parseInt(unresolvedEl.textContent || '0', 10);
			const prevRes = parseInt(resolvedEl.textContent || '0', 10);
			if (!isNaN(prevUn) && !isNaN(prevRes) && previousStatus !== 'resolved') {
				unresolvedEl.textContent = Math.max(prevUn - 1, 0);
				resolvedEl.textContent = prevRes + 1;
			}

			// update tab labels
			const tabs = document.querySelectorAll('.panel-tabs .tab-link');
			tabs.forEach(t => {
				if (t.dataset.filter === 'unresolved') t.textContent = `Unresolved Alerts (${unresolvedEl.textContent})`;
				if (t.dataset.filter === 'resolved') t.textContent = `Resolved (${resolvedEl.textContent})`;
				if (t.dataset.filter === 'all') t.textContent = `All Alerts (${totalEl.textContent})`;
			});

			// reapply current filter so resolved row may hide if on unresolved tab
			const active = document.querySelector('.panel-tabs .tab-link.active');
			if (active) applyFilter(active.dataset.filter || 'all');

			closeResolveModal();
			closeAlertModal();
		}

		// Attach click handlers to dynamic buttons
		document.addEventListener('click', async function (e) {
			if (e.target && e.target.matches('.view-btn')) {
				const id = e.target.dataset.alertId;
				openAlertModal(id);
			}
			if (e.target && (e.target.matches('#closeAlertBtn') || e.target.matches('.js-close-alert') || e.target.id === 'alertModal')) {
				closeAlertModal();
			}
			if (e.target && e.target.matches('#resolveAlertBtn')) {
				const id = e.target.dataset.alertId;
				openResolveModal(id);
			}
			if (e.target && (e.target.matches('#closeResolveModalBtn') || e.target.matches('#cancelResolveBtn'))) {
				closeResolveModal();
			}
			if (e.target && e.target.id === 'resolveModal') {
				closeResolveModal();
			}
			if (e.target && e.target.matches('#confirmResolveBtn')) {
				if (!pendingResolveAlertId) return;
				const notesEl = document.getElementById('resolveNotes');
				const notes = notesEl ? notesEl.value.trim() : '';
				const confirmBtn = e.target;
				confirmBtn.disabled = true;
				confirmBtn.textContent = 'Resolving...';
				await resolveAlertClient(pendingResolveAlertId, notes);
				confirmBtn.disabled = false;
				confirmBtn.textContent = 'Resolve';
			}
		});
	</script>
</body>
</html>
