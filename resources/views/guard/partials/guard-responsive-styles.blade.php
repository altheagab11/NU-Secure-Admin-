html {
	-webkit-text-size-adjust: 100%;
	text-size-adjust: 100%;
}

body {
	overflow-x: clip;
}

img,
svg {
	max-width: 100%;
	height: auto;
}

video,
canvas {
	max-width: 100%;
}

.layout {
	min-height: 100vh;
	min-height: 100dvh;
	width: 100%;
}

.main {
	min-width: 0;
	width: 100%;
}

.guard-nav-backdrop {
	display: none;
	position: fixed;
	inset: 0;
	background: rgba(15, 23, 42, 0.48);
	z-index: 999;
	backdrop-filter: blur(3px);
	-webkit-backdrop-filter: blur(3px);
}

body.guard-sidebar-open .guard-nav-backdrop {
	display: block;
}

.guard-topbar {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
	background: #ffffff;
	border-radius: 20px;
	padding: 16px 20px;
	box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
	margin-bottom: 20px;
	border: 1px solid rgba(57, 69, 154, 0.08);
}

.guard-topbar-left {
	display: flex;
	align-items: center;
	gap: 12px;
	min-width: 0;
}

.guard-topbar-copy {
	min-width: 0;
}

.guard-topbar-title {
	margin: 0;
	font-size: 1.45rem;
	font-weight: 800;
	color: #39459a;
	line-height: 1.2;
}

.guard-topbar-date {
	margin: 4px 0 0;
	color: #6b7280;
	font-size: 0.92rem;
}

.guard-topbar-right {
	display: flex;
	align-items: center;
	gap: 14px;
	flex-wrap: wrap;
	justify-content: flex-end;
	flex-shrink: 0;
}

.guard-menu-btn {
	display: none;
	align-items: center;
	justify-content: center;
	width: 42px;
	height: 42px;
	padding: 0;
	border: 0;
	border-radius: 14px;
	background: #eef4ff;
	color: #39459a;
	cursor: pointer;
	flex-shrink: 0;
	transition: background 0.15s ease;
	-webkit-tap-highlight-color: transparent;
}

.guard-menu-btn:hover {
	background: #e0e9ff;
}

.guard-menu-btn .bi {
	font-size: 1.35rem;
	line-height: 1;
}

.guard-icon-btn {
	width: 42px;
	height: 42px;
	border-radius: 14px;
	border: 1px solid #e4ebf7;
	background: #f8fbff;
	color: #39459a;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	position: relative;
	text-decoration: none;
	flex-shrink: 0;
}

.guard-icon-btn:hover {
	background: #eef4ff;
	color: #39459a;
}

.guard-icon-btn__dot {
	position: absolute;
	top: 8px;
	right: 8px;
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: #ef4444;
}

.guard-avatar-chip {
	width: 42px;
	height: 42px;
	border-radius: 14px;
	background: linear-gradient(135deg, #39459a, #4b5cd1);
	color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 700;
	font-size: 0.85rem;
	flex-shrink: 0;
}

.guard-logout-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	border: 1.5px solid #39459a;
	color: #39459a;
	background: #fff;
	border-radius: 999px;
	font-weight: 600;
	font-size: 0.9rem;
	padding: 0.45rem 1.1rem;
	line-height: 1.2;
	cursor: pointer;
	transition: background 0.15s ease, color 0.15s ease;
}

.guard-logout-btn:hover {
	background: #eef4ff;
	color: #39459a;
}

/* Legacy mobile topbar class (kept for print-ticket selectors) */
.guard-mobile-topbar {
	display: none;
}

/* Tablet / mobile: collapsible sidebar drawer */
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
		padding-top: max(18px, env(safe-area-inset-top));
		padding-bottom: max(18px, env(safe-area-inset-bottom));
	}

	body.guard-sidebar-open .sidebar {
		transform: translateX(0);
		box-shadow: 12px 0 40px rgba(0, 0, 0, 0.22);
	}

	.main {
		margin-left: 0 !important;
		flex: 1;
		min-height: 0;
		width: 100%;
		max-width: 100%;
	}

	.guard-menu-btn {
		display: inline-flex;
	}

	.guard-topbar {
		padding: 12px 14px;
		border-radius: 16px;
		margin-bottom: 16px;
		position: sticky;
		top: 0;
		z-index: 50;
	}

	.guard-topbar-title {
		font-size: 1.15rem;
	}

	.guard-topbar-date {
		font-size: 0.8rem;
	}
}

/* Compact tablets / large phones */
@media (max-width: 767.98px) {
	.brand-title span:first-child {
		font-size: clamp(1.15rem, 5vw, 1.5rem);
	}

	.brand-title span:last-child {
		font-size: clamp(1.05rem, 4.5vw, 1.35rem);
	}

	.brand-subtitle {
		font-size: 0.7rem;
	}
}

/* Small phones */
@media (max-width: 390px) {
	.guard-menu-btn {
		width: 40px;
		height: 40px;
		border-radius: 10px;
	}

	.guard-topbar-title {
		font-size: 1.05rem;
	}

	.guard-avatar-chip {
		display: none;
	}
}

/* Landscape phones: keep usable drawer height */
@media (max-height: 500px) and (orientation: landscape) {
	.sidebar {
		overflow-y: auto;
		-webkit-overflow-scrolling: touch;
	}
}

@media (min-width: 992px) {
	.guard-nav-backdrop {
		display: none !important;
	}

	body.guard-sidebar-open {
		overflow: unset !important;
	}

	body.guard-sidebar-open .sidebar {
		transform: none;
	}
}
