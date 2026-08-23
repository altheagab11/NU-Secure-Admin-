<a href="{{ route('admin.login-attempts') }}" class="sidebar-link {{ request()->is('admin/login-attempts*') ? 'active' : '' }}">
	<span class="sidebar-icon"><i class="bi bi-shield-lock-fill"></i></span>
	<span class="sidebar-text">Login Attempts</span>
</a>
