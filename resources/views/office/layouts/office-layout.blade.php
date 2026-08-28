<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title', $pageTitle ?? 'Office Portal') — NU-Secure</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
	<style nonce="{{ $cspNonce }}">
		:root {
			font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
			--sidebar-bg: #39459a;
			--sidebar-bg-light: #4b5cd1;
			--text-white: #f4f6ff;
			--text-yellow: #ffe632;
			--muted: #d8defe;
			--line: rgba(255, 255, 255, 0.18);
			--nu-primary: #064A9F;
			--nu-secondary: #0B57B7;
			--nu-gold: #F5B400;
			--nu-gold-soft: #ffd84d;
			--nu-bg: #F3F7FC;
			--nu-white: #FFFFFF;
			--nu-success: #19B766;
			--nu-warning: #F5A800;
			--nu-danger: #DC3545;
			--nu-text: #182033;
			--nu-muted: #6b7a99;
			--sidebar-width: 260px;
			--radius-card: 20px;
			--radius-btn: 14px;
			--shadow-soft: 0 10px 28px rgba(6, 74, 159, 0.08);
		}

		* { box-sizing: border-box; }
		body {
			margin: 0;
			background: var(--nu-bg);
			color: var(--nu-text);
			min-height: 100vh;
		}

		.office-layout {
			display: flex;
			min-height: 100vh;
			min-height: 100dvh;
		}

		.sidebar,
		.office-sidebar {
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
			transition: transform .25s ease;
		}

		.sidebar::-webkit-scrollbar,
		.office-sidebar::-webkit-scrollbar { width: 6px; }
		.sidebar::-webkit-scrollbar-thumb,
		.office-sidebar::-webkit-scrollbar-thumb {
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
			margin: 0;
			font-size: 0;
			line-height: 1;
			font-weight: 800;
			letter-spacing: -0.02em;
			display: flex;
			gap: 6px;
			align-items: baseline;
		}

		.brand-title span:first-child {
			color: #ffd84d;
			font-size: 28px;
		}

		.brand-title span:last-child {
			color: #ffffff;
			font-size: 26px;
			font-weight: 700;
		}

		.brand-subtitle {
			color: rgba(255, 255, 255, 0.78);
			font-size: 12px;
			display: block;
			margin-top: 2px;
		}

		.sidebar-section { margin-top: 18px; }

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
			border: none;
			transition: all 0.25s ease;
			cursor: pointer;
		}

		.logout-btn:hover {
			background: #ffe9e9;
			color: #ff3b30;
			transform: translateY(-1px);
		}

		.office-main {
			flex: 1;
			margin-left: 260px;
			min-width: 0;
			min-height: 100vh;
			padding: 20px 22px 24px;
			background: #f7f8ff;
		}

		.office-topbar {
			display: flex; align-items: center; justify-content: space-between; gap: 16px;
			background: var(--nu-white);
			border-radius: var(--radius-card);
			padding: 16px 20px;
			box-shadow: var(--shadow-soft);
			margin-bottom: 20px;
			border: 1px solid rgba(6, 74, 159, .06);
		}
		.topbar-left { min-width: 0; }
		.page-heading { margin: 0; font-size: 1.45rem; font-weight: 800; color: var(--nu-primary); }
		.page-sub { margin: 4px 0 0; color: var(--nu-muted); font-size: .92rem; }
		.topbar-right { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; justify-content: flex-end; }
		.topbar-meta { text-align: right; }
		.topbar-meta .office-name { font-weight: 700; color: var(--nu-text); }
		.topbar-meta .staff-line { font-size: .85rem; color: var(--nu-muted); }
		.avatar-chip {
			width: 42px; height: 42px; border-radius: 14px;
			background: linear-gradient(135deg, var(--nu-primary), var(--nu-secondary));
			color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;
		}
		.icon-btn {
			width: 42px; height: 42px; border-radius: 14px; border: 1px solid #e4ebf7;
			background: #f8fbff; color: var(--nu-primary); display: inline-flex;
			align-items: center; justify-content: center; position: relative; text-decoration: none;
		}
		.icon-btn:hover { background: #eef4ff; color: var(--nu-primary); }
		.icon-btn .dot {
			position: absolute; top: 8px; right: 8px; width: 8px; height: 8px;
			border-radius: 50%; background: var(--nu-danger);
		}

		.office-card {
			background: var(--nu-white);
			border-radius: var(--radius-card);
			box-shadow: var(--shadow-soft);
			border: 1px solid rgba(6, 74, 159, .06);
			padding: 20px;
		}
		.office-card h2, .office-card h3 {
			margin: 0 0 6px; font-size: 1.1rem; font-weight: 800; color: var(--nu-text);
		}
		.office-page-title {
			font-size: 1.15rem;
			font-weight: 800;
		}
		.scan-promo-panel {
			min-height: 160px;
			background: linear-gradient(160deg, #0B57B7, #064A9F);
			color: #fff;
		}
		.scan-promo-icon {
			font-size: 2.6rem;
			color: #ffd84d;
		}
		.scan-modal-content {
			border-radius: 20px;
			border: 0;
		}
		.scan-visitor-photo {
			max-height: 180px;
			object-fit: cover;
			width: 100%;
			background: #eef4ff;
		}
		.scan-visitor-photo-fallback {
			height: 160px;
			background: #eef4ff;
			color: #064A9F;
		}
		.scan-photo-icon {
			font-size: 3rem;
		}
		.visitor-detail-photo {
			max-height: 220px;
			object-fit: cover;
			width: 100%;
		}
		.visitor-detail-photo-fallback {
			height: 180px;
			background: #eef4ff;
			color: #064A9F;
		}
		.card-muted { color: var(--nu-muted); font-size: .9rem; margin-bottom: 14px; }

		.btn-nu-primary {
			background: var(--nu-primary); border-color: var(--nu-primary); color: #fff;
			border-radius: var(--radius-btn); font-weight: 600; padding: .65rem 1.1rem;
		}
		.btn-nu-primary:hover { background: var(--nu-secondary); border-color: var(--nu-secondary); color: #fff; }
		.btn-nu-gold {
			background: var(--nu-gold); border-color: var(--nu-gold); color: #182033;
			border-radius: var(--radius-btn); font-weight: 700; padding: .65rem 1.1rem;
		}
		.btn-nu-outline {
			border: 1.5px solid var(--nu-primary); color: var(--nu-primary); background: #fff;
			border-radius: var(--radius-btn); font-weight: 600; padding: .65rem 1.1rem;
		}
		.btn-nu-outline:hover { background: #eef4ff; color: var(--nu-primary); }

		.stat-grid {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 14px;
		}
		.stat-card {
			background: var(--nu-white);
			border-radius: 18px;
			padding: 16px;
			box-shadow: var(--shadow-soft);
			border: 1px solid rgba(6, 74, 159, .06);
			border-top: 4px solid var(--nu-gold);
		}
		.stat-card .label { color: var(--nu-muted); font-size: .82rem; font-weight: 600; }
		.stat-card .value { font-size: 1.85rem; font-weight: 800; color: var(--nu-primary); line-height: 1.2; margin-top: 4px; }
		.stat-card .icon-box {
			width: 40px; height: 40px; border-radius: 12px; background: #eaf1ff;
			color: var(--nu-primary); display: flex; align-items: center; justify-content: center; margin-bottom: 10px;
		}

		.table-office { width: 100%; border-collapse: separate; border-spacing: 0; }
		.table-office th {
			font-size: .78rem; text-transform: uppercase; letter-spacing: .04em;
			color: var(--nu-muted); font-weight: 700; padding: 10px 12px;
			border-bottom: 1px solid #e8eef8; background: #f8fbff; white-space: nowrap;
		}
		.table-office td {
			padding: 12px; border-bottom: 1px solid #eef2f8; vertical-align: middle; font-size: .92rem;
		}
		.table-office tr:hover td { background: #fafcff; }
		.table-scroll { overflow-x: auto; }

		.badge-status {
			display: inline-flex; align-items: center; gap: 6px;
			padding: 5px 10px; border-radius: 999px; font-size: .75rem; font-weight: 700;
		}
		.badge-info { background: #e8f0ff; color: #0B57B7; }
		.badge-warning { background: #fff4d6; color: #9a6b00; }
		.badge-success { background: #e6f8ef; color: #0f8a4b; }
		.badge-danger { background: #fde8ea; color: #b4232e; }
		.badge-muted { background: #eef1f6; color: #5b6478; }

		.empty-state {
			text-align: center; padding: 36px 16px; color: var(--nu-muted);
		}
		.empty-state i { font-size: 2rem; color: var(--nu-secondary); opacity: .7; display: block; margin-bottom: 10px; }

		.sidebar-backdrop {
			display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, .45); z-index: 1035;
		}
		.menu-toggle {
			display: none; border: 0; background: #eef4ff; color: var(--nu-primary);
			width: 42px; height: 42px; border-radius: 14px;
		}

		@media (max-width: 992px) {
			.sidebar,
			.office-sidebar { transform: translateX(-105%); z-index: 1040; }
			.sidebar.open,
			.office-sidebar.open { transform: translateX(0); }
			.office-main { margin-left: 0; padding: 14px; }
			.menu-toggle { display: inline-flex; align-items: center; justify-content: center; }
			.sidebar-backdrop.show { display: block; }
			.stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
		}
		@media (max-width: 576px) {
			.stat-grid { grid-template-columns: 1fr; }
			.topbar-meta { display: none; }
			.office-topbar { padding: 12px; }
		}

		:focus-visible {
			outline: 3px solid rgba(245, 180, 0, .55);
			outline-offset: 2px;
		}
	</style>
	@stack('styles')
</head>
<body>
@php
	$office = $office ?? ($officeContext ?? null);
	$staffName = $staffName ?? trim(trim((string) ($office->first_name ?? '')).' '.trim((string) ($office->last_name ?? '')));
	$staffRole = $staffRole ?? (trim((string) ($office->position ?? 'Office Staff')) ?: 'Office Staff');
	$currentDate = $currentDate ?? now('Asia/Manila')->format('l, F j, Y');
	$active = $activeNav ?? '';
	$unreadCount = isset($notifications) ? count($notifications) : 0;
	$initials = collect(explode(' ', $staffName))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('');
	if ($initials === '') { $initials = 'OS'; }
@endphp

<div class="sidebar-backdrop" id="sidebarBackdrop" hidden></div>

<div class="office-layout">
	<aside class="sidebar office-sidebar d-flex flex-column justify-content-between" id="officeSidebar" aria-label="Office navigation">
		<div>
			<div class="sidebar-brand d-flex align-items-center">
				<div class="brand-icon" aria-hidden="true">
					<i class="bi bi-building"></i>
				</div>
				<div>
					<h4 class="brand-title mb-0"><span>NU</span> <span>Secure</span></h4>
					<small class="brand-subtitle">Office Web Portal</small>
				</div>
			</div>

			<div class="sidebar-section">
				<p class="sidebar-label">Main</p>
				<a href="{{ route('office.dashboard') }}" class="sidebar-link {{ $active === 'dashboard' ? 'active' : '' }}">
					<span class="sidebar-icon"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i></span>
					<span class="sidebar-text">Dashboard</span>
				</a>
				<a href="{{ route('office.scanner') }}" class="sidebar-link {{ $active === 'scanner' ? 'active' : '' }}">
					<span class="sidebar-icon"><i class="bi bi-qr-code-scan" aria-hidden="true"></i></span>
					<span class="sidebar-text">QR Scanner</span>
				</a>
				<a href="{{ route('office.expected-visitors') }}" class="sidebar-link {{ $active === 'expected' ? 'active' : '' }}">
					<span class="sidebar-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
					<span class="sidebar-text">Expected Visitors</span>
				</a>
				<a href="{{ route('office.visit-history') }}" class="sidebar-link {{ $active === 'history' ? 'active' : '' }}">
					<span class="sidebar-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
					<span class="sidebar-text">Visit History</span>
				</a>
			</div>

			<div class="sidebar-section">
				<p class="sidebar-label">Account</p>
				<a href="{{ route('office.notifications') }}" class="sidebar-link {{ $active === 'notifications' ? 'active' : '' }}">
					<span class="sidebar-icon"><i class="bi bi-bell-fill" aria-hidden="true"></i></span>
					<span class="sidebar-text">Notifications</span>
					@if($unreadCount > 0)<span class="sidebar-badge" aria-label="{{ $unreadCount }} unread">{{ $unreadCount }}</span>@endif
				</a>
			</div>
		</div>

		<div class="sidebar-footer">
			<div class="admin-card">
				<div class="admin-avatar" aria-hidden="true">
					<i class="bi bi-person-circle"></i>
				</div>
				<div class="admin-info">
					<h6 class="mb-0">{{ $staffName }}</h6>
					<small>{{ $office->office_name ?? 'Office' }} · {{ $staffRole }}</small>
				</div>
			</div>
		</div>
	</aside>

	<main class="office-main">
		<header class="office-topbar">
			<div class="d-flex align-items-center gap-2 topbar-left">
				<button type="button" class="menu-toggle" id="menuToggle" aria-label="Open navigation" aria-controls="officeSidebar" aria-expanded="false">
					<i class="bi bi-list" aria-hidden="true"></i>
				</button>
				<div>
					<h1 class="page-heading">{{ $pageTitle ?? 'Office Portal' }}</h1>
					<p class="page-sub">{{ $currentDate }}</p>
				</div>
			</div>
			<div class="topbar-right">
				<a href="{{ route('office.notifications') }}" class="icon-btn" aria-label="Notifications">
					<i class="bi bi-bell" aria-hidden="true"></i>
					@if($unreadCount > 0)<span class="dot" aria-hidden="true"></span>@endif
				</a>
				<div class="avatar-chip" title="{{ $staffName }}" aria-hidden="true">{{ $initials }}</div>
				<form method="POST" action="{{ route('logout') }}" class="m-0">
					@csrf
					<button type="submit" class="btn btn-nu-outline btn-sm">Logout</button>
				</form>
			</div>
		</header>

		@if(session('status'))
			<div class="alert alert-success border-0 shadow-sm" role="status">{{ session('status') }}</div>
		@endif
		@if(isset($errors) && $errors->any())
			<div class="alert alert-danger border-0 shadow-sm" role="alert">
				{{ $errors->first() }}
			</div>
		@endif

		@yield('content')
	</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script nonce="{{ $cspNonce }}">
(function () {
	document.querySelectorAll('[data-history-back]').forEach(function (el) {
		el.addEventListener('click', function (event) {
			event.preventDefault();
			window.history.back();
		});
	});

	const sidebar = document.getElementById('officeSidebar');
	const backdrop = document.getElementById('sidebarBackdrop');
	const toggle = document.getElementById('menuToggle');
	if (!sidebar || !toggle) return;

	const setOpen = (open) => {
		sidebar.classList.toggle('open', open);
		backdrop.classList.toggle('show', open);
		backdrop.hidden = !open;
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
	};

	toggle.addEventListener('click', () => setOpen(!sidebar.classList.contains('open')));
	backdrop.addEventListener('click', () => setOpen(false));
	window.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') setOpen(false);
	});
})();
</script>
@stack('scripts')
@include('partials.live-auto-refresh', [
	'liveRefreshIntervalMs' => 10000,
	'liveRefreshPauseSelectors' => [
		'#scanResultModal.show',
		'#manualPayloadModal.show',
		'.modal.show',
	],
])
</body>
</html>
