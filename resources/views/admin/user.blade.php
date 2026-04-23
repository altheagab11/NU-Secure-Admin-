<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	@php
		$activeSection = $section ?? 'overview';
	@endphp
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $activeSection === 'guards' ? 'Guards' : ($activeSection === 'offices' ? 'Offices' : 'User Management') }}</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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

		* { box-sizing: border-box; }

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
			width: 260px;
			min-height: 100vh;
			background: linear-gradient(180deg, #243c96 0%, #2d3fa3 45%, #3146b4 100%);
			color: #fff;
			padding: 18px 14px;
			box-shadow: 4px 0 20px rgba(0, 0, 0, 0.12);
			position: fixed;
			top: 0;
			left: 0;
			bottom: 0;
			height: 100vh;
			overflow-y: auto;
			z-index: 1000;
		}

		.sidebar::-webkit-scrollbar {
			width: 6px;
		}

		.sidebar::-webkit-scrollbar-thumb {
			background: rgba(255, 255, 255, 0.18);
			border-radius: 10px;
		}

		.sidebar-brand {
			gap: 12px;
			padding: 10px 10px 18px;
			margin-bottom: 10px;
			border-bottom: 1px solid rgba(255, 255, 255, 0.12);
		}

		.brand-icon {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			background: rgba(255, 255, 255, 0.14);
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 22px;
			flex-shrink: 0;
		}

		.brand-title {
			font-size: 28px;
			font-weight: 800;
			color: #ffd84d;
			line-height: 1;
		}

		.brand-subtitle {
			color: rgba(255, 255, 255, 0.78);
			font-size: 12px;
			display: block;
			margin-top: 2px;
		}

		.sidebar-section {
			margin-top: 18px;
		}

		.sidebar-label {
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 1px;
			color: rgba(255, 255, 255, 0.55);
			margin: 0 0 8px 10px;
			text-transform: uppercase;
		}

		.sidebar-link {
			width: 100%;
			display: flex;
			align-items: center;
			gap: 12px;
			color: #fff;
			text-decoration: none;
			padding: 12px 14px;
			border-radius: 12px;
			margin-bottom: 6px;
			position: relative;
			transition: all 0.25s ease;
			font-weight: 500;
			border: none;
			background: transparent;
		}

		.sidebar-link:hover {
			background: rgba(255, 255, 255, 0.10);
			color: #fff;
			transform: translateX(4px);
		}

		.sidebar-link.active {
			background: linear-gradient(90deg, #4f62ff, #6678ff);
			color: #fff;
			box-shadow: 0 8px 20px rgba(46, 78, 255, 0.28);
		}

		.sidebar-link.active::before {
			content: "";
			position: absolute;
			left: -14px;
			top: 8px;
			bottom: 8px;
			width: 4px;
			border-radius: 10px;
			background: #ffd84d;
		}

		.sidebar-icon {
			width: 20px;
			text-align: center;
			font-size: 18px;
			flex-shrink: 0;
		}

		.sidebar-text {
			flex: 1;
			text-align: left;
		}

		.sidebar-badge {
			background: #ff4d4f;
			color: #fff;
			font-size: 11px;
			font-weight: 700;
			padding: 3px 8px;
			border-radius: 50px;
			min-width: 22px;
			text-align: center;
		}

		.sidebar-toggle {
			justify-content: space-between;
			cursor: pointer;
		}

		.dropdown-arrow {
			transition: transform 0.25s ease;
			font-size: 13px;
		}

		.sidebar-dropdown.open .dropdown-arrow,
		.sidebar-toggle[aria-expanded="true"] .dropdown-arrow {
			transform: rotate(180deg);
		}

		.submenu {
			display: none;
			margin: 6px 0 8px 14px;
			padding-left: 14px;
			border-left: 1px solid rgba(255, 255, 255, 0.15);
		}

		.sidebar-dropdown.open .submenu {
			display: block;
		}

		.submenu-link {
			display: flex;
			align-items: center;
			gap: 10px;
			color: rgba(255, 255, 255, 0.88);
			text-decoration: none;
			padding: 10px 12px;
			border-radius: 10px;
			margin-bottom: 5px;
			font-size: 14px;
			transition: all 0.2s ease;
		}

		.submenu-link:hover {
			background: rgba(255, 255, 255, 0.10);
			color: #fff;
			transform: translateX(3px);
		}

		.submenu-link.active {
			background: rgba(255, 255, 255, 0.16);
			color: #ffd84d;
			font-weight: 600;
		}

		.sidebar-footer {
			padding-top: 16px;
			margin-top: 20px;
			border-top: 1px solid rgba(255, 255, 255, 0.12);
		}

		.admin-card {
			display: flex;
			align-items: center;
			gap: 12px;
			background: rgba(255, 255, 255, 0.08);
			border-radius: 14px;
			padding: 12px;
			margin-bottom: 12px;
		}

		.admin-avatar {
			width: 42px;
			height: 42px;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.15);
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 22px;
			flex-shrink: 0;
		}

		.admin-info h6 {
			font-size: 15px;
			font-weight: 700;
			color: #fff;
		}

		.admin-info small {
			color: rgba(255, 255, 255, 0.72);
		}

		.logout-btn {
			width: 100%;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			background: #fff;
			color: #ff3b30;
			text-decoration: none;
			padding: 11px 14px;
			border-radius: 12px;
			font-weight: 700;
			transition: all 0.25s ease;
		}

		.logout-btn:hover {
			background: #ffe9e9;
			color: #ff3b30;
			transform: translateY(-1px);
		}

		.main {
			flex: 1;
			background: #f7f8ff;
			padding: 24px 32px;
			margin-left: 260px;
			min-height: 100vh;
		}

		.page-title {
			margin: 0;
			font-size: 28px;
			font-weight: 700;
			color: #0f172a;
		}

		.page-subtitle {
			margin: 8px 0 0;
			color: #64748b;
			font-size: 15px;
		}

		.header-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 12px;
		}

		.add-guard-btn {
			border: 0;
			background: #39459a;
			color: #f4f6ff;
			font-size: 14px;
			font-weight: 600;
			padding: 8px 12px;
			border-radius: 6px;
			display: inline-flex;
			align-items: center;
			gap: 6px;
			cursor: pointer;
		}

		.add-guard-btn svg {
			width: 16px;
			height: 16px;
		}

		.guard-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
			padding: 14px 0 0;
			overflow: hidden;
		}

		.guard-card-head {
			padding: 0 16px 10px;
		}

		.guard-title {
			margin: 0;
			font-size: 20px;
			font-weight: 600;
			color: #1f2937;
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.guard-title svg {
			width: 18px;
			height: 18px;
			color: #4b5cd1;
		}

		.guard-total {
			margin: 8px 0 0;
			font-size: 14px;
			color: #475569;
		}

		.guard-table {
			width: 100%;
			border-collapse: collapse;
		}

		.guard-table th,
		.guard-table td {
			padding: 10px 16px;
			text-align: left;
			font-size: 13px;
		}

		.guard-table th {
			font-weight: 500;
			color: #334155;
			border-bottom: 1px solid #e5e7eb;
		}

		.guard-table td {
			color: #111827;
			border-bottom: 1px solid #edf0f4;
		}

		.guard-table tbody tr:last-child td {
			border-bottom: 0;
		}

		.pagination-wrap {
			display: flex;
			justify-content: flex-end;
			align-items: center;
			gap: 8px;
			padding: 12px 16px 16px;
		}

		/* Filters card (copied-simplified from visitor view) */
		.filters-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			padding: 12px 14px;
			margin-bottom: 12px;
			display: flex;
			gap: 10px;
			align-items: center;
		}

		.filter-input, .filter-select {
			padding: 8px 10px;
			border-radius: 8px;
			border: 1px solid #e6edf7;
			font-size: 14px;
			min-width: 160px;
		}

		.search-wrap { display:flex; align-items:center; gap:8px; }

		.clear-filters-btn { color:#64748b; text-decoration:none; padding:6px 10px; border-radius:8px; border:1px solid transparent; }


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

		.email-cell {
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		.email-cell svg {
			width: 14px;
			height: 14px;
			color: #111827;
		}

		.badge-pill {
			display: inline-block;
			padding: 2px 8px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 600;
			color: #0369a1;
			background: #dbeafe;
		}

		.action-icons {
			display: inline-flex;
			align-items: center;
			gap: 10px;
		}

		.action-icons svg {
			width: 14px;
			height: 14px;
		}

		.action-edit {
			color: #4b5563;
		}

		.action-delete {
			color: #ef4444;
		}

		.icon-btn {
			border: 0;
			background: transparent;
			padding: 0;
			line-height: 0;
			cursor: pointer;
		}

		.office-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
			padding: 14px 0 0;
			overflow: hidden;
		}

		.office-card-head {
			padding: 0 16px 10px;
		}

		.office-title {
			margin: 0;
			font-size: 20px;
			font-weight: 600;
			color: #1f2937;
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.office-title svg {
			width: 18px;
			height: 18px;
			color: #4b5cd1;
		}

		.office-total {
			margin: 8px 0 0;
			font-size: 14px;
			color: #475569;
		}

		.office-table {
			width: 100%;
			border-collapse: collapse;
		}

		.office-table th,
		.office-table td {
			padding: 10px 16px;
			text-align: left;
			font-size: 13px;
		}

		.office-table th {
			font-weight: 500;
			color: #334155;
			border-bottom: 1px solid #e5e7eb;
		}

		.office-table td {
			color: #111827;
			border-bottom: 1px solid #edf0f4;
		}

		.office-table tbody tr:last-child td {
			border-bottom: 0;
		}

		.office-summary-grid {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 14px;
			margin-top: 18px;
		}

		.office-summary-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
			padding: 14px;
		}

		.office-summary-name {
			margin: 0;
			font-size: 14px;
			color: #1f2937;
		}

		.office-summary-count {
			margin: 10px 0 2px;
			font-size: 30px;
			line-height: 1;
			font-weight: 500;
			color: #111827;
		}

		.office-summary-label {
			font-size: 13px;
			color: #374151;
		}

		@media (max-width: 1200px) {
			.office-summary-grid {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
		}

		@media (max-width: 700px) {
			.header-row {
				flex-direction: column;
				align-items: flex-start;
			}

			.office-summary-grid {
				grid-template-columns: 1fr;
			}

			.sidebar {
				width: 220px;
				padding: 10px 8px;
			}

			.main {
				margin-left: 220px;
			}

			.brand-title {
				font-size: 18px;
			}
		}
	</style>
</head>
<body>
	<div class="layout">
		<aside class="sidebar d-flex flex-column justify-content-between">
			<div>
				<div class="sidebar-brand d-flex align-items-center">
					<div class="brand-icon">
						<i class="bi bi-person-badge-fill"></i>
					</div>
					<div>
						<h4 class="brand-title mb-0">SVMS Admin</h4>
						<small class="brand-subtitle">Smart Visitor Monitoring System</small>
					</div>
				</div>

				<div class="sidebar-section">
					<p class="sidebar-label">MAIN</p>
					<a href="/admin/dashboard" class="sidebar-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-grid-1x2-fill"></i></span>
						<span class="sidebar-text">Dashboard</span>
					</a>
				</div>

				<div class="sidebar-section">
					<p class="sidebar-label">MONITORING</p>
					<a href="/admin/visitor" class="sidebar-link {{ request()->is('admin/visitor*') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-people-fill"></i></span>
						<span class="sidebar-text">Visitor Monitoring</span>
					</a>
					<a href="/admin/alerts" class="sidebar-link {{ request()->is('admin/alerts*') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-exclamation-triangle-fill"></i></span>
						<span class="sidebar-text">Alerts</span>
						<span class="sidebar-badge">5</span>
					</a>
				</div>

				@php
					$isUserMgmtOpen = request()->is('admin/user*');
				@endphp
				<div class="sidebar-section">
					<p class="sidebar-label">MANAGEMENT</p>
					<div class="sidebar-dropdown {{ $isUserMgmtOpen ? 'open' : '' }}" id="userMenuGroup">
						<button class="sidebar-link sidebar-toggle {{ $isUserMgmtOpen ? 'active' : '' }}"
							type="button"
							id="userMenuToggle"
							aria-expanded="{{ $isUserMgmtOpen ? 'true' : 'false' }}">
							<span class="d-flex align-items-center gap-2">
								<span class="sidebar-icon"><i class="bi bi-person-lines-fill"></i></span>
								<span class="sidebar-text">User Management</span>
							</span>
							<span class="dropdown-arrow"><i class="bi bi-chevron-down"></i></span>
						</button>
						<div class="submenu" id="userSubmenu">
							<a href="/admin/user/guards" class="submenu-link {{ $activeSection === 'guards' ? 'active' : '' }}">
								<i class="bi bi-shield-fill-check"></i>
								<span>Guards</span>
							</a>
							<a href="/admin/user/offices" class="submenu-link {{ $activeSection === 'offices' ? 'active' : '' }}">
								<i class="bi bi-building"></i>
								<span>Offices</span>
							</a>
						</div>
					</div>
				</div>
			</div>

			<div class="sidebar-footer">
				<div class="admin-card">
					<div class="admin-avatar">
						<i class="bi bi-person-circle"></i>
					</div>
					@php
						$sidebarAuthUser = auth()->user();
						$sidebarDisplayName = trim(((string) ($sidebarAuthUser->first_name ?? '')).' '.((string) ($sidebarAuthUser->last_name ?? '')));
						$sidebarDisplayName = $sidebarDisplayName !== ''
							? $sidebarDisplayName
							: ((string) ($sidebarAuthUser->name ?? $sidebarAuthUser->email ?? 'User'));
						$sidebarRoleLabel = ((int) ($sidebarAuthUser->role_id ?? 0) === 4) ? 'Guard' : 'System Administrator';
					@endphp
					<div class="admin-info">
						<h6 class="mb-0">{{ $sidebarDisplayName }}</h6>
						<small>{{ $sidebarRoleLabel }}</small>
					</div>
				</div>

				<a href="{{ route('logout') }}" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
					<i class="bi bi-box-arrow-right"></i>
					<span>Logout</span>
				</a>

				<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
					@csrf
				</form>
			</div>
		</aside>

		<main class="main">
			@if ($activeSection === 'guards')
				<div class="header-row">
					<h1 class="page-title">Guard Management</h1>
					<div style="display:flex;gap:8px;align-items:center;">
						<button type="button" id="openGuardRecycleBinBtn" class="add-guard-btn" style="background:#334155;">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3 6h18M8 6V4h8v2M7 6l1 14h8l1-14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							Recycle Bin ({{ isset($recycledGuards) ? count($recycledGuards) : 0 }})
						</button>
						<button type="button" id="openAddGuardBtn" class="add-guard-btn">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							Add Guard
						</button>
					</div>
				</div>

				@if(session('success'))
					<div id="guardSuccessAlert" style="margin:12px 0;padding:10px;border-radius:8px;background:#ecfdf5;color:#065f46;">{{ session('success') }}</div>
					<script>
						setTimeout(function(){
							var el = document.getElementById('guardSuccessAlert');
							if (el) {
								el.style.transition = 'opacity 0.4s ease';
								el.style.opacity = '0';
								setTimeout(function(){ el.remove(); }, 400);
							}
						}, 5000);
					</script>
				@endif

				@if($errors->any())
					<div style="margin:12px 0;padding:10px;border-radius:8px;background:#fff1f2;color:#9f1239;">
						<ul style="margin:0;padding-left:18px;">
							@foreach($errors->all() as $err)
								<li>{{ $err }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<section class="guard-card">
					<div class="guard-card-head">
						<h2 class="guard-title">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 3 5 6v6c0 5.2 3.4 8.6 7 9.9 3.6-1.3 7-4.7 7-9.9V6l-7-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							Guard Accounts
						</h2>
						<p class="guard-total">Total Guards: {{ (isset($guards) && method_exists($guards, 'total')) ? $guards->total() : count($guards ?? []) }}</p>
					</div>

					<table class="guard-table" aria-label="Guard accounts table">
						<thead>
							<tr>
								<th>Name</th>
								<th>Email</th>
								<th>Badge Number</th>
								<th>Station</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							@forelse($guards ?? [] as $guard)
								<tr>
									<td>{{ $guard->name }}</td>
									<td>
										<span class="email-cell">
											<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
												<path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											{{ $guard->email }}
										</span>
									</td>
									<td><span class="badge-pill">{{ $guard->badge_number ?? $guard->badge ?? '' }}</span></td>
									<td>{{ $guard->station ?? '—' }}</td>
									<td>
										<span class="action-icons">
											<img src="{{ asset('picture/bx_edit.png') }}" alt="Edit" class="action-edit" style="width:14px;height:14px;" />
											<form method="POST" action="/admin/user/guards/{{ $guard->user_id ?? '' }}" style="display:inline;" onsubmit="return confirm('Move this guard account to recycle bin?');">
												@csrf
												@method('DELETE')
												<button type="submit" class="icon-btn" aria-label="Delete">
													<img src="{{ asset('picture/Vector.png') }}" alt="Delete" class="action-delete" style="width:14px;height:14px;" />
												</button>
											</form>
										</span>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" style="text-align:center;color:#64748b;padding:18px;">No guards found.</td>
								</tr>
							@endforelse
						</tbody>
					</table>

					@if(isset($guards) && method_exists($guards, 'lastPage') && $guards->lastPage() > 1)
						<div class="pagination-wrap" aria-label="Guard table pagination">
							<a class="page-link {{ $guards->onFirstPage() ? 'disabled' : '' }}" href="{{ $guards->onFirstPage() ? '#' : $guards->previousPageUrl() }}">Prev</a>

							@for($page = 1; $page <= $guards->lastPage(); $page++)
								<a class="page-link {{ $guards->currentPage() === $page ? 'active' : '' }}" href="{{ $guards->url($page) }}">{{ $page }}</a>
							@endfor

							<a class="page-link {{ $guards->hasMorePages() ? '' : 'disabled' }}" href="{{ $guards->hasMorePages() ? $guards->nextPageUrl() : '#' }}">Next</a>
						</div>
					@endif
				</section>
			@elseif ($activeSection === 'offices')
				<div class="header-row">
					<h1 class="page-title">Office User Management</h1>
					<div style="display:flex;gap:8px;align-items:center;">
						<button type="button" id="openRecycleBinBtn" class="add-guard-btn" style="background:#334155;">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3 6h18M8 6V4h8v2M7 6l1 14h8l1-14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							Recycle Bin ({{ isset($recycledOffices) ? count($recycledOffices) : 0 }})
						</button>
						<button type="button" id="openAddOfficeBtn" class="add-guard-btn" onclick="document.getElementById('addOfficeModal') && (document.getElementById('addOfficeModal').style.display = 'flex')">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								<circle cx="10" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
								<path d="M22 8h-6M19 5v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							Add Office User
						</button>
					</div>
				</div>

				{{-- Filters for offices (placed between page title and Office User Accounts) --}}
				<form method="GET" action="" style="margin:12px 0 18px;">
					<div class="filters-card" role="search" aria-label="Office filters">
						<div class="search-wrap">
							<input class="filter-input" type="text" name="search" value="{{ request('search', '') }}" placeholder="Search name or email" aria-label="Search users">
						</div>

						<select name="office" class="filter-select" aria-label="Filter by office" onchange="this.form.submit()">
							<option value="">All offices</option>
							@foreach($officeOptions ?? [] as $opt)
								<option value="{{ $opt->office_id ?? $opt->id }}" @selected(request('office') == ($opt->office_id ?? $opt->id))>{{ $opt->office_name ?? $opt->name }}</option>
							@endforeach
						</select>

						<select name="position" class="filter-select" aria-label="Filter by position" onchange="this.form.submit()">
							<option value="">All positions</option>
							@foreach($positions ?? [] as $pos)
								@if(!empty($pos))
									<option value="{{ $pos }}" @selected(request('position') === (string) $pos)>{{ $pos }}</option>
								@endif
							@endforeach
						</select>

						<div style="margin-left:auto;display:flex;gap:8px;align-items:center;">
							<button type="submit" class="add-guard-btn" style="background:#39459a;padding:8px 12px;">Apply</button>
							<a href="/admin/user/offices" class="clear-filters-btn">Clear</a>
						</div>
					</div>
				</form>

				@if(session('success'))
					<div id="officeSuccessAlert" style="margin:12px 0;padding:10px;border-radius:8px;background:#ecfdf5;color:#065f46;">{{ session('success') }}</div>
					<script>
						setTimeout(function(){
							var el = document.getElementById('officeSuccessAlert');
							if (el) {
								el.style.transition = 'opacity 0.4s ease';
								el.style.opacity = '0';
								setTimeout(function(){ el.remove(); }, 400);
							}
						}, 5000);
					</script>
				@endif

				@if($errors->any())
					<div id="officeErrorAlert" style="margin:12px 0;padding:10px;border-radius:8px;background:#fff1f2;color:#9f1239;">
						<ul style="margin:0;padding-left:18px;">
							@foreach($errors->all() as $err)
								<li>{{ $err }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<section class="office-card">
					<div class="office-card-head">
						<h2 class="office-title">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M4 21h16M7 21V6h10v15M10 9h1M13 9h1M10 12h1M13 12h1M10 15h1M13 15h1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							Office User Accounts
						</h2>
						<p class="office-total">Total Users: {{ isset($totalUsers) ? $totalUsers : count($offices ?? []) }} across {{ isset($totalOffices) ? $totalOffices : count($officeOptions ?? []) }} offices</p>
					</div>

					<table class="office-table" aria-label="Office user accounts table">
						<thead>
							<tr>
								<th>Name</th>
								<th>Email</th>
								<th>Office</th>
								<th>Position</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							@forelse($offices ?? [] as $office)
								<tr>
									<td>{{ $office->name ?? ($office->first_name ? ($office->first_name . ' ' . ($office->last_name ?? '')) : ($office->full_name ?? '')) }}</td>
									<td>
										<span class="email-cell">
											<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
												<path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											{{ $office->email ?? '' }}
										</span>
									</td>
									<td><span class="badge-pill">{{ $office->office_name ?? $office->office ?? '—' }}</span></td>
									<td>{{ $office->position ?? '—' }}</td>
									<td>
										<span class="action-icons">
											<img src="{{ asset('picture/bx_edit.png') }}" alt="Edit" class="action-edit edit-office-btn" style="width:14px;height:14px;cursor:pointer;" 
												 data-user-id="{{ $office->user_id ?? '' }}"
												 data-name="{{ $office->name ?? '' }}"
												 data-email="{{ $office->email ?? '' }}"
												 data-office-id="{{ $office->office_id ?? '' }}"
												 data-position="{{ $office->position ?? '' }}"
											/>
											<form method="POST" action="/admin/user/offices/{{ $office->user_id ?? '' }}" style="display:inline;" onsubmit="return confirm('Move this office user to recycle bin?');">
												@csrf
												@method('DELETE')
												<button type="submit" class="icon-btn" aria-label="Delete">
													<img src="{{ asset('picture/Vector.png') }}" alt="Delete" class="action-delete" style="width:14px;height:14px;" />
												</button>
											</form>
										</span>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" style="text-align:center;color:#64748b;padding:18px;">No users found.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</section>

				@if(isset($offices) && method_exists($offices, 'lastPage') && $offices->lastPage() > 1)
					<div class="pagination-wrap" aria-label="Office table pagination">
						<a class="page-link {{ $offices->onFirstPage() ? 'disabled' : '' }}" href="{{ $offices->onFirstPage() ? '#' : $offices->previousPageUrl() }}">Prev</a>

						@for($page = 1; $page <= $offices->lastPage(); $page++)
							<a class="page-link {{ $offices->currentPage() === $page ? 'active' : '' }}" href="{{ $offices->url($page) }}">{{ $page }}</a>
						@endfor

						<a class="page-link {{ $offices->hasMorePages() ? '' : 'disabled' }}" href="{{ $offices->hasMorePages() ? $offices->nextPageUrl() : '#' }}">Next</a>
					</div>
				@endif

				<div class="office-summary-grid">
					@php
						// Prefer pre-computed $officeSummaries from controller (total counts across all users).
						if (isset($officeSummaries)) {
							$officeSummaries = collect($officeSummaries);
						} else {
							// Fallback: build office summary from current $offices (may be paginated - this will reflect current page only)
							$collectionForSummary = (isset($offices) && method_exists($offices, 'items')) ? collect($offices->items()) : collect($offices ?? []);
							$officeSummaries = $collectionForSummary->groupBy(function($item) {
								return data_get($item, 'office_name') ?? data_get($item, 'office') ?? 'Unknown Office';
							})->map(function($group, $name) {
								return ['name' => $name, 'count' => count($group)];
							})->values();
						}
					@endphp

					@forelse($officeSummaries as $os)
						<div class="office-summary-card">
							<p class="office-summary-name">{{ $os['name'] }}</p>
							<p class="office-summary-count">{{ $os['count'] }}</p>
							<span class="office-summary-label">Active users</span>
						</div>
					@empty
						<div class="office-summary-card">
							<p class="office-summary-name">No offices</p>
							<p class="office-summary-count">0</p>
							<span class="office-summary-label">Active users</span>
						</div>
					@endforelse
				</div>

			@else
				<h1 class="page-title">
					User Management
				</h1>
				<p class="page-subtitle">
					Manage user accounts from this section.
				</p>
			@endif


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

		// Modal open/close and form handling for Add Guard
		(function() {
			const addBtn = document.getElementById('openAddGuardBtn');

			function getModal() { return document.getElementById('addGuardModal'); }
			function getCloseBtn() { const m = getModal(); return m ? m.querySelector('#closeAddGuard') : null; }
			function getCancelBtn() { const m = getModal(); return m ? m.querySelector('#cancelAddGuard') : null; }
			function getForm() { return document.getElementById('addGuardForm'); }
			function getHiddenFullName() { return document.getElementById('fullNameHidden'); }

			function showModal() {
				const modal = getModal();
				if (!modal) return;
				modal.style.display = 'flex';
				const first = modal.querySelector('input[name="first_name"]');
				if (first) first.focus();
				// attach backdrop close listener if not already attached
				if (!modal._backdropAttached) {
					modal.addEventListener('click', (e) => {
						if (e.target === modal) hideModal();
					});
					modal._backdropAttached = true;
				}
			}

			function hideModal() {
				const modal = getModal();
				if (!modal) return;
				modal.style.display = 'none';
			}

			if (addBtn) addBtn.addEventListener('click', showModal);
			// close and cancel may be added later when modal exists; delegate via event listener on document
			document.addEventListener('click', (e) => {
				if (e.target && e.target.id === 'closeAddGuard') hideModal();
				if (e.target && e.target.id === 'cancelAddGuard') hideModal();
			});

			// on submit, combine first and last into hidden `name` so backend still receives `name`
			document.addEventListener('submit', (e) => {
				if (e.target && e.target.id === 'addGuardForm') {
					const form = e.target;
					const fn = form.querySelector('input[name="first_name"]')?.value?.trim() || '';
					const ln = form.querySelector('input[name="last_name"]')?.value?.trim() || '';
					const hiddenFullName = getHiddenFullName();
					if (hiddenFullName) hiddenFullName.value = (fn + (fn && ln ? ' ' : '') + ln).trim();
					// allow submit to proceed
				}
			});
		})();
	</script>

	<!-- Add Guard Modal -->
	<div id="addGuardModal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.6); align-items:center; justify-content:center; z-index:80;">
		<div role="dialog" aria-modal="true" aria-labelledby="addGuardTitle" style="background:#fff;border-radius:10px; width:520px; max-width:94%; padding:20px; box-shadow:0 10px 30px rgba(2,6,23,0.35);">
			<div style="display:flex; justify-content:space-between; align-items:center;">
				<h3 id="addGuardTitle" style="margin:0; font-size:18px;">Add Guard Account</h3>
				<button id="closeAddGuard" aria-label="Close" style="border:0;background:transparent;font-size:22px;cursor:pointer;line-height:1;color:#374151;">&times;</button>
			</div>


			@if($errors->any())
				<div style="margin:12px 0;padding:10px;border-radius:8px;background:#fff1f2;color:#9f1239;">
					<ul style="margin:0;padding-left:18px;">
						@foreach($errors->all() as $err)
							<li>{{ $err }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			<p style="color:#6b7280;margin:8px 0 14px;">Create a new security guard account for the system.</p>
			<form id="addGuardForm" method="POST" action="/admin/user/guards">
				@csrf
				<div style="display:flex;gap:10px;">
					<div style="flex:1;">
						<label style="font-size:13px;color:#334155;">First Name</label>
						<input name="first_name" type="text" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;margin-top:6px;">
					</div>
					<div style="flex:1;">
						<label style="font-size:13px;color:#334155;">Last Name</label>
						<input name="last_name" type="text" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;margin-top:6px;">
					</div>
				</div>
				<input type="hidden" name="name" id="fullNameHidden">
				<div style="margin-top:10px;">
					<label style="font-size:13px;color:#334155;">Email</label>
					<input name="email" type="email" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;margin-top:6px;">
				</div>
				<div style="margin-top:10px;">
					<label style="font-size:13px;color:#334155;">Badge Number</label>
					<input name="badge_number" type="text" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;margin-top:6px;">
				</div>
				<div style="margin-top:10px;">
					<label style="font-size:13px;color:#334155;">Station</label>
					<input name="station" type="text" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;margin-top:6px;">
				</div>
				<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:18px;">
					<button type="button" id="cancelAddGuard" style="background:#ecedf2;border-radius:8px;padding:8px 14px;border:0;cursor:pointer;color:#0f172a;">Cancel</button>
					<button type="submit" style="background:#4b5cd1;color:#fff;border-radius:8px;padding:8px 14px;border:0;cursor:pointer;">Add User</button>
				</div>
			</form>
		</div>
	</div>


	<!-- Add Office User Modal -->
	<div id="addOfficeModal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.6); align-items:center; justify-content:center; z-index:80;">
		<div role="dialog" aria-modal="true" aria-labelledby="addOfficeTitle" style="background:#fff;border-radius:10px; width:560px; max-width:94%; padding:20px; box-shadow:0 10px 30px rgba(2,6,23,0.35);">
			<div style="display:flex; justify-content:space-between; align-items:center;">
				<h3 id="addOfficeTitle" style="margin:0; font-size:18px;">Add Office User Account</h3>
				<button id="closeAddOffice" aria-label="Close" style="border:0;background:transparent;font-size:22px;cursor:pointer;line-height:1;color:#374151;">&times;</button>
			</div>

			@if($errors->any())
				<div style="margin:12px 0;padding:10px;border-radius:8px;background:#fff1f2;color:#9f1239;">
					<ul style="margin:0;padding-left:18px;">
						@foreach($errors->all() as $err)
							<li>{{ $err }}</li>
						@endforeach
					</ul>
				</div>
			@endif

			<p style="color:#6b7280;margin:8px 0 14px;">Create a new office staff account for the system.</p>
			<form id="addOfficeForm" method="POST" action="/admin/user/offices">
				@csrf
				<div style="display:flex;gap:10px;">
					<div style="flex:1;">
						<label style="font-size:13px;color:#334155;">First Name</label>
						<input name="first_name" type="text" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;margin-top:6px;">
					</div>
					<div style="flex:1;">
						<label style="font-size:13px;color:#334155;">Last Name</label>
						<input name="last_name" type="text" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;margin-top:6px;">
					</div>
				</div>
				<input type="hidden" name="name" id="fullNameOfficeHidden">
				<div style="margin-top:10px;">
					<label style="font-size:13px;color:#334155;">Email</label>
					<input name="email" type="email" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;margin-top:6px;">
				</div>
				<div style="margin-top:10px;">
					<label style="font-size:13px;color:#334155;">Office</label>
					<select name="office_id" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;margin-top:6px;background:#fff;">
						<option value="" disabled selected>Select Office</option>
						@foreach($officeOptions ?? [] as $officeOption)
							<option value="{{ $officeOption->office_id }}">{{ $officeOption->office_name }}</option>
						@endforeach
					</select>
				</div>
				<div style="margin-top:10px;">
					<label style="font-size:13px;color:#334155;">Position</label>
					<input name="position" type="text" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;margin-top:6px;">
				</div>
				<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:18px;">
					<button type="button" id="cancelAddOffice" style="background:#ecedf2;border-radius:8px;padding:8px 14px;border:0;cursor:pointer;color:#0f172a;">Cancel</button>
					<button type="submit" style="background:#4b5cd1;color:#fff;border-radius:8px;padding:8px 14px;border:0;cursor:pointer;">Add User</button>
				</div>
			</form>
		</div>
	</div>

	<!-- Guard Recycle Bin Modal -->
	<div id="guardRecycleBinModal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.6); align-items:center; justify-content:center; z-index:85;">
		<div role="dialog" aria-modal="true" aria-labelledby="guardRecycleBinTitle" style="background:#fff;border-radius:10px; width:760px; max-width:96%; padding:20px; box-shadow:0 10px 30px rgba(2,6,23,0.35);">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
				<h3 id="guardRecycleBinTitle" style="margin:0; font-size:18px;">Guard Recycle Bin</h3>
				<button id="closeGuardRecycleBin" aria-label="Close" style="border:0;background:transparent;font-size:22px;cursor:pointer;line-height:1;color:#374151;">&times;</button>
			</div>

			<p style="color:#6b7280;margin:4px 0 14px;">Deleted guard accounts are stored here. You can restore them anytime.</p>

			<div style="max-height:420px; overflow:auto; border:1px solid #e5e7eb; border-radius:8px;">
				<table class="guard-table" aria-label="Guard recycle bin table" style="margin:0; width:100%;">
					<thead>
						<tr>
							<th>Name</th>
							<th>Email</th>
							<th>Badge Number</th>
							<th>Station</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						@forelse(($recycledGuards ?? collect([])) as $recycled)
							<tr>
								<td>{{ $recycled->name ?? '—' }}</td>
								<td>{{ $recycled->email ?? '—' }}</td>
								<td>{{ $recycled->badge_number ?? '—' }}</td>
								<td>{{ $recycled->station ?? '—' }}</td>
								<td>
									<form method="POST" action="/admin/user/guards/{{ $recycled->user_id }}/restore" style="display:inline;">
										@csrf
										<button type="submit" class="add-guard-btn" style="padding:6px 10px;font-size:12px;">Restore</button>
									</form>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="5" style="text-align:center;color:#64748b;padding:18px;">Recycle bin is empty.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div style="display:flex; justify-content:flex-end; margin-top:14px;">
				<button type="button" id="cancelGuardRecycleBin" style="background:#ecedf2;border-radius:8px;padding:8px 14px;border:0;cursor:pointer;color:#0f172a;">Close</button>
			</div>
		</div>
	</div>

	<!-- Office Recycle Bin Modal -->
	<div id="officeRecycleBinModal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.6); align-items:center; justify-content:center; z-index:85;">
		<div role="dialog" aria-modal="true" aria-labelledby="officeRecycleBinTitle" style="background:#fff;border-radius:10px; width:760px; max-width:96%; padding:20px; box-shadow:0 10px 30px rgba(2,6,23,0.35);">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
				<h3 id="officeRecycleBinTitle" style="margin:0; font-size:18px;">Office Recycle Bin</h3>
				<button id="closeOfficeRecycleBin" aria-label="Close" style="border:0;background:transparent;font-size:22px;cursor:pointer;line-height:1;color:#374151;">&times;</button>
			</div>

			<p style="color:#6b7280;margin:4px 0 14px;">Deleted office users are stored here. You can restore them anytime.</p>

			<div style="max-height:420px; overflow:auto; border:1px solid #e5e7eb; border-radius:8px;">
				<table class="office-table" aria-label="Office recycle bin table" style="margin:0;">
					<thead>
						<tr>
							<th>Name</th>
							<th>Email</th>
							<th>Office</th>
							<th>Position</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						@forelse(($recycledOffices ?? collect([])) as $recycled)
							<tr>
								<td>{{ $recycled->name ?? '—' }}</td>
								<td>{{ $recycled->email ?? '—' }}</td>
								<td>{{ $recycled->office_name ?? '—' }}</td>
								<td>{{ $recycled->position ?? '—' }}</td>
								<td>
									<form method="POST" action="/admin/user/offices/{{ $recycled->user_id }}/restore" style="display:inline;">
										@csrf
										<button type="submit" class="add-guard-btn" style="padding:6px 10px;font-size:12px;">Restore</button>
									</form>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="5" style="text-align:center;color:#64748b;padding:18px;">Recycle bin is empty.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div style="display:flex; justify-content:flex-end; margin-top:14px;">
				<button type="button" id="cancelOfficeRecycleBin" style="background:#ecedf2;border-radius:8px;padding:8px 14px;border:0;cursor:pointer;color:#0f172a;">Close</button>
			</div>
		</div>
	</div>

	<script>
		// Modal open/close and form handling for Add Office User
		(function() {
			try {
				const openBtn = document.getElementById('openAddOfficeBtn');
				function getModal() { return document.getElementById('addOfficeModal'); }
				function getHiddenFullName() { return document.getElementById('fullNameOfficeHidden'); }

				function showModal() {
					const modal = getModal(); if (!modal) return; modal.style.display = 'flex';
					const first = modal.querySelector('input[name="first_name"]'); if (first) first.focus();
					if (!modal._backdropAttached) { modal.addEventListener('click', (e) => { if (e.target === modal) hideModal(); }); modal._backdropAttached = true; }
				}

				function hideModal() { const modal = getModal(); if (!modal) return; modal.style.display = 'none'; }

				if (openBtn) openBtn.addEventListener('click', showModal);
				document.addEventListener('click', (e) => { if (e.target && e.target.id === 'closeAddOffice') hideModal(); if (e.target && e.target.id === 'cancelAddOffice') hideModal(); });

				document.addEventListener('submit', (e) => {
					if (e.target && e.target.id === 'addOfficeForm') {
						const form = e.target;
						const fn = form.querySelector('input[name="first_name"]')?.value?.trim() || '';
						const ln = form.querySelector('input[name="last_name"]')?.value?.trim() || '';
						const hidden = getHiddenFullName(); if (hidden) hidden.value = (fn + (fn && ln ? ' ' : '') + ln).trim();
					}
				});
			} catch (err) { console && console.error && console.error('Office modal init error:', err); }
		})();

		// Recycle bin modal open/close
		(function() {
			try {
				const openBtn = document.getElementById('openRecycleBinBtn');
				const modal = document.getElementById('officeRecycleBinModal');

				function openModal() { if (modal) modal.style.display = 'flex'; }
				function closeModal() { if (modal) modal.style.display = 'none'; }

				if (openBtn) openBtn.addEventListener('click', openModal);
				document.addEventListener('click', function(e){
					if (!e.target) return;
					if (e.target.id === 'closeOfficeRecycleBin' || e.target.id === 'cancelOfficeRecycleBin') closeModal();
				});

				if (modal) {
					modal.addEventListener('click', function(e){
						if (e.target === modal) closeModal();
					});
				}
			} catch (err) {
				console && console.error && console.error('Recycle bin modal init error:', err);
			}
		})();

		// Guard recycle bin modal open/close
		(function() {
			try {
				const openBtn = document.getElementById('openGuardRecycleBinBtn');
				const modal = document.getElementById('guardRecycleBinModal');

				function openModal() { if (modal) modal.style.display = 'flex'; }
				function closeModal() { if (modal) modal.style.display = 'none'; }

				if (openBtn) openBtn.addEventListener('click', openModal);
				document.addEventListener('click', function(e){
					if (!e.target) return;
					if (e.target.id === 'closeGuardRecycleBin' || e.target.id === 'cancelGuardRecycleBin') closeModal();
				});

				if (modal) {
					modal.addEventListener('click', function(e){
						if (e.target === modal) closeModal();
					});
				}
			} catch (err) {
				console && console.error && console.error('Guard recycle bin modal init error:', err);
			}
		})();

			// Edit button: reuse Add Office modal to perform edits
			(function(){
				function cleanupEditState() {
					const form = document.getElementById('addOfficeForm');
					if (!form) return;
					const methodInput = form.querySelector('input[name="_method"][data-edit-added]');
					if (methodInput) methodInput.remove();
					const uidInput = form.querySelector('input[name="user_id"][data-edit-added]');
					if (uidInput) uidInput.remove();
					if (form.dataset.originalAction) form.setAttribute('action', form.dataset.originalAction);
					const emailInput = form.querySelector('input[name="email"]');
					if (emailInput) {
						emailInput.readOnly = false;
						emailInput.removeAttribute('aria-readonly');
						emailInput.style.background = '';
						emailInput.style.cursor = '';
					}
					const title = document.getElementById('addOfficeTitle'); if (title) title.textContent = 'Add Office User Account';
					const submitBtn = form.querySelector('button[type=submit]'); if (submitBtn) submitBtn.textContent = 'Add User';
				}

				document.querySelectorAll('.edit-office-btn').forEach(function(btn){
					btn.addEventListener('click', function(e){
						e.preventDefault();
						const ds = btn.dataset || {};
						const userId = ds.userId || ds.user_id || ds.userid;
						if (!userId) return;

						const modal = document.getElementById('addOfficeModal'); if (!modal) return; modal.style.display = 'flex';
						const form = document.getElementById('addOfficeForm'); if (!form) return;

						// store original action so we can restore later
						if (!form.dataset.originalAction) form.dataset.originalAction = form.getAttribute('action');

						// fill fields (split name into first/last)
						const fullName = ds.name || '';
						const parts = fullName.trim().split(/\s+/);
						const first = parts.shift() || '';
						const last = parts.join(' ') || '';
						form.querySelector('input[name="first_name"]').value = first;
						form.querySelector('input[name="last_name"]').value = last;
						const emailInput = form.querySelector('input[name="email"]');
						if (emailInput) {
							emailInput.value = ds.email || '';
							emailInput.readOnly = true;
							emailInput.setAttribute('aria-readonly', 'true');
							emailInput.style.background = '#f8fafc';
							emailInput.style.cursor = 'not-allowed';
						}
						const sel = form.querySelector('select[name="office_id"]'); if (sel) sel.value = ds.officeId || ds.office_id || '';
						form.querySelector('input[name="position"]').value = ds.position || '';

						// add method override _method = PUT
						if (!form.querySelector('input[name="_method"][data-edit-added]')){
							const methodInput = document.createElement('input');
							methodInput.type = 'hidden'; methodInput.name = '_method'; methodInput.value = 'PUT'; methodInput.setAttribute('data-edit-added','1');
							form.appendChild(methodInput);
						}

						// add hidden user_id
						if (!form.querySelector('input[name="user_id"][data-edit-added]')){
							const uidInput = document.createElement('input');
							uidInput.type = 'hidden'; uidInput.name = 'user_id'; uidInput.value = userId; uidInput.setAttribute('data-edit-added','1');
							form.appendChild(uidInput);
						}

						// set form action to update URL
						form.setAttribute('action', '/admin/user/offices/' + encodeURIComponent(userId));

						// update modal title and submit text
						const title = document.getElementById('addOfficeTitle'); if (title) title.textContent = 'Edit Office User';
						const submitBtn = form.querySelector('button[type=submit]'); if (submitBtn) submitBtn.textContent = 'Save Changes';
					});
				});

				// cleanup when modal closed via close or cancel buttons
				document.addEventListener('click', function(e){
					if (e.target && (e.target.id === 'closeAddOffice' || e.target.id === 'cancelAddOffice')) {
						cleanupEditState();
					}
				});

				// cleanup on backdrop click as well
				const modal = document.getElementById('addOfficeModal');
				if (modal) {
					modal.addEventListener('click', function(e){
						if (e.target === modal) cleanupEditState();
					});
				}
			})();
	</script>

	</body>
	</html>
