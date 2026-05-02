@php($topTitle = $title ?? '')
<div class="guard-mobile-topbar">
	<button type="button" class="guard-menu-btn" id="guardNavOpen" aria-label="Open navigation menu" aria-expanded="false" aria-controls="guardSidebarNav">
		<i class="bi bi-list" aria-hidden="true"></i>
	</button>
	@if ($topTitle !== '')
		<span class="guard-mobile-topbar-title">{{ $topTitle }}</span>
	@endif
</div>
