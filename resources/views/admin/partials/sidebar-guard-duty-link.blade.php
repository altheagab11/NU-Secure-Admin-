@php
	$sidebarActiveGuardCount = 0;
	try {
		$sidebarActiveGuardCount = (int) \App\Models\GuardDutyShift::query()->active()->count();
	} catch (\Throwable $e) {
		$sidebarActiveGuardCount = 0;
	}
	$sidebarGuardDutyOn = $sidebarActiveGuardCount > 0;
@endphp
<a href="{{ route('admin.guard-duty') }}" class="sidebar-link {{ request()->is('admin/guard-duty*') ? 'active' : '' }}">
	<span class="sidebar-icon"><i class="bi bi-stopwatch-fill"></i></span>
	<span class="sidebar-text">Guard Duty</span>
	<span
		class="sidebar-status-dot {{ $sidebarGuardDutyOn ? 'is-on' : 'is-off' }}"
		title="{{ $sidebarGuardDutyOn ? 'Guard currently on duty' : 'No guard currently on duty' }}"
		aria-label="{{ $sidebarGuardDutyOn ? 'Guard currently on duty' : 'No guard currently on duty' }}"
	></span>
</a>
