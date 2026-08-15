@php
	$topTitle = $title ?? 'Admin';
	$topbarUser = auth()->user();
	$topbarName = trim(((string) ($topbarUser->first_name ?? '')).' '.((string) ($topbarUser->last_name ?? '')));
	$topbarName = $topbarName !== '' ? $topbarName : ((string) ($topbarUser->name ?? $topbarUser->email ?? 'Administrator'));
	$topbarInitials = collect(explode(' ', $topbarName))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('');
	if ($topbarInitials === '') {
		$topbarInitials = 'AD';
	}
	$topbarDate = now('Asia/Manila')->format('l, F j, Y');
	$topbarAlertCount = isset($sidebarUnresolvedAlertsCount)
		? (int) $sidebarUnresolvedAlertsCount
		: (int) \Illuminate\Support\Facades\DB::table('alerts')
			->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = ?", ['unresolved'])
			->count();
@endphp
<header class="admin-topbar">
	<div class="admin-topbar-left">
		<button type="button" class="admin-menu-btn" id="adminNavOpen" aria-label="Open navigation" aria-expanded="false">
			<i class="bi bi-list" aria-hidden="true"></i>
		</button>
		<div class="admin-topbar-copy">
			<h1 class="admin-topbar-title">{{ $topTitle }}</h1>
			<p class="admin-topbar-date">{{ $topbarDate }}</p>
		</div>
	</div>
	<div class="admin-topbar-right">
		<a href="/admin/alerts" class="admin-icon-btn" aria-label="Alerts{{ $topbarAlertCount > 0 ? ' ('.$topbarAlertCount.' unresolved)' : '' }}">
			<i class="bi bi-bell" aria-hidden="true"></i>
			@if ($topbarAlertCount > 0)
				<span class="admin-icon-btn__dot" aria-hidden="true"></span>
			@endif
		</a>
		<div class="admin-avatar-chip" title="{{ $topbarName }}" aria-hidden="true">{{ $topbarInitials }}</div>
		<form method="POST" action="{{ route('logout') }}" class="m-0" id="logout-form">
			@csrf
			<button type="submit" class="admin-logout-btn">Logout</button>
		</form>
	</div>
</header>
