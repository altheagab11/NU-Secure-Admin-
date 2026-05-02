body {
	overflow-x: clip;
}

.layout {
	min-height: 100vh;
	min-height: 100dvh;
}

.main {
	min-width: 0;
}

.guard-nav-backdrop {
	display: none;
	position: fixed;
	inset: 0;
	background: rgba(15, 23, 42, 0.48);
	z-index: 999;
	backdrop-filter: blur(3px);
}

body.guard-sidebar-open .guard-nav-backdrop {
	display: block;
}

.guard-mobile-topbar {
	display: none;
	align-items: center;
	gap: 12px;
	padding: 10px 0 14px;
	margin: 0 0 4px;
	border-bottom: 1px solid #e2e8f0;
	background: inherit;
}

.guard-menu-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 44px;
	height: 44px;
	padding: 0;
	border: 1px solid #cbd5e1;
	border-radius: 12px;
	background: #fff;
	color: #1e293b;
	cursor: pointer;
	flex-shrink: 0;
	transition: background 0.15s ease, border-color 0.15s ease;
}

.guard-menu-btn:hover {
	background: #f1f5f9;
	border-color: #94a3b8;
}

.guard-menu-btn .bi {
	font-size: 1.45rem;
	line-height: 1;
}

.guard-mobile-topbar-title {
	font-size: 1.05rem;
	font-weight: 700;
	color: #0f172a;
	line-height: 1.25;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

@media (max-width: 991.98px) {
	.layout {
		flex-direction: row;
		height: auto;
		max-height: none;
		overflow: visible;
	}

	.sidebar {
		width: min(300px, 88vw);
		height: 100vh;
		height: 100dvh;
		min-height: 0;
		position: fixed;
		left: 0;
		top: 0;
		bottom: auto;
		transform: translateX(-102%);
		transition: transform 0.28s ease, box-shadow 0.28s ease;
		z-index: 1001;
		box-shadow: none;
	}

	body.guard-sidebar-open .sidebar {
		transform: translateX(0);
		box-shadow: 12px 0 40px rgba(0, 0, 0, 0.22);
	}

	.main {
		margin-left: 0 !important;
		flex: 1;
		min-height: 0;
	}

	.guard-mobile-topbar {
		display: flex;
	}
}

@media (min-width: 992px) {
	.guard-nav-backdrop {
		display: none !important;
	}

	body.guard-sidebar-open {
		overflow: unset !important;
	}
}
