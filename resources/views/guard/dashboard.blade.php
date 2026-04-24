<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Guard Dashboard</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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

		* {
			box-sizing: border-box;
		}

		body {
			margin: 0;
			background: var(--sidebar-bg);
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
			background: rgba(255,255,255,0.18);
			border-radius: 10px;
		}

		.sidebar-brand {
			gap: 12px;
			padding: 10px 10px 18px;
			margin-bottom: 10px;
			border-bottom: 1px solid rgba(255,255,255,0.12);
		}

		.brand-icon {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			background: rgba(255,255,255,0.14);
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
			color: rgba(255,255,255,0.78);
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
			color: rgba(255,255,255,0.55);
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
			background: rgba(255,255,255,0.10);
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
		}

		.dropdown-arrow {
			transition: transform 0.25s ease;
			font-size: 13px;
		}

		.sidebar-toggle[aria-expanded="true"] .dropdown-arrow {
			transform: rotate(180deg);
		}

		.submenu {
			margin: 6px 0 8px 14px;
			padding-left: 14px;
			border-left: 1px solid rgba(255,255,255,0.15);
			display: grid;
			gap: 5px;
		}

		.submenu-link {
			display: flex;
			align-items: center;
			gap: 10px;
			color: rgba(255,255,255,0.88);
			text-decoration: none;
			padding: 10px 12px;
			border-radius: 10px;
			margin-bottom: 0;
			font-size: 14px;
			transition: all 0.2s ease;
		}

		.submenu-link:hover {
			background: rgba(255,255,255,0.10);
			color: #fff;
			transform: translateX(3px);
		}

		.submenu-link.active {
			background: rgba(255,255,255,0.16);
			color: #ffd84d;
			font-weight: 600;
		}

		.quick-action-btn {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			width: 100%;
			text-decoration: none;
			background: #ffd84d;
			color: #243c96;
			font-weight: 700;
			border-radius: 12px;
			padding: 12px 14px;
			margin-top: 6px;
			transition: all 0.25s ease;
		}

		.quick-action-btn:hover {
			background: #ffcf1f;
			color: #243c96;
			transform: translateY(-2px);
			box-shadow: 0 10px 18px rgba(255, 216, 77, 0.25);
		}

		.sidebar-footer {
			padding-top: 16px;
			margin-top: 20px;
			border-top: 1px solid rgba(255,255,255,0.12);
		}

		.admin-card {
			display: flex;
			align-items: center;
			gap: 12px;
			background: rgba(255,255,255,0.08);
			border-radius: 14px;
			padding: 12px;
			margin-bottom: 12px;
		}

		.admin-avatar {
			width: 42px;
			height: 42px;
			border-radius: 50%;
			background: rgba(255,255,255,0.15);
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
			color: rgba(255,255,255,0.72);
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
			background: #f5f7fb;
			padding: 0;
			overflow-y: auto;
			margin-left: 260px;
			min-height: 100vh;
		}

		.dashboard-wrap {
			padding: 24px;
		}

		.dashboard-header {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			gap: 16px;
			margin-bottom: 24px;
			flex-wrap: wrap;
		}

		.page-title {
			font-size: 30px;
			font-weight: 700;
			margin: 0 0 6px;
			color: #111827;
		}

		.page-subtitle {
			margin: 0;
			font-size: 14px;
			color: #6b7280;
		}

		.header-meta {
			display: flex;
			align-items: center;
			gap: 12px;
			flex-wrap: wrap;
		}

		.live-badge {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			background: #ffffff;
			border: 1px solid #e5e7eb;
			padding: 10px 14px;
			border-radius: 999px;
			font-size: 13px;
			font-weight: 600;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
		}

		.dot {
			width: 8px;
			height: 8px;
			background: #22c55e;
			border-radius: 50%;
			display: inline-block;
		}

		.last-update {
			font-size: 13px;
			color: #6b7280;
		}

		.stats-grid {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 18px;
			margin-bottom: 18px;
		}

		.stat-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 18px;
			padding: 20px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
			transition: 0.2s ease;
		}

		.stat-card:hover {
			transform: translateY(-2px);
		}

		.stat-card-primary {
			background: linear-gradient(135deg, #3949ab, #2f3f9d);
			color: #fff;
			border: none;
		}

		.stat-card-primary .stat-label,
		.stat-card-primary .stat-text {
			color: rgba(255, 255, 255, 0.85);
		}

		.stat-top {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 18px;
		}

		.stat-label {
			font-size: 14px;
			color: #6b7280;
			font-weight: 600;
		}

		.stat-icon {
			width: 42px;
			height: 42px;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 16px;
		}

		.soft-green {
			background: #eafaf1;
			color: #16a34a;
		}

		.soft-yellow {
			background: #fff8e6;
			color: #d97706;
		}

		.soft-gray {
			background: #f3f4f6;
			color: #6b7280;
		}

		.soft-blue-light {
			background: rgba(255, 255, 255, 0.18);
			color: #ffffff;
		}

		.stat-number {
			margin: 0 0 6px;
			font-size: 32px;
			font-weight: 700;
			line-height: 1.1;
		}

		.stat-text {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
		}

		.overview-strip {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 14px;
			margin-bottom: 18px;
		}

		.overview-item {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 14px;
			padding: 14px 16px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
		}

		.overview-title {
			display: block;
			font-size: 12px;
			color: #6b7280;
			margin-bottom: 6px;
		}

		.overview-item strong {
			font-size: 20px;
			color: #111827;
		}

		.action-grid {
			display: grid;
			grid-template-columns: 1.4fr 1fr;
			gap: 18px;
			margin-bottom: 18px;
		}

		.action-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 18px;
			padding: 22px;
			display: flex;
			align-items: center;
			gap: 16px;
			text-decoration: none;
			color: inherit;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
			transition: all 0.2s ease;
		}

		.action-card:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
			color: inherit;
		}

		.action-card-primary {
			background: linear-gradient(135deg, #3949ab, #2f3f9d);
			border: none;
			color: #ffffff;
		}

		.action-card-primary .action-content p {
			color: rgba(255, 255, 255, 0.82);
		}

		.action-icon {
			width: 52px;
			height: 52px;
			min-width: 52px;
			border-radius: 14px;
			background: #fef3c7;
			color: #d97706;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 18px;
		}

		.action-icon.secondary {
			background: #eef2ff;
			color: #3949ab;
		}

		.action-content {
			flex: 1;
		}

		.action-content h3 {
			margin: 0 0 6px;
			font-size: 18px;
			font-weight: 700;
		}

		.action-content p {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
			line-height: 1.5;
		}

		.action-arrow {
			font-size: 18px;
			opacity: 0.8;
		}

		.alert-banner {
			background: #ffffff;
			border: 1px solid #f3e8b3;
			border-left: 5px solid #facc15;
			border-radius: 18px;
			padding: 18px 20px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 16px;
			margin-bottom: 20px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
		}

		.alert-banner-left {
			display: flex;
			align-items: center;
			gap: 14px;
		}

		.alert-banner-icon {
			width: 46px;
			height: 46px;
			border-radius: 12px;
			background: #fff8e6;
			color: #d97706;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 18px;
		}

		.alert-banner h4 {
			margin: 0 0 4px;
			font-size: 16px;
			font-weight: 700;
		}

		.alert-banner p {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
		}

		.alert-banner-btn {
			text-decoration: none;
			background: #111827;
			color: #ffffff;
			padding: 10px 16px;
			border-radius: 10px;
			font-size: 13px;
			font-weight: 600;
		}

		.panel-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 20px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
			overflow: hidden;
		}

		.panel-header {
			padding: 20px 22px;
			border-bottom: 1px solid #e5e7eb;
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 16px;
			flex-wrap: wrap;
		}

		.panel-header h3 {
			margin: 0 0 4px;
			font-size: 18px;
			font-weight: 700;
		}

		.panel-header p {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
		}

		.panel-actions {
			display: flex;
			gap: 10px;
			flex-wrap: wrap;
		}

		.search-input,
		.filter-select {
			height: 42px;
			border: 1px solid #e5e7eb;
			background: #ffffff;
			border-radius: 10px;
			padding: 0 14px;
			font-size: 14px;
			color: #1f2937;
			outline: none;
		}

		.search-input {
			min-width: 220px;
		}

		.search-input:focus,
		.filter-select:focus {
			border-color: #c7d2fe;
			box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08);
		}

		.visitor-table-wrap {
			width: 100%;
			overflow-x: auto;
		}

		.visitor-table {
			width: 100%;
			border-collapse: collapse;
		}

		.visitor-table thead th {
			text-align: left;
			font-size: 12px;
			font-weight: 700;
			color: #6b7280;
			padding: 16px 22px;
			background: #fafbfc;
			border-bottom: 1px solid #e5e7eb;
			text-transform: uppercase;
			letter-spacing: 0.04em;
		}

		.visitor-table tbody td {
			padding: 18px 22px;
			border-bottom: 1px solid #f1f5f9;
			vertical-align: middle;
			font-size: 14px;
		}

		.visitor-table tbody tr:hover {
			background: #fafcff;
		}

		.visitor-cell {
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.avatar-circle {
			width: 42px;
			height: 42px;
			border-radius: 50%;
			background: #eef2ff;
			color: #3949ab;
			font-weight: 700;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 13px;
		}

		.visitor-name {
			font-weight: 600;
			color: #111827;
		}

		.visitor-meta {
			font-size: 12px;
			color: #6b7280;
			margin-top: 2px;
		}

		.status-badge {
			display: inline-flex;
			align-items: center;
			padding: 7px 12px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 700;
		}

		.status-badge.arrived {
			background: #eafaf1;
			color: #15803d;
		}

		.status-badge.transit {
			background: #eaf4ff;
			color: #2563eb;
		}

		.status-badge.exit {
			background: #fff8e6;
			color: #b45309;
		}

		.table-link {
			color: #3949ab;
			text-decoration: none;
			font-weight: 600;
			background: transparent;
			border: 0;
			padding: 0;
		}

		.table-link:hover {
			text-decoration: underline;
		}

		.drawer-overlay {
			position: fixed;
			inset: 0;
			background: rgba(15, 23, 42, 0.45);
			opacity: 0;
			visibility: hidden;
			transition: 0.25s ease;
			z-index: 998;
		}

		.drawer-overlay.active {
			opacity: 1;
			visibility: visible;
		}

		.visitor-drawer {
			position: fixed;
			top: 0;
			right: -680px;
			width: 620px;
			max-width: 100%;
			height: 100vh;
			background: #f8fafc;
			box-shadow: -10px 0 40px rgba(15, 23, 42, 0.18);
			z-index: 999;
			display: flex;
			flex-direction: column;
			transition: 0.3s ease;
		}

		.visitor-drawer.active {
			right: 0;
		}

		.drawer-header {
			background: #ffffff;
			padding: 22px 26px;
			border-bottom: 1px solid #e5e7eb;
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
		}

		.drawer-header h2 { margin: 0; font-size: 22px; font-weight: 800; color: #0f172a; }
		.drawer-header p { margin: 5px 0 0; font-size: 13px; color: #64748b; }
		.drawer-close { border: none; background: #f1f5f9; color: #334155; width: 34px; height: 34px; border-radius: 10px; font-size: 24px; cursor: pointer; }
		.drawer-close:hover { background: #e2e8f0; }
		.drawer-body { flex: 1; overflow-y: auto; padding: 20px 24px; }
		.drawer-card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 18px; padding: 18px; margin-bottom: 16px; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04); }
		.drawer-section-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
		.drawer-section-title h3 { margin: 0; font-size: 16px; font-weight: 800; color: #0f172a; }
		.profile-top { display: flex; gap: 16px; align-items: center; }
		.visitor-photo { width: 90px; height: 90px; border-radius: 16px; background: #eef2ff; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #64748b; }
		.visitor-photo img { width: 100%; height: 100%; object-fit: cover; }
		.profile-info h3 { margin: 0 0 8px; font-size: 20px; color: #0f172a; }
		.profile-info p { margin: 5px 0; font-size: 13px; color: #475569; }
		.drawer-info-grid { margin-top: 16px; display: grid; grid-template-columns: 1fr; gap: 12px; }
		.drawer-info-grid.two-col { grid-template-columns: repeat(2, 1fr); }
		.drawer-info-grid div, .drawer-full-info { background: #f8fafc; border-radius: 12px; padding: 12px; }
		.drawer-info-grid span, .drawer-full-info span { display: block; font-size: 12px; color: #64748b; margin-bottom: 4px; }
		.drawer-info-grid strong, .drawer-full-info strong { font-size: 14px; color: #0f172a; font-weight: 700; }
		.drawer-full-info { margin-top: 12px; }
		.drawer-status-badge { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
		.drawer-status-badge.green { background: #dcfce7; color: #15803d; }
		.drawer-status-badge.gray { background: #f1f5f9; color: #475569; }
		.drawer-status-badge.red { background: #fee2e2; color: #b91c1c; }
		.drawer-status-badge.orange { background: #ffedd5; color: #c2410c; }
		.timeline { position: relative; }
		.timeline-item { display: flex; gap: 14px; position: relative; padding-bottom: 18px; }
		.timeline-item:not(:last-child)::before { content: ""; position: absolute; left: 8px; top: 20px; width: 2px; height: calc(100% - 10px); background: #e5e7eb; }
		.timeline-dot { width: 18px; height: 18px; border-radius: 50%; margin-top: 3px; background: #cbd5e1; z-index: 1; }
		.timeline-item.completed .timeline-dot { background: #22c55e; }
		.timeline-item.pending .timeline-dot { background: #94a3b8; }
		.timeline-content { width: 100%; }
		.timeline-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
		.timeline-head strong { font-size: 14px; color: #0f172a; }
		.timeline-content p { margin: 5px 0 0; font-size: 13px; color: #64748b; }
		.scan-list { display: flex; flex-direction: column; gap: 12px; }
		.scan-card { border-radius: 14px; padding: 14px; border: 1px solid #e5e7eb; background: #f8fafc; }
		.scan-card.valid { border-left: 5px solid #22c55e; }
		.scan-card.invalid { border-left: 5px solid #ef4444; }
		.scan-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
		.scan-head h4 { margin: 0; font-size: 15px; color: #0f172a; }
		.scan-head p { margin: 4px 0 0; font-size: 13px; color: #475569; }
		.scan-meta { display: flex; justify-content: space-between; gap: 10px; margin-top: 12px; font-size: 12px; color: #64748b; }
		.scan-remarks { margin: 10px 0 0; font-size: 13px; color: #334155; }
		.drawer-alert-card { border-color: #fed7aa; background: #fffaf5; }
		.drawer-alert-message { background: #ffffff; border: 1px solid #fed7aa; padding: 12px; border-radius: 12px; font-size: 14px; color: #7c2d12; line-height: 1.5; }
		.drawer-footer { background: #ffffff; border-top: 1px solid #e5e7eb; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px; }
		.drawer-btn-secondary, .drawer-btn-primary { border: none; padding: 11px 18px; border-radius: 12px; font-weight: 700; cursor: pointer; font-size: 14px; }
		.drawer-btn-secondary { background: #f1f5f9; color: #334155; }
		.drawer-btn-primary { background: #0f172a; color: #ffffff; }
		.drawer-empty { text-align: center; color: #64748b; font-size: 14px; padding: 8px 0; }

		@media (max-width: 1024px) {
			.layout {
				flex-direction: column;
			}

			.sidebar {
				width: 100%;
				min-height: auto;
			}

			.main {
				display: block;
			}
		}

		@media (max-width: 1200px) {
			.stats-grid,
			.overview-strip {
				grid-template-columns: repeat(2, 1fr);
			}

			.action-grid {
				grid-template-columns: 1fr;
			}
		}

		@media (max-width: 768px) {
			.dashboard-wrap {
				padding: 16px;
			}

			.stats-grid,
			.overview-strip {
				grid-template-columns: 1fr;
			}

			.panel-actions {
				width: 100%;
				flex-direction: column;
			}

			.search-input,
			.filter-select {
				width: 100%;
			}

			.alert-banner {
				flex-direction: column;
				align-items: flex-start;
			}

			.visitor-table thead th,
			.visitor-table tbody td {
				padding: 14px 16px;
			}
		}
	</style>
</head>
<body>
	<div class="layout">
		@php
			$guardSidebarUser = auth()->user();
			$guardSidebarName = trim(((string) ($guardSidebarUser->first_name ?? '')).' '.((string) ($guardSidebarUser->last_name ?? '')));
			$guardSidebarName = $guardSidebarName !== '' ? $guardSidebarName : ((string) ($guardSidebarUser->name ?? $guardSidebarUser->email ?? 'Guard Officer'));
			$isSelfRegisteredRole = (int) optional($guardSidebarUser)->role_id === 4;
		@endphp
		<aside class="sidebar d-flex flex-column justify-content-between">
			<div>
				<div class="sidebar-brand d-flex align-items-center">
					<div class="brand-icon">
						<i class="bi bi-shield-lock-fill"></i>
					</div>
					<div>
						<h4 class="brand-title mb-0">SVMS Guard</h4>
						<small class="brand-subtitle">Smart Visitor Monitoring System</small>
					</div>
				</div>

				@if (! $isSelfRegisteredRole)
					<div class="sidebar-section">
						<p class="sidebar-label">MAIN</p>
						<a href="/guard/dashboard" class="sidebar-link {{ request()->is('guard/dashboard') ? 'active' : '' }}">
							<span class="sidebar-icon"><i class="bi bi-grid-1x2-fill"></i></span>
							<span class="sidebar-text">Dashboard</span>
						</a>
					</div>
				@endif

				<div class="sidebar-section">
					<p class="sidebar-label">VISITOR ACTIONS</p>

					<div class="sidebar-dropdown">
						<button class="sidebar-link sidebar-toggle {{ request()->is('guard/register*') ? 'active open' : '' }}"
							type="button"
							data-bs-toggle="collapse"
							data-bs-target="#registerMenu"
							aria-expanded="{{ request()->is('guard/register*') ? 'true' : 'false' }}">
							<span class="d-flex align-items-center gap-2">
								<span class="sidebar-icon"><i class="bi bi-person-plus-fill"></i></span>
								<span class="sidebar-text">Register</span>
							</span>
							<span class="dropdown-arrow"><i class="bi bi-chevron-down"></i></span>
						</button>

						<div class="collapse {{ request()->is('guard/register*') ? 'show' : '' }}" id="registerMenu">
							<div class="submenu">
								<a href="/guard/register?type=normal" class="submenu-link {{ request('type', 'normal') === 'normal' ? 'active' : '' }}">
									<i class="bi bi-person"></i>
									<span>Normal Visitor</span>
								</a>
								<a href="/guard/register?type=enrollee" class="submenu-link {{ request('type') === 'enrollee' ? 'active' : '' }}">
									<i class="bi bi-mortarboard-fill"></i>
									<span>Enrollee</span>
								</a>
								<a href="/guard/register?type=contractor" class="submenu-link {{ request('type') === 'contractor' ? 'active' : '' }}">
									<i class="bi bi-briefcase-fill"></i>
									<span>Contractor</span>
								</a>
							</div>
						</div>
					</div>

					@if (! $isSelfRegisteredRole)
						<a href="/guard/exit" class="sidebar-link {{ request()->is('guard/exit') ? 'active' : '' }}">
							<span class="sidebar-icon"><i class="bi bi-box-arrow-right"></i></span>
							<span class="sidebar-text">Exit Scan</span>
						</a>
					@endif
				</div>

				@if (! $isSelfRegisteredRole)
					<div class="sidebar-section">
						<p class="sidebar-label">MONITORING</p>
						<a href="/guard/alert" class="sidebar-link {{ request()->is('guard/alert') ? 'active' : '' }}">
							<span class="sidebar-icon"><i class="bi bi-bell-fill"></i></span>
							<span class="sidebar-text">Active Alerts</span>
							<span class="sidebar-badge">{{ $activeAlertsCount ?? 0 }}</span>
						</a>
					</div>
				@endif

				<div class="sidebar-section">
					<p class="sidebar-label">QUICK ACTION</p>
					<a href="/guard/register" class="quick-action-btn">
						<i class="bi bi-qr-code-scan"></i>
						<span>Register Visitor</span>
					</a>
				</div>
			</div>

			<div class="sidebar-footer">
				<div class="admin-card">
					<div class="admin-avatar">
						<i class="bi bi-person-badge-fill"></i>
					</div>
					<div class="admin-info">
						<h6 class="mb-0">{{ $guardSidebarName }}</h6>
						<small>Guard Officer</small>
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
			<div class="dashboard-wrap">
				<div class="dashboard-header">
					<div>
						<h1 class="page-title">Guard Dashboard</h1>
						<p class="page-subtitle">Monitor visitors, alerts, and campus activity in real time.</p>
					</div>

					<div class="header-meta">
						<div class="live-badge">
							<span class="dot"></span>
							Live Monitoring
						</div>
						<div class="last-update">Last updated: <span id="lastUpdateTime">--:-- --</span></div>
					</div>
				</div>

				<div class="stats-grid">
					<div class="stat-card">
						<div class="stat-top">
							<span class="stat-label">Active Visitors</span>
							<div class="stat-icon soft-green">
								<i class="fas fa-users"></i>
							</div>
						</div>
						<h2 class="stat-number">{{ number_format((int) ($activeVisitorsCount ?? 0)) }}</h2>
						<p class="stat-text">Currently inside campus</p>
					</div>

					<div class="stat-card stat-card-primary">
						<div class="stat-top">
							<span class="stat-label">Current Time</span>
							<div class="stat-icon soft-blue-light">
								<i class="fas fa-clock"></i>
							</div>
						</div>
						<h2 class="stat-number" id="currentTimeValue">--:-- --</h2>
						<p class="stat-text">System time</p>
					</div>

					<div class="stat-card">
						<div class="stat-top">
							<span class="stat-label">Active Alerts</span>
							<div class="stat-icon soft-yellow">
								<i class="fas fa-bell"></i>
							</div>
						</div>
						<h2 class="stat-number">{{ number_format((int) ($activeAlertsCount ?? 0)) }}</h2>
						<p class="stat-text">Total unresolved alerts</p>
					</div>

					<div class="stat-card">
						<div class="stat-top">
							<span class="stat-label">Exited Today</span>
							<div class="stat-icon soft-gray">
								<i class="fas fa-arrow-right-from-bracket"></i>
							</div>
						</div>
						<h2 class="stat-number">{{ number_format((int) ($exitedTodayCount ?? 0)) }}</h2>
						<p class="stat-text">Completed visitor exits</p>
					</div>
				</div>

				<div class="overview-strip">
					<div class="overview-item">
						<span class="overview-title">Entries Today</span>
						<strong>{{ number_format((int) ($entriesTodayCount ?? 0)) }}</strong>
					</div>
					<div class="overview-item">
						<span class="overview-title">Pending Exit Scans</span>
						<strong>{{ number_format((int) ($pendingExitScansCount ?? 0)) }}</strong>
					</div>
					<div class="overview-item">
						<span class="overview-title">Unresolved Alerts</span>
						<strong>{{ number_format((int) ($unresolvedAlertsCount ?? 0)) }}</strong>
					</div>
					<div class="overview-item">
						<span class="overview-title">Resolved Today</span>
						<strong>{{ number_format((int) ($resolvedTodayCount ?? 0)) }}</strong>
					</div>
				</div>

				<div class="action-grid">
					<a href="/guard/register?type=normal" class="action-card action-card-primary">
						<div class="action-icon">
							<i class="fas fa-user-plus"></i>
						</div>
						<div class="action-content">
							<h3>Register New Visitor</h3>
							<p>Capture face and ID, fill out form, and generate QR ticket.</p>
						</div>
						<div class="action-arrow">
							<i class="fas fa-arrow-right"></i>
						</div>
					</a>

					<a href="/guard/exit" class="action-card">
						<div class="action-icon secondary">
							<i class="fas fa-qrcode"></i>
						</div>
						<div class="action-content">
							<h3>Exit Scan</h3>
							<p>Scan visitor QR code to process exit quickly and accurately.</p>
						</div>
						<div class="action-arrow">
							<i class="fas fa-arrow-right"></i>
						</div>
					</a>
				</div>

				<div class="alert-banner">
					<div class="alert-banner-left">
						<div class="alert-banner-icon">
							<i class="fas fa-bell"></i>
						</div>
						<div>
							<h4>Attention Needed</h4>
							<p>
								{{ number_format((int) ($pendingExitScansCount ?? 0)) }}
								visitor{{ ((int) ($pendingExitScansCount ?? 0)) === 1 ? '' : 's' }}
								{{ ((int) ($pendingExitScansCount ?? 0)) === 1 ? 'is' : 'are' }}
								ready to exit and waiting for QR scan.
							</p>
						</div>
					</div>

					<a href="/guard/alert" class="alert-banner-btn">View Alerts</a>
				</div>

				<div class="panel-card">
					<div class="panel-header">
						<div>
							<h3>Active Visitors Inside Campus</h3>
							<p>Track current visitor movement and status.</p>
						</div>

						<div class="panel-actions">
							<input type="text" class="search-input" placeholder="Search visitor..." />
							<select class="filter-select">
								<option>All Status</option>
								<option>Arrived</option>
								<option>In Transit</option>
								<option>Ready to Exit</option>
							</select>
						</div>
					</div>

					<div class="visitor-table-wrap">
						<table class="visitor-table">
							<thead>
								<tr>
									<th>Visitor</th>
									<th>Destination</th>
									<th>Entry Time</th>
									<th>Duration</th>
									<th>Status</th>
									<th>Alert</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								@forelse(($activeVisitors ?? []) as $visitor)
									<tr>
										<td>
											<div class="visitor-cell">
												<div class="avatar-circle">{{ $visitor['initials'] ?? 'NA' }}</div>
												<div>
													<div class="visitor-name">{{ $visitor['visitor_name'] ?? 'Unknown Visitor' }}</div>
													<div class="visitor-meta">Pass No: {{ $visitor['pass_number'] ?? 'No pass/control number' }}</div>
												</div>
											</div>
										</td>
										<td>{{ $visitor['office_name'] ?? 'No destination' }}</td>
										<td>{{ $visitor['entry_time'] ?? '—' }}</td>
										<td>{{ $visitor['duration'] ?? '—' }}</td>
										<td><span class="status-badge {{ $visitor['status_class'] ?? 'arrived' }}">{{ $visitor['status_label'] ?? 'Arrived' }}</span></td>
										<td>{{ $visitor['alert'] ?? 'None' }}</td>
										<td><button type="button" class="table-link js-view-visitor" data-visit-id="{{ $visitor['visit_id'] ?? 0 }}">View</button></td>
									</tr>
								@empty
									<tr>
										<td colspan="7" style="text-align:center; color:#6b7280;">No active visitors inside campus.</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</main>
	</div>

	<div id="visitorDrawerOverlay" class="drawer-overlay"></div>
	<div id="visitorDrawer" class="visitor-drawer">
		<div class="drawer-header">
			<div>
				<h2>Visitor Details</h2>
				<p>View complete visitor transaction details.</p>
			</div>
			<button type="button" class="drawer-close" id="closeVisitorDrawerBtn">&times;</button>
		</div>
		<div class="drawer-body" id="visitorDrawerBody">
			<div class="drawer-empty">Select a visitor to view details.</div>
		</div>
		<div class="drawer-footer">
			<button type="button" class="drawer-btn-secondary" id="closeVisitorDrawerFooterBtn">Close</button>
			<button type="button" class="drawer-btn-primary" id="drawerResolveAlertBtn">Resolve Alert</button>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		const timeTarget = document.getElementById('currentTimeValue');
		const lastUpdateTarget = document.getElementById('lastUpdateTime');
		if (timeTarget && lastUpdateTarget) {
			const formatter = new Intl.DateTimeFormat('en-US', {
				hour: '2-digit',
				minute: '2-digit',
				hour12: true
			});

			const updateClock = () => {
				const now = new Date();
				const formattedTime = formatter.format(now);
				timeTarget.textContent = formattedTime;
				lastUpdateTarget.textContent = formattedTime;
			};

			updateClock();
			setInterval(updateClock, 30000);
		}

		const drawer = document.getElementById('visitorDrawer');
		const drawerOverlay = document.getElementById('visitorDrawerOverlay');
		const drawerBody = document.getElementById('visitorDrawerBody');
		const drawerResolveAlertBtn = document.getElementById('drawerResolveAlertBtn');

		function closeVisitorDrawer() {
			drawer?.classList.remove('active');
			drawerOverlay?.classList.remove('active');
		}

		function openVisitorDrawer() {
			drawer?.classList.add('active');
			drawerOverlay?.classList.add('active');
		}

		function formatTime(isoValue) {
			if (!isoValue) return '—';
			const date = new Date(isoValue);
			if (Number.isNaN(date.getTime())) return '—';
			return date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
		}

		function getDrawerStatusClass(statusText) {
			const value = String(statusText || '').toLowerCase();
			if (value.includes('arrived') || value.includes('inside')) return 'green';
			if (value.includes('pending')) return 'gray';
			if (value.includes('invalid') || value.includes('wrong') || value.includes('unresolved')) return 'red';
			return 'gray';
		}

		function escapeHtml(value) {
			return String(value ?? '')
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#039;');
		}

		function renderVisitorDrawer(details) {
			const visitor = details?.visitor || {};
			const visit = details?.visit || {};
			const route = Array.isArray(details?.expected_route) ? details.expected_route : [];
			const scans = Array.isArray(details?.scans) ? details.scans : [];
			const alert = details?.alert || null;

			const photoHtml = visitor.photo_url
				? `<img src="${escapeHtml(visitor.photo_url)}" alt="Visitor Photo">`
				: `<span>No Photo</span>`;

			const routeHtml = route.length > 0
				? route.map((item) => {
					const routeStatus = item.status || 'Pending';
					const isCompleted = String(routeStatus).toLowerCase().includes('arrived') || String(routeStatus).toLowerCase().includes('done');
					return `
						<div class="timeline-item ${isCompleted ? 'completed' : 'pending'}">
							<div class="timeline-dot"></div>
							<div class="timeline-content">
								<div class="timeline-head">
									<strong>${escapeHtml(item.expected_order ?? '-')} . ${escapeHtml(item.office_name ?? 'Unknown Office')}</strong>
									<span class="drawer-status-badge ${escapeHtml(getDrawerStatusClass(routeStatus))}">${escapeHtml(routeStatus)}</span>
								</div>
								<p>Arrived At: ${escapeHtml(formatTime(item.arrived_at))}</p>
							</div>
						</div>`;
				}).join('')
				: '<div class="drawer-empty">No expected office route found.</div>';

			const scansHtml = scans.length > 0
				? scans.map((scan) => {
					const validation = scan.validation_status || 'Unknown';
					const isValid = String(validation).toLowerCase().includes('valid') && !String(validation).toLowerCase().includes('invalid');
					return `
						<div class="scan-card ${isValid ? 'valid' : 'invalid'}">
							<div class="scan-head">
								<div>
									<h4>Scan #${escapeHtml(scan.scan_id ?? '-')}</h4>
									<p>${escapeHtml(scan.office_name ?? 'Unknown Office')}</p>
								</div>
								<span class="drawer-status-badge ${isValid ? 'green' : 'red'}">${escapeHtml(validation)}</span>
							</div>
							<div class="scan-meta">
								<span>Scanned by ${escapeHtml(scan.scanned_by ?? 'Unknown scanner')}</span>
								<span>${escapeHtml(formatTime(scan.scan_time))}</span>
							</div>
							<p class="scan-remarks">${escapeHtml(scan.remarks ?? '—')}</p>
						</div>`;
				}).join('')
				: '<div class="drawer-empty">No scan records found.</div>';

			const alertHtml = alert
				? `
					<div class="drawer-card drawer-alert-card">
						<div class="drawer-section-title">
							<h3>Alert Information</h3>
							<span class="drawer-status-badge orange">${escapeHtml(alert.severity || 'Medium')}</span>
						</div>
						<div class="drawer-section-title" style="margin-bottom:10px;">
							<h3 style="font-size:15px;">${escapeHtml(alert.alert_type || 'General Alert')}</h3>
							<span class="drawer-status-badge ${escapeHtml(getDrawerStatusClass(alert.status || 'Unresolved'))}">${escapeHtml(alert.status || 'Unresolved')}</span>
						</div>
						<p class="drawer-alert-message">${escapeHtml(alert.message || '—')}</p>
						<div class="drawer-info-grid two-col">
							<div><span>Alert ID</span><strong>${escapeHtml(alert.alert_id ?? '-')}</strong></div>
							<div><span>Created At</span><strong>${escapeHtml(formatTime(alert.created_at))}</strong></div>
							<div><span>Resolved At</span><strong>${escapeHtml(formatTime(alert.resolved_at))}</strong></div>
							<div><span>Resolved By</span><strong>${escapeHtml(alert.resolved_by || '—')}</strong></div>
						</div>
						<div class="drawer-full-info"><span>Resolution Notes</span><strong>${escapeHtml(alert.resolution_notes || '—')}</strong></div>
					</div>`
				: `
					<div class="drawer-card">
						<div class="drawer-section-title"><h3>Alert Information</h3></div>
						<div class="drawer-empty">No active alerts found.</div>
					</div>`;

			drawerBody.innerHTML = `
				<div class="drawer-card">
					<div class="profile-top">
						<div class="visitor-photo">${photoHtml}</div>
						<div class="profile-info">
							<h3>${escapeHtml(visitor.name || 'Unknown Visitor')}</h3>
							<span class="drawer-status-badge ${escapeHtml(getDrawerStatusClass(visitor.status || 'Still Inside'))}">${escapeHtml(visitor.status || 'Still Inside')}</span>
							<p>Visitor ID: <strong>${escapeHtml(visitor.visitor_id ?? '-')}</strong></p>
							<p>${escapeHtml(visitor.pass_number || '—')} • ${escapeHtml(visitor.control_number || '—')}</p>
						</div>
					</div>
					<div class="drawer-info-grid">
						<div><span>Contact Number</span><strong>${escapeHtml(visitor.contact_no || '—')}</strong></div>
						<div><span>Address</span><strong>${escapeHtml(visitor.address || '—')}</strong></div>
					</div>
				</div>

				<div class="drawer-card">
					<div class="drawer-section-title"><h3>Visit Information</h3></div>
					<div class="drawer-info-grid two-col">
						<div><span>Visit ID</span><strong>${escapeHtml(visit.visit_id ?? '-')}</strong></div>
						<div><span>Visit Type</span><strong>${escapeHtml(visit.visit_type || '—')}</strong></div>
						<div><span>Primary Office</span><strong>${escapeHtml(visit.primary_office || '—')}</strong></div>
						<div><span>Exit Status</span><strong>${escapeHtml(visit.exit_status || '—')}</strong></div>
						<div><span>Entry Time</span><strong>${escapeHtml(formatTime(visit.entry_time))}</strong></div>
						<div><span>Exit Time</span><strong>${escapeHtml(formatTime(visit.exit_time))}</strong></div>
						<div><span>Duration</span><strong>${escapeHtml(visit.duration_label || '—')}</strong></div>
						<div><span>Registered By</span><strong>${escapeHtml(visit.registered_by || '—')}</strong></div>
					</div>
					<div class="drawer-full-info"><span>Purpose / Reason</span><strong>${escapeHtml(visit.purpose_reason || '—')}</strong></div>
					<div class="drawer-full-info"><span>Destination Text</span><strong>${escapeHtml(visit.destination_text || '—')}</strong></div>
				</div>

				<div class="drawer-card">
					<div class="drawer-section-title"><h3>Office Route / Expected Offices</h3></div>
					<div class="timeline">${routeHtml}</div>
				</div>

				<div class="drawer-card">
					<div class="drawer-section-title"><h3>Scan Information</h3></div>
					<div class="scan-list">${scansHtml}</div>
				</div>

				${alertHtml}
			`;

			drawerResolveAlertBtn.style.display = alert ? 'inline-block' : 'none';
			drawerResolveAlertBtn.onclick = () => window.location.href = '/guard/alert';
		}

		async function openVisitorDetails(visitId) {
			drawerBody.innerHTML = '<div class="drawer-empty">Loading visitor details...</div>';
			openVisitorDrawer();
			try {
				const response = await fetch(`/guard/dashboard/visits/${encodeURIComponent(visitId)}/details`, {
					headers: { Accept: 'application/json' },
				});
				const payload = await response.json().catch(() => ({}));
				if (!response.ok) {
					throw new Error(payload.message || 'Unable to load visitor details.');
				}
				renderVisitorDrawer(payload);
			} catch (error) {
				drawerBody.innerHTML = `<div class="drawer-empty">${escapeHtml(error.message || 'Unable to load visitor details.')}</div>`;
				drawerResolveAlertBtn.style.display = 'none';
			}
		}

		document.getElementById('closeVisitorDrawerBtn')?.addEventListener('click', closeVisitorDrawer);
		document.getElementById('closeVisitorDrawerFooterBtn')?.addEventListener('click', closeVisitorDrawer);
		drawerOverlay?.addEventListener('click', closeVisitorDrawer);

		document.addEventListener('click', (event) => {
			const trigger = event.target.closest('.js-view-visitor');
			if (!trigger) return;
			const visitId = trigger.dataset.visitId;
			if (!visitId) return;
			openVisitorDetails(visitId);
		});
	</script>
</body>
</html>