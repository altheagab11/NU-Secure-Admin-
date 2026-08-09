@php
	$topTitle = $title ?? 'Guard';
	$topbarUser = auth()->user();
	$topbarName = trim(((string) ($topbarUser->first_name ?? '')).' '.((string) ($topbarUser->last_name ?? '')));
	$topbarName = $topbarName !== '' ? $topbarName : ((string) ($topbarUser->name ?? $topbarUser->email ?? 'Guard Officer'));
	$topbarInitials = collect(explode(' ', $topbarName))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('');
	if ($topbarInitials === '') {
		$topbarInitials = 'GO';
	}
	$topbarDate = now('Asia/Manila')->format('l, F j, Y');
	$topbarAlertCount = (int) ($activeAlertsCount ?? 0);
@endphp
<header class="guard-topbar">
	<div class="guard-topbar-left">
		<button type="button" class="guard-menu-btn" id="guardNavOpen" aria-label="Open navigation menu" aria-expanded="false" aria-controls="guardSidebarNav">
			<i class="bi bi-list" aria-hidden="true"></i>
		</button>
		<div class="guard-topbar-copy">
			<h1 class="guard-topbar-title">{{ $topTitle }}</h1>
			<p class="guard-topbar-date">{{ $topbarDate }}</p>
		</div>
	</div>
	<div class="guard-topbar-right">
		<a href="/guard/alert" class="guard-icon-btn" aria-label="Active alerts{{ $topbarAlertCount > 0 ? ' ('.$topbarAlertCount.' unread)' : '' }}">
			<i class="bi bi-bell" aria-hidden="true"></i>
			@if ($topbarAlertCount > 0)
				<span class="guard-icon-btn__dot" aria-hidden="true"></span>
			@endif
		</a>
		<div class="guard-avatar-chip" title="{{ $topbarName }}" aria-hidden="true">{{ $topbarInitials }}</div>
		<form method="POST" action="{{ route('logout') }}" class="m-0" id="logout-form">
			@csrf
			<button type="submit" class="guard-logout-btn">Logout</button>
		</form>
	</div>
</header>
