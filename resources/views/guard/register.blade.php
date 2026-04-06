<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
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
			height: 100vh;
			overflow: hidden;
		}

		.sidebar {
			width: 280px;
			background: var(--sidebar-bg);
			color: var(--text-white);
			padding: 12px 10px;
			display: flex;
			flex-direction: column;
			border-right: 1px solid rgba(0, 0, 0, 0.05);
			height: 100vh;
			overflow: hidden;
			flex-shrink: 0;
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
			background: #111827;
			border-radius: 10px;
			padding: 50px 32px;
			display: flex;
			align-items: center;
			justify-content: center;
			position: relative;
			overflow: hidden;
			width: 100%;
		}

		.camera-feed {
			position: absolute;
			inset: 0;
			width: 100%;
			height: 100%;
			object-fit: contain;
			display: block;
			background: #111827;
		}

		.frozen-frame {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			display: none;
			z-index: 3;
			max-width: 100%;
			max-height: 100%;
			object-fit: contain;
		}

		.frozen-frame.visible {
			display: block;
		}

		.scanner-overlay {
			position: absolute;
			inset: 0;
			z-index: 2;
			display: flex;
			align-items: center;
			justify-content: center;
			pointer-events: none;
		}

		.camera-status {
			margin: 10px 0 0;
			text-align: center;
			font-size: 13px;
			color: #4f5b75;
		}

		.is-hidden {
			display: none !important;
		}

		.picture-guide {
			width: min(78%, 300px);
			aspect-ratio: 3 / 4;
			position: relative;
			border-radius: 18px;
			border: 2px solid rgba(99, 116, 241, 0.9);
			background: rgba(15, 23, 42, 0.18);
			box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14);
		}

		.picture-guide .corner {
			position: absolute;
			width: 34px;
			height: 34px;
			border-color: #3f4a9f;
			border-style: solid;
		}

		.picture-guide .corner.tl {
			top: 0;
			left: 0;
			border-width: 5px 0 0 5px;
			border-top-left-radius: 16px;
		}

		.picture-guide .corner.tr {
			top: 0;
			right: 0;
			border-width: 5px 5px 0 0;
			border-top-right-radius: 16px;
		}

		.picture-guide .corner.bl {
			bottom: 0;
			left: 0;
			border-width: 0 0 5px 5px;
			border-bottom-left-radius: 16px;
		}

		.picture-guide .corner.br {
			bottom: 0;
			right: 0;
			border-width: 0 5px 5px 0;
			border-bottom-right-radius: 16px;
		}

		.picture-guide .face-oval {
			position: absolute;
			left: 50%;
			top: 50%;
			transform: translate(-50%, -52%);
			width: 58%;
			height: 68%;
			border-radius: 50% / 46%;
			border: 2px dashed rgba(191, 219, 254, 0.85);
		}

		.picture-guide .shoulder-line {
			position: absolute;
			left: 16%;
			right: 16%;
			bottom: 12%;
			height: 2px;
			background: rgba(191, 219, 254, 0.85);
			border-radius: 999px;
		}

		.picture-guide .id-holder {
			position: absolute;
			right: 10%;
			bottom: 18%;
			width: 30%;
			aspect-ratio: 1.58 / 1;
			border: 2px dashed rgba(250, 204, 21, 0.92);
			border-radius: 8px;
			background: rgba(250, 204, 21, 0.12);
		}

		.id-guide {
			width: min(93%, 430px);
			aspect-ratio: 1.58 / 1;
			position: relative;
			border-radius: 16px;
			border: 2px solid rgba(99, 116, 241, 0.95);
			background: rgba(15, 23, 42, 0.2);
			backdrop-filter: blur(1px);
			box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
		}

		.id-guide .corner {
			position: absolute;
			width: 36px;
			height: 36px;
			border-color: #3f4a9f;
			border-style: solid;
			pointer-events: none;
		}

		.id-guide .corner.tl {
			top: 0;
			left: 0;
			border-width: 5px 0 0 5px;
			border-top-left-radius: 16px;
		}

		.id-guide .corner.tr {
			top: 0;
			right: 0;
			border-width: 5px 5px 0 0;
			border-top-right-radius: 16px;
		}

		.id-guide .corner.bl {
			bottom: 0;
			left: 0;
			border-width: 0 0 5px 5px;
			border-bottom-left-radius: 16px;
		}

		.id-guide .corner.br {
			bottom: 0;
			right: 0;
			border-width: 0 5px 5px 0;
			border-bottom-right-radius: 16px;
		}

		.id-guide-layout {
			position: absolute;
			inset: 16px;
			display: grid;
			grid-template-columns: 32% 1fr;
			gap: 12px;
		}

		.id-guide-photo {
			border: 2px solid rgba(191, 219, 254, 0.75);
			border-radius: 10px;
			background: rgba(148, 163, 184, 0.18);
		}

		.id-guide-info {
			display: flex;
			flex-direction: column;
			justify-content: space-between;
		}

		.id-guide-line {
			height: 9px;
			border-radius: 999px;
			background: rgba(191, 219, 254, 0.8);
		}

		.id-guide-line.short {
			width: 64%;
		}

		.id-guide-line.medium {
			width: 82%;
		}

		.id-guide-line.long {
			width: 100%;
		}

		.id-guide-chip {
			width: 42px;
			height: 30px;
			border-radius: 6px;
			border: 2px solid rgba(250, 204, 21, 0.75);
			background: rgba(250, 204, 21, 0.16);
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

		.scan-action:disabled {
			opacity: 0.75;
			cursor: wait;
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

		.visitor-step {
			margin-top: 16px;
		}

		.visitor-card,
		.visitor-field,
		.visitor-textarea-wrap {
			background: #ffffff;
			border: 1px solid #d9dde4;
			border-radius: 12px;
			box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12);
			padding: 14px 16px;
			margin-bottom: 18px;
		}

		.visitor-card-title {
			display: block;
			font-size: 15px;
			font-weight: 600;
			color: #1f2937;
			margin-bottom: 12px;
		}

		.visitor-details-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 12px 14px;
		}

		.visitor-input-group {
			display: flex;
			flex-direction: column;
		}

		.visitor-label {
			font-size: 15px;
			color: #1f2937;
			margin-bottom: 10px;
			display: block;
		}

		.visitor-details-row {
			display: flex;
			gap: 14px;
			align-items: center;
		}

		.visitor-avatar {
			width: 78px;
			height: 78px;
			border-radius: 14px;
			background: #f3f4f6;
			border: 1px solid #d9dde4;
			display: grid;
			place-items: center;
			font-size: 48px;
			flex-shrink: 0;
		}

		.visitor-meta {
			display: grid;
			gap: 8px;
		}

		.visitor-meta-label {
			font-size: 13px;
			color: #374151;
			margin: 0;
		}

		.visitor-meta-value {
			font-size: 34px;
			line-height: 1;
			font-weight: 600;
			color: #111827;
			margin: 0;
		}

		.visitor-meta-value.id {
			font-size: 32px;
		}

		.visitor-select,
		.visitor-input,
		.visitor-textarea {
			width: 100%;
			border: 1px solid #bcc4cf;
			border-radius: 9px;
			background: #ffffff;
			padding: 10px 12px;
			font-size: 16px;
			color: #111827;
			outline: none;
		}

		.visitor-select:focus,
		.visitor-input:focus,
		.visitor-textarea:focus {
			border-color: #5c6bc0;
			box-shadow: 0 0 0 2px rgba(92, 107, 192, 0.12);
		}

		.visitor-textarea {
			min-height: 110px;
			resize: vertical;
		}

		.office-list {
			border: 1px solid #bcc4cf;
			border-radius: 9px;
			background: #ffffff;
			padding: 8px 10px;
			display: grid;
			gap: 2px;
		}

		.office-list-note {
			margin: 2px 0;
			font-size: 14px;
			color: #4b5563;
		}

		.office-option {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 15px;
			color: #1f2937;
			padding: 6px 4px;
			cursor: pointer;
		}

		.office-option input {
			width: 16px;
			height: 16px;
		}

		.visitor-submit {
			margin: 10px auto 0;
			width: min(100%, 420px);
			height: 52px;
			border: 0;
			border-radius: 10px;
			background: #3e4ba0;
			color: #ffffff;
			font-size: 16px;
			font-weight: 700;
			cursor: pointer;
			display: block;
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

		.loading-overlay {
			position: absolute;
			inset: 0;
			background: rgba(0, 0, 0, 0.85);
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 10px;
			z-index: 10;
		}

		.loading-overlay.is-hidden {
			display: none !important;
		}

		.loading-content {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 16px;
		}

		.spinner {
			width: 50px;
			height: 50px;
			border: 4px solid rgba(255, 255, 255, 0.2);
			border-top-color: #3e4ba0;
			border-radius: 50%;
			animation: spin 0.8s linear infinite;
		}

		@keyframes spin {
			to {
				transform: rotate(360deg);
			}
		}

		.loading-text {
			color: #ffffff;
			font-size: 16px;
			font-weight: 500;
			text-align: center;
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
				padding: 28px 12px;
				min-height: 260px;
			}

			.id-guide {
				width: min(94%, 340px);
			}

			.picture-guide {
				width: min(82%, 260px);
			}

			.scan-action {
				height: 50px;
				font-size: 16px;
			}

			.visitor-meta-value {
				font-size: 26px;
			}

			.visitor-meta-value.id {
				font-size: 24px;
			}

			.visitor-details-grid {
				grid-template-columns: 1fr;
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
							<p class="flow-step-name" id="flowStepName">Face + ID</p>
							<p class="flow-step-count" id="flowStepCount">Step 1 of 3</p>
						</div>
					</div>

					<div class="scanner-card">
						<div class="scanner-zone">
							<video id="cameraFeed" class="camera-feed" autoplay playsinline muted></video>
							<canvas id="frozenFrame" class="frozen-frame"></canvas>
							<div class="scanner-overlay" aria-hidden="true">
								<div class="picture-guide" id="pictureGuide">
									<span class="corner tl"></span>
									<span class="corner tr"></span>
									<span class="corner bl"></span>
									<span class="corner br"></span>
									<span class="face-oval"></span>
									<span class="shoulder-line"></span>
									<span class="id-holder"></span>
								</div>
								<div class="id-guide is-hidden" id="idGuide">
									<span class="corner tl"></span>
									<span class="corner tr"></span>
									<span class="corner bl"></span>
									<span class="corner br"></span>
									<div class="id-guide-layout">
										<span class="id-guide-photo"></span>
										<div class="id-guide-info">
											<span class="id-guide-line long"></span>
											<span class="id-guide-line medium"></span>
											<span class="id-guide-line short"></span>
											<span class="id-guide-line long"></span>
											<span class="id-guide-chip"></span>
										</div>
									</div>
								</div>
								<div class="loading-overlay is-hidden" id="loadingOverlay">
									<div class="loading-content">
										<div class="spinner"></div>
										<p class="loading-text" id="loadingText">Processing capture...</p>
									</div>
								</div>
							</div>
						</div>
						<p class="camera-status" id="cameraStatus">Starting camera...</p>
						<canvas id="captureCanvas" style="display:none;"></canvas>

							<button type="button" class="scan-action" id="scanAction">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M7 4v3M17 4v3M4 8h16M6 20h12a2 2 0 0 0 2-2V8H4v10a2 2 0 0 0 2 2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
								<rect x="9" y="11" width="6" height="5" rx="1" fill="currentColor"/>
							</svg>
								<span id="scanActionText">Capture Face + ID</span>
						</button>

						<div class="id-types is-hidden" id="idTypesPanel">
							<p class="id-types-title">Supported ID Types:</p>
							<ul class="id-types-list">
								<li>- National ID / Driver's License</li>
								<li>- Company ID / Employee Badge</li>
								<li>- Passport (Photo page)</li>
							</ul>
						</div>
					</div>

					<div class="visitor-step is-hidden" id="visitorStepPanel">
						<div class="visitor-card">
							<span class="visitor-card-title">Visitor Details</span>
							<div class="visitor-details-grid">
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorFirstName">First Name *</label>
									<input class="visitor-input" id="visitorFirstName" name="first_name" type="text" placeholder="First name">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorLastName">Last Name *</label>
									<input class="visitor-input" id="visitorLastName" name="last_name" type="text" placeholder="Last name">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorHouseNo">House No. *</label>
									<input class="visitor-input" id="visitorHouseNo" name="house_no" type="text" placeholder="House no.">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorStreet">Street *</label>
									<input class="visitor-input" id="visitorStreet" name="street" type="text" placeholder="Street">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorBarangay">Barangay *</label>
									<input class="visitor-input" id="visitorBarangay" name="barangay" type="text" placeholder="Barangay">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorCity">City / Municipality *</label>
									<input class="visitor-input" id="visitorCity" name="city_municipality" type="text" placeholder="City / municipality">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorProvince">Province *</label>
									<input class="visitor-input" id="visitorProvince" name="province" type="text" placeholder="Province">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorRegion">Region *</label>
									<input class="visitor-input" id="visitorRegion" name="region" type="text" placeholder="Region">
								</div>
							</div>
						</div>

						<div class="visitor-field">
							<label class="visitor-label" for="destinationOffice">Destination Office *</label>
							<div class="office-list" id="destinationOffice">
								<p class="office-list-note" id="officeListNote">Loading offices...</p>
							</div>
						</div>

						<div class="visitor-field">
							<label class="visitor-label" for="visitorPhone">Phone Number (Optional)</label>
							<input class="visitor-input" id="visitorPhone" type="text" placeholder="">
						</div>

						<div class="visitor-textarea-wrap">
							<label class="visitor-label" for="visitReason">Reason For Visit *</label>
							<textarea class="visitor-textarea" id="visitReason"></textarea>
						</div>

						<button type="button" class="visitor-submit" id="generateQrBtn">Generate QR Ticket</button>
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
		const flowStepName = document.getElementById('flowStepName');
		const flowStepCount = document.getElementById('flowStepCount');
		const scannerZone = document.querySelector('.scanner-zone');
		const cameraFeed = document.getElementById('cameraFeed');
		const pictureGuide = document.getElementById('pictureGuide');
		const idGuide = document.getElementById('idGuide');
		const cameraStatus = document.getElementById('cameraStatus');
		const captureCanvas = document.getElementById('captureCanvas');
		const frozenFrame = document.getElementById('frozenFrame');
		const scannerCard = document.querySelector('.scanner-card');
		const idTypesPanel = document.getElementById('idTypesPanel');
		const visitorStepPanel = document.getElementById('visitorStepPanel');
		const scanAction = document.getElementById('scanAction');
		const scanActionText = document.getElementById('scanActionText');
		const loadingOverlay = document.getElementById('loadingOverlay');
		const loadingText = document.getElementById('loadingText');
		const generateQrBtn = document.getElementById('generateQrBtn');
		const destinationOffice = document.getElementById('destinationOffice');
		const officeListNote = document.getElementById('officeListNote');
		let activeStream = null;
		let currentStep = 1;
		let capturedPictureData = '';
		let selectedOfficeIds = [];

		if (registerMenuGroup && registerMenuToggle) {
			registerMenuToggle.addEventListener('click', () => {
				const isOpen = registerMenuGroup.classList.toggle('open');
				registerMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		}

		const updateStepUI = () => {
			const isPictureStep = currentStep === 1;
			const isIdStep = currentStep === 2;
			const isVisitorInfoStep = currentStep === 3;

			flowStepName.textContent = isPictureStep ? 'Face + ID' : (isIdStep ? 'ID Scan' : 'Visitor Information');
			flowStepCount.textContent = isPictureStep ? 'Step 1 of 3' : (isIdStep ? 'Step 2 of 3' : 'Step 3 of 3');

			scannerCard.classList.toggle('is-hidden', isVisitorInfoStep);
			visitorStepPanel.classList.toggle('is-hidden', !isVisitorInfoStep);

			pictureGuide.classList.toggle('is-hidden', !isPictureStep);
			idGuide.classList.toggle('is-hidden', !isIdStep);
			idTypesPanel.classList.toggle('is-hidden', !isIdStep);
			scanActionText.textContent = isPictureStep ? 'Capture Face + ID' : 'Scan ID Card';
		};

		const setCameraState = (isOn, message) => {
			scannerZone.classList.toggle('camera-on', isOn);
			cameraStatus.textContent = message;
			if (!isOn) {
				scanActionText.textContent = 'Retry Camera';
			}
			scanAction.disabled = false;
		};

		const releaseCamera = () => {
			if (!activeStream) {
				return;
			}

			activeStream.getTracks().forEach((track) => track.stop());
			activeStream = null;
			cameraFeed.srcObject = null;
		};

		const startCamera = async () => {
			if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
				setCameraState(false, 'Camera access is not supported in this browser.');
				return;
			}

			scanAction.disabled = true;

			try {
				releaseCamera();

				const stream = await navigator.mediaDevices.getUserMedia({
					video: { facingMode: 'environment' },
					audio: false
				});

				activeStream = stream;
				cameraFeed.srcObject = stream;

				// Set scanner-zone aspect ratio based on camera stream dimensions
				cameraFeed.onloadedmetadata = () => {
					const width = cameraFeed.videoWidth;
					const height = cameraFeed.videoHeight;
					if (width && height) {
						scannerZone.style.aspectRatio = `${width} / ${height}`;
					}
				};

				setCameraState(true, currentStep === 1
					? 'Camera is ready. Center your face and hold your ID beside it.'
					: 'Camera is ready. Position the ID inside the frame.');
			} catch (error) {
				setCameraState(false, 'Camera permission denied or unavailable. Click Retry Camera after allowing access.');
			}
		};

		const capturePicture = () => {
			if (!cameraFeed.videoWidth || !cameraFeed.videoHeight) {
				cameraStatus.textContent = 'Waiting for camera feed. Try again in a second.';
				return;
			}

			captureCanvas.width = cameraFeed.videoWidth;
			captureCanvas.height = cameraFeed.videoHeight;
			const context = captureCanvas.getContext('2d');
			context.drawImage(cameraFeed, 0, 0, captureCanvas.width, captureCanvas.height);
			capturedPictureData = captureCanvas.toDataURL('image/jpeg', 0.92);

			// Freeze the frame by displaying it and stopping the video
			const frozenCtx = frozenFrame.getContext('2d');
			frozenFrame.width = cameraFeed.videoWidth;
			frozenFrame.height = cameraFeed.videoHeight;
			frozenCtx.drawImage(cameraFeed, 0, 0);
			frozenFrame.classList.add('visible');

			// Stop the video stream
			cameraFeed.pause();
			if (activeStream) {
				activeStream.getTracks().forEach(track => track.stop());
			}

			// Show loading overlay
			loadingOverlay.classList.remove('is-hidden');
			loadingText.textContent = 'Processing capture...';
			scanAction.disabled = true;

			// Send image to server for saving
			const formData = new FormData();
			formData.append('image', capturedPictureData);
			formData.append('step', 1);

			fetch('/guard/capture', {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
				},
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					loadingText.textContent = 'Capture saved! Moving to Step 2...';
					setTimeout(() => {
						currentStep = 2;
						updateStepUI();
						// Hide frozen frame when transitioning
						frozenFrame.classList.remove('visible');
						cameraStatus.textContent = 'Face + ID captured. Now align the ID card and continue.';
						
						// Restart camera for ID scan
						startCamera();
						
						// Hide loading overlay
						loadingOverlay.classList.add('is-hidden');
						scanAction.disabled = false;
					}, 1000);
				} else {
					loadingText.textContent = 'Failed to save. Try again.';
					setTimeout(() => {
						loadingOverlay.classList.add('is-hidden');
						frozenFrame.classList.remove('visible');
						scanAction.disabled = false;
						// Restart camera on failure
						startCamera();
					}, 2000);
				}
			})
			.catch(error => {
				console.error('Capture error:', error);
				loadingText.textContent = 'Error saving capture. Try again.';
				setTimeout(() => {
					loadingOverlay.classList.add('is-hidden');
					frozenFrame.classList.remove('visible');
					scanAction.disabled = false;
					// Restart camera on error
					startCamera();
				}, 2000);
			});
		};

		const captureIdAndProceed = () => {
			if (!cameraFeed.videoWidth || !cameraFeed.videoHeight) {
				cameraStatus.textContent = 'Waiting for camera feed. Try again in a second.';
				return;
			}

			captureCanvas.width = cameraFeed.videoWidth;
			captureCanvas.height = cameraFeed.videoHeight;
			const context = captureCanvas.getContext('2d');
			context.drawImage(cameraFeed, 0, 0, captureCanvas.width, captureCanvas.height);
			const capturedIdData = captureCanvas.toDataURL('image/jpeg', 0.92);

			loadingOverlay.classList.remove('is-hidden');
			loadingText.textContent = 'Saving ID scan...';
			scanAction.disabled = true;

			const formData = new FormData();
			formData.append('image', capturedIdData);
			formData.append('step', 2);

			fetch('/guard/capture', {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
				},
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (!data.success) {
					throw new Error(data.message || 'Failed to save ID scan');
				}

				releaseCamera();
				loadingOverlay.classList.add('is-hidden');
				scanAction.disabled = false;
				currentStep = 3;
				updateStepUI();
			})
			.catch(() => {
				loadingText.textContent = 'Failed to save ID scan. Try again.';
				setTimeout(() => {
					loadingOverlay.classList.add('is-hidden');
					scanAction.disabled = false;
				}, 1500);
			});
		};

		scanAction?.addEventListener('click', () => {
			if (!activeStream) {
				startCamera();
				return;
			}

			if (currentStep === 1) {
				capturePicture();
				return;
			}

			if (currentStep === 2) {
				captureIdAndProceed();
			}
		});

		generateQrBtn?.addEventListener('click', () => {
			alert('QR ticket generation will be connected to backend next.');
		});

		const renderOfficeList = (offices) => {
			if (!destinationOffice) {
				return;
			}

			destinationOffice.innerHTML = '';

			if (!Array.isArray(offices) || offices.length === 0) {
				const emptyState = document.createElement('p');
				emptyState.className = 'office-list-note';
				emptyState.textContent = 'No active offices found.';
				destinationOffice.appendChild(emptyState);
				return;
			}

			offices.forEach((office) => {
				const label = document.createElement('label');
				label.className = 'office-option';

				const checkbox = document.createElement('input');
				checkbox.type = 'checkbox';
				checkbox.className = 'office-checkbox';
				checkbox.value = String(office.office_id);

				checkbox.addEventListener('change', () => {
					selectedOfficeIds = Array.from(destinationOffice.querySelectorAll('.office-checkbox:checked'))
						.map((cb) => cb.value);
				});

				label.appendChild(checkbox);
				label.appendChild(document.createTextNode(office.office_name));
				destinationOffice.appendChild(label);
			});
		};

		const fetchOffices = async () => {
			if (officeListNote) {
				officeListNote.textContent = 'Loading offices...';
			}

			try {
				const response = await fetch('/guard/offices');
				const data = await response.json();

				if (!response.ok || !data.success) {
					throw new Error(data.message || 'Failed to load offices');
				}

				renderOfficeList(data.offices || []);
			} catch (error) {
				if (destinationOffice) {
					destinationOffice.innerHTML = '';
					const errorState = document.createElement('p');
					errorState.className = 'office-list-note';
					errorState.textContent = 'Unable to load offices right now.';
					destinationOffice.appendChild(errorState);
				}
			}
		};

		window.addEventListener('beforeunload', () => {
			releaseCamera();
		});

		updateStepUI();
		fetchOffices();
		startCamera();
	</script>
</body>
</html>
