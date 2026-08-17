html {
	-webkit-text-size-adjust: 100%;
	text-size-adjust: 100%;
}

body {
	overflow-x: clip !important;
}

img,
svg,
canvas,
video {
	max-width: 100%;
}

.layout {
	min-width: 0 !important;
	width: 100%;
	max-width: 100%;
}

.main {
	min-width: 0;
	max-width: 100%;
}

.admin-nav-backdrop {
	display: none;
	position: fixed;
	inset: 0;
	background: rgba(15, 23, 42, 0.48);
	z-index: 1090;
	backdrop-filter: blur(3px);
	-webkit-backdrop-filter: blur(3px);
}

body.admin-sidebar-open .admin-nav-backdrop {
	display: block;
}

.admin-menu-btn {
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

.admin-menu-btn:hover,
.admin-menu-btn:focus-visible {
	background: #e0e9ff;
	color: #39459a;
}

.admin-menu-btn .bi {
	font-size: 1.35rem;
	line-height: 1;
}

.table-responsive,
.table-card {
	max-width: 100%;
	-webkit-overflow-scrolling: touch;
}

.sidebar-status-dot {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	flex-shrink: 0;
	box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.16);
}

.sidebar-status-dot.is-on {
	background: #4ade80;
}

.sidebar-status-dot.is-off {
	background: #f87171;
}

/* Laptop / small desktop: keep sidebar, let content wrap */
@media (max-width: 1199.98px) {
	.filters-row,
	.filters-grid,
	.alert-filters-row,
	.generate-grid {
		grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
	}

	.stats-grid,
	.summary-cards,
	.alert-stats,
	.office-summary-grid,
	.charts-grid {
		grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
	}

	.toolbar,
	.header-row {
		flex-wrap: wrap;
	}
}

/* Tablet and phone: off-canvas sidebar + full-width content */
@media (max-width: 991.98px) {
	.layout {
		display: block !important;
		height: auto !important;
		max-height: none !important;
		overflow: visible !important;
		min-width: 0 !important;
	}

	.sidebar {
		width: min(300px, 88vw) !important;
		height: 100vh !important;
		height: 100dvh !important;
		min-height: 0 !important;
		position: fixed !important;
		left: 0 !important;
		top: 0 !important;
		bottom: auto !important;
		transform: translateX(-102%);
		transition: transform 0.28s ease, box-shadow 0.28s ease;
		z-index: 1100 !important;
		box-shadow: none !important;
		padding-top: max(18px, env(safe-area-inset-top));
		padding-bottom: max(18px, env(safe-area-inset-bottom));
	}

	body.admin-sidebar-open .sidebar {
		transform: translateX(0);
		box-shadow: 12px 0 40px rgba(0, 0, 0, 0.22) !important;
	}

	.main {
		display: block !important;
		margin-left: 0 !important;
		width: 100% !important;
		max-width: 100% !important;
		flex: none !important;
		height: auto !important;
		min-height: 100vh;
		min-height: 100dvh;
		padding: 12px 14px 28px !important;
		overflow: visible !important;
	}

	.admin-menu-btn {
		display: inline-flex;
	}

	.admin-topbar {
		padding: 12px 14px;
		border-radius: 16px;
		margin-bottom: 16px;
		position: sticky;
		top: 0;
		z-index: 50;
	}

	.admin-topbar-title {
		font-size: 1.15rem;
	}

	.admin-topbar-date {
		font-size: 0.8rem;
	}

	.filters-row,
	.filters-grid,
	.alert-filters-row,
	.generate-grid,
	.custom-range-row.is-visible {
		grid-template-columns: 1fr !important;
	}

	.filters-card {
		flex-wrap: wrap;
		align-items: stretch !important;
	}

	.filter-input,
	.filter-select {
		min-width: 0 !important;
		width: 100%;
		max-width: 100%;
	}

	.toolbar,
	.header-row,
	.refresh-meta,
	.filters-actions,
	.action-btns {
		flex-wrap: wrap;
	}

	.page-title,
	.dashboard-title {
		font-size: 1.35rem !important;
	}

	.insights-title {
		font-size: 1.2rem !important;
	}

	.insights-list li {
		font-size: 1rem !important;
		line-height: 1.45 !important;
	}

	.stat-number,
	.stat-value,
	.office-summary-count {
		font-size: 1.6rem !important;
	}

	.offcanvas {
		width: min(520px, 100vw) !important;
	}

	.modal-dialog {
		margin: 0.75rem;
		max-width: calc(100% - 1.5rem);
	}

	.visitor-detail-modal {
		padding: 10px;
	}

	.visitor-detail-dialog {
		max-width: 100%;
	}

	.vd-header-left h2 {
		font-size: 1.25rem;
	}

	.vd-grid {
		grid-template-columns: 1fr !important;
	}
}

@media (max-width: 767.98px) {
	.stats-grid,
	.summary-cards,
	.alert-stats,
	.office-summary-grid,
	.charts-grid,
	.legend-grid {
		grid-template-columns: 1fr !important;
	}

	.admin-avatar-chip {
		display: none;
	}

	.admin-logout-btn {
		padding: 0.4rem 0.85rem;
		font-size: 0.82rem;
	}

	.brand-title span:first-child {
		font-size: clamp(1.15rem, 5vw, 1.5rem);
	}

	.brand-title span:last-child {
		font-size: clamp(1.05rem, 4.5vw, 1.35rem);
	}

	.activity-pagination,
	.table-pagination-bar {
		flex-direction: column;
		align-items: stretch;
	}
}

@media (max-width: 390px) {
	.admin-menu-btn {
		width: 40px;
		height: 40px;
		border-radius: 10px;
	}

	.admin-topbar-title {
		font-size: 1.05rem;
	}

	.admin-icon-btn {
		width: 38px;
		height: 38px;
	}
}

@media (max-height: 500px) and (orientation: landscape) {
	.sidebar {
		overflow-y: auto;
		-webkit-overflow-scrolling: touch;
	}
}

@media (min-width: 992px) {
	.admin-nav-backdrop {
		display: none !important;
	}

	body.admin-sidebar-open {
		overflow: unset !important;
	}

	body.admin-sidebar-open .sidebar {
		transform: none;
	}
}
