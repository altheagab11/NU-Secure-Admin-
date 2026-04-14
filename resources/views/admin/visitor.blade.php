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
			height: 100vh;
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
			grid-template-columns: 1.2fr repeat(5, minmax(0, 1fr)) auto;
			gap: 10px;
			align-items: center;
		}

		.filters-label {
			margin: 0 0 8px;
			font-size: 18px;
			font-weight: 700;
			color: #111827;
		}

		.filter-input,
		.filter-select {
			height: 38px;
			border: 1px solid #d6dde8;
			outline: none;
			background: #f3f4f6;
			border-radius: 8px;
			padding: 0 12px;
			font-size: 14px;
			color: #1f2a44;
		}

		.filter-date {
			height: 38px;
			border: 1px solid #d6dde8;
			outline: none;
			background: #f3f4f6;
			border-radius: 8px;
			padding: 0 12px;
			font-size: 14px;
			color: #1f2a44;
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

		.summary-cards {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 12px;
			margin-top: 14px;
		}

		.summary-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 10px;
			padding: 12px 14px;
			min-height: 78px;
			display: flex;
			flex-direction: column;
			justify-content: center;
		}

		.summary-card-label {
			margin: 0;
			font-size: 12px;
			font-weight: 700;
			color: #64748b;
			letter-spacing: 0.02em;
			text-transform: uppercase;
		}

		.summary-card-value {
			margin: 4px 0 0;
			font-size: 28px;
			line-height: 1;
			font-weight: 800;
			color: #0f172a;
		}

		.summary-card.total .summary-card-value {
			color: #1d4ed8;
		}

		.summary-card.active .summary-card-value {
			color: #0369a1;
		}

		.summary-card.completed .summary-card-value {
			color: #15803d;
		}

		.summary-card.alerts .summary-card-value {
			color: #b91c1c;
		}

		.clear-filters-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			height: 38px;
			padding: 0 14px;
			border-radius: 8px;
			background: #eef2ff;
			color: #334155;
			border: 1px solid #d6deef;
			font-size: 13px;
			font-weight: 700;
			text-decoration: none;
			white-space: nowrap;
		}

		.clear-filters-btn:hover {
			background: #e2e8f0;
		}

		.clear-filters-btn.disabled {
			opacity: 0.5;
			pointer-events: none;
		}

		.table-card {
			margin-top: 22px;
			overflow-x: auto;
		}

		.visitor-table {
			width: 100%;
			border-collapse: collapse;
			min-width: 1450px;
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

		.status-completed {
			background: #e2e8f0;
			color: #334155;
		}

		.status-overstay {
			background: #fee2e2;
			color: #b91c1c;
		}

		.fetch-error {
			margin-top: 12px;
			padding: 10px 14px;
			border-radius: 8px;
			background: #fef2f2;
			border: 1px solid #fecaca;
			color: #b91c1c;
			font-size: 13px;
		}

		.empty-row {
			text-align: center;
			color: #64748b;
			font-size: 13px;
		}

		.pagination-wrap {
			display: flex;
			justify-content: flex-end;
			align-items: center;
			gap: 8px;
			padding: 12px 16px 16px;
		}

		.page-link {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-width: 34px;
			height: 34px;
			padding: 0 10px;
			border: 1px solid #dbe1ea;
			border-radius: 8px;
			background: #fff;
			color: #334155;
			font-size: 13px;
			font-weight: 600;
			text-decoration: none;
		}

		.page-link:hover {
			background: #f8fafc;
		}

		.page-link.active {
			background: #4256b4;
			color: #fff;
			border-color: #4256b4;
		}

		.page-link.disabled {
			opacity: 0.45;
			pointer-events: none;
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
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 12px;
			margin-top: 22px;
			align-items: stretch;
		}

		.right-stack {
			display: grid;
			grid-template-columns: 1fr;
			grid-template-rows: 1fr 1fr;
			gap: 12px;
			height: 100%;
			min-height: 360px;
		}

		.panel-card {
			overflow: hidden;
		}

		.recent-panel,
		.stack-panel {
			display: flex;
			flex-direction: column;
			height: 100%;
		}

		.recent-panel {
			min-height: 360px;
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

		.recent-panel .recent-list {
			flex: 1;
			display: flex;
			flex-direction: column;
		}

		.recent-item {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			padding: 14px 16px;
			border-bottom: 1px solid #d5d8de;
			flex: 0 0 auto;
			min-height: 74px;
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

		.correct-list {
			list-style: none;
			margin: 0;
			padding: 0;
			flex: 1;
			display: flex;
			flex-direction: column;
		}

		.correct-item {
			padding: 12px 16px;
			border-bottom: 1px solid #d5d8de;
			flex: 0 0 auto;
			min-height: 70px;
		}

		.correct-item:last-child {
			border-bottom: 0;
		}

		.correct-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 8px;
			font-size: 13px;
			color: #111827;
			font-weight: 600;
		}

		.correct-meta {
			margin-top: 4px;
			font-size: 12px;
			color: #4b5563;
		}

		.correct-pill {
			display: inline-flex;
			align-items: center;
			padding: 5px 8px;
			border-radius: 999px;
			background: #dcfce7;
			color: #15803d;
			font-size: 10px;
			font-weight: 700;
			letter-spacing: 0.02em;
		}

		@media (max-width: 1180px) {
			.filters-row {
				grid-template-columns: 1fr;
			}

			.summary-cards {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}

			.bottom-grid {
				grid-template-columns: 1fr;
			}

			.right-stack {
				grid-template-rows: auto;
				height: auto;
			}

			.recent-item,
			.correct-item {
				flex: initial;
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
			.summary-cards {
				grid-template-columns: 1fr;
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
			<h1 class="page-title">Visitor Monitoring</h1>
			@if (!empty($fetchError))
				<p class="fetch-error">{{ $fetchError }}</p>
			@endif

			<div class="filters-card">
				<p class="filters-label">Filters:</p>
				@php
					$hasActiveFilters = !empty($filters['search'])
						|| !empty($filters['status'])
						|| !empty($filters['office'])
						|| !empty($filters['visit_type'])
						|| !empty($filters['date_from'])
						|| !empty($filters['date_to']);
				@endphp
				<form method="GET" action="{{ route('admin.visitor') }}">
					<div class="filters-row">
						<div class="search-wrap">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2.5"/>
								<path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
							</svg>
							<input class="filter-input" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search Visitor" aria-label="Search visitor">
						</div>
						<select class="filter-select" name="status" aria-label="Filter by status" onchange="this.form.submit()">
							<option value="">Status</option>
							@foreach(($statusOptions ?? []) as $statusOption)
								<option value="{{ $statusOption }}" @selected(($filters['status'] ?? '') === $statusOption)>{{ $statusOption }}</option>
							@endforeach
						</select>
						<select class="filter-select" name="office" aria-label="Filter by office" onchange="this.form.submit()">
							<option value="">Office</option>
							@foreach(($officeOptions ?? []) as $officeOption)
								<option value="{{ $officeOption }}" @selected(($filters['office'] ?? '') === $officeOption)>{{ $officeOption }}</option>
							@endforeach
						</select>
						<select class="filter-select" name="visit_type" aria-label="Filter by visit type" onchange="this.form.submit()">
							<option value="">Visit Type</option>
							@foreach(($visitTypeOptions ?? []) as $visitTypeOption)
								<option value="{{ $visitTypeOption }}" @selected(($filters['visit_type'] ?? '') === $visitTypeOption)>{{ $visitTypeOption }}</option>
							@endforeach
						</select>
						<input class="filter-date" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" aria-label="Date from" onchange="this.form.submit()">
						<input class="filter-date" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" aria-label="Date to" onchange="this.form.submit()">
						<a href="{{ route('admin.visitor') }}" class="clear-filters-btn {{ $hasActiveFilters ? '' : 'disabled' }}" aria-label="Clear filters">Clear</a>
					</div>
				</form>
				<p class="filters-count">Showing {{ ($rows ?? collect())->count() }} of {{ $filteredCount ?? 0 }} filtered visitors ({{ $totalRows ?? 0 }} total)</p>
			</div>

			<div class="summary-cards" aria-label="Visitor summary cards">
				@foreach(($summaryCards ?? []) as $card)
					<div class="summary-card {{ $card['modifier'] ?? '' }}">
						<p class="summary-card-label">{{ $card['label'] ?? '' }}</p>
						<p class="summary-card-value">{{ number_format((int) ($card['value'] ?? 0)) }}</p>
					</div>
				@endforeach
			</div>

			<div class="table-card">
				<table class="visitor-table" aria-label="Visitors table">
					<thead>
						<tr>
							<th>Visitor</th>
							<th>Pass No.</th>
							<th>Control #</th>
							<th>Contact No.</th>
							<th>Visit Type</th>
							<th>Purpose</th>
							<th>Destination</th>
							<th>Time In</th>
							<th>Duration</th>
							<th>Status</th>
							<th>Alert</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						@forelse(($rows ?? []) as $row)
							<tr>
								<td>
									<div class="visitor-cell">
										<svg class="avatar-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
											<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z"/>
										</svg>
										<div>
											<div class="visitor-name">{{ $row['visitor_name'] }}</div>
											<div class="visitor-id">{{ $row['pass_number'] }}</div>
										</div>
									</div>
								</td>
								<td>{{ $row['pass_number'] }}</td>
								<td>{{ $row['control_number'] }}</td>
								<td>{{ $row['contact_no'] }}</td>
								<td>{{ $row['visit_type'] }}</td>
								<td>{{ $row['purpose'] }}</td>
								<td>{{ $row['destination'] }}</td>
								<td>{{ $row['entry_time_label_date'] }}<br>{{ $row['entry_time_label_time'] }}</td>
								<td>{{ $row['duration_label'] }}</td>
								<td><span class="status-pill {{ $row['status_class'] }}">{{ $row['status'] }}</span></td>
								<td>{{ $row['alert'] }}</td>
								<td>
									<a href="#" class="action-link" aria-disabled="true">
										<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z" stroke="currentColor" stroke-width="2"/>
											<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
										</svg>
										View
									</a>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="12" class="empty-row">No visitor records found.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(($rows ?? null) && method_exists($rows, 'lastPage') && $rows->lastPage() > 1)
					<div class="pagination-wrap" aria-label="Visitor table pagination">
						<a class="page-link {{ $rows->onFirstPage() ? 'disabled' : '' }}" href="{{ $rows->onFirstPage() ? '#' : $rows->previousPageUrl() }}">Prev</a>

						@for($page = 1; $page <= $rows->lastPage(); $page++)
							<a class="page-link {{ $rows->currentPage() === $page ? 'active' : '' }}" href="{{ $rows->url($page) }}">{{ $page }}</a>
						@endfor

						<a class="page-link {{ $rows->hasMorePages() ? '' : 'disabled' }}" href="{{ $rows->hasMorePages() ? $rows->nextPageUrl() : '#' }}">Next</a>
					</div>
				@endif
			</div>

			<div class="bottom-grid">
				<section class="panel-card recent-panel">
					<h2 class="panel-title">Recent Visitors</h2>
					<ul class="recent-list">
						@forelse(($recentVisitors ?? []) as $recent)
							<li class="recent-item">
								<div class="recent-left">
									<svg class="avatar-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z"/>
									</svg>
									<div>
										<div>{{ $recent['visitor_name'] }}</div>
										<div class="recent-meta">{{ $recent['destination'] }} • {{ $recent['time_label'] }}</div>
									</div>
								</div>
								<span class="status-pill {{ $recent['status_class'] }}">{{ $recent['status'] }}</span>
							</li>
						@empty
							<li class="recent-item">
								<div class="recent-left">
									<div class="recent-meta">No recent visitor data.</div>
								</div>
							</li>
						@endforelse
					</ul>
				</section>

				<div class="right-stack">
					<section class="panel-card stack-panel">
						<h2 class="panel-title">Active by Office</h2>
						<ul class="office-list">
							@forelse(($activeByOffice ?? []) as $officeActivity)
								<li class="office-item">
									<div class="office-row">
										<span class="office-label">
											<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M7 21h10M9 21V8h6v13M8 8h8M8 4h8v4H8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
											</svg>
											{{ $officeActivity['office_name'] }}
										</span>
										<span>{{ $officeActivity['count'] }}</span>
									</div>
									<div class="bar-track">
										@php
											$barWidth = (int) round((($officeActivity['count'] ?? 0) / max(1, ($maxOfficeCount ?? 1))) * 100);
										@endphp
										<div class="bar-fill" style="width: {{ max(8, $barWidth) }}%;"></div>
									</div>
								</li>
							@empty
								<li class="office-item">
									<div class="office-row">
										<span class="office-label">No active office data</span>
									</div>
								</li>
							@endforelse
						</ul>
					</section>

					<section class="panel-card stack-panel">
						<h2 class="panel-title">Correct Office Scans</h2>
						<ul class="correct-list" aria-label="Correct office scan results">
							@forelse(($correctOfficeScans ?? []) as $scan)
								<li class="correct-item">
									<div class="correct-head">
										<span>{{ $scan['visitor_name'] }}</span>
										<span class="correct-pill">{{ $scan['result'] }}</span>
									</div>
									<p class="correct-meta">{{ $scan['destination'] }} • {{ $scan['control_number'] }} • {{ $scan['time_label'] }}</p>
								</li>
							@empty
								<li class="correct-item">
									<p class="correct-meta">No matched office scans yet.</p>
								</li>
							@endforelse
						</ul>
					</section>
				</div>
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
