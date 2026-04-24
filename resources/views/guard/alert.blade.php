<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Active Alerts</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
			width: 280px;
			background: var(--sidebar-bg);
			color: var(--text-white);
			padding: 12px 10px;
			display: flex;
			flex-direction: column;
			border-right: 1px solid rgba(0, 0, 0, 0.05);
		}

		.brand-row {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 6px 10px 2px;
			margin-bottom: 18px;
		}

		.brand-icon {
			width: 32px;
			height: 32px;
			background: #f6f8ff;
			border-radius: 6px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #273272;
			flex-shrink: 0;
		}

		.brand-title {
			margin: 0;
			font-size: 0;
			line-height: 1;
			font-weight: 800;
			letter-spacing: -0.02em;
			display: flex;
			gap: 4px;
			align-items: baseline;
		}

		.brand-title span:first-child {
			color: var(--text-yellow);
			font-size: 24px;
		}

		.brand-title span:last-child {
			color: #eef2ff;
			font-size: 22px;
			font-weight: 700;
		}

		.brand-subtitle {
			margin: 2px 0 0;
			color: var(--muted);
			font-size: 12px;
			white-space: nowrap;
		}

		.menu {
			display: flex;
			flex-direction: column;
			gap: 6px;
		}

		.menu-item {
			display: block;
			text-decoration: none;
			color: var(--text-white);
			padding: 9px 12px;
			border-radius: 6px;
			font-size: 16px;
			font-weight: 500;
			line-height: 1.2;
		}

		.menu-item .inner {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.menu-item svg {
			flex-shrink: 0;
			width: 22px;
			height: 22px;
		}

		.menu-item.active {
			background: var(--sidebar-bg-light);
			color: var(--text-yellow);
		}

		.menu-item:hover {
			background: rgba(255, 255, 255, 0.08);
		}

		.menu-group {
			display: flex;
			flex-direction: column;
			gap: 6px;
		}

		.menu-toggle {
			width: 100%;
			border: 0;
			background: transparent;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}

		.menu-toggle.active {
			background: var(--sidebar-bg-light);
			color: var(--text-yellow);
		}

		.menu-toggle .caret {
			width: 16px;
			height: 16px;
			transition: transform 0.2s ease;
		}

		.menu-group.open .menu-toggle .caret {
			transform: rotate(180deg);
		}

		.submenu {
			display: none;
			flex-direction: column;
			gap: 4px;
			margin-left: 34px;
		}

		.menu-group.open .submenu {
			display: flex;
		}

		.submenu-item {
			text-decoration: none;
			color: var(--text-white);
			font-size: 14px;
			font-weight: 500;
			padding: 6px 10px;
			border-radius: 6px;
		}

		.submenu-item:hover {
			background: rgba(255, 255, 255, 0.08);
		}

		.submenu-item.active {
			background: var(--sidebar-bg-light);
			color: var(--text-yellow);
		}

		.spacer {
			flex: 1;
		}

		.bottom {
			border-top: 2px solid var(--line);
			margin: 0 -10px;
			padding: 14px 10px 14px;
		}

		.admin-row {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			font-size: 16px;
			font-weight: 500;
			margin-bottom: 12px;
		}

		.admin-row svg {
			width: 20px;
			height: 20px;
		}

		.logout-wrap {
			display: flex;
			justify-content: center;
		}

		.logout-btn {
			border: 0;
			background: #ecedf2;
			color: #ff0000;
			font-weight: 700;
			font-size: 17px;
			border-radius: 14px;
			padding: 8px 18px;
			display: inline-flex;
			align-items: center;
			gap: 8px;
			cursor: pointer;
		}

		.logout-btn svg {
			width: 18px;
			height: 18px;
		}

		/* Register page sidebar parity */
		.layout {
			display: flex;
			height: 100vh;
			overflow: hidden;
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
			overflow-y: auto;
			overflow-x: hidden;
			z-index: 1000;
		}

		.sidebar::-webkit-scrollbar {
			width: 6px;
		}

		.sidebar::-webkit-scrollbar-thumb {
			background: rgba(255,255,255,0.18);
			border-radius: 10px;
		}

		.menu {
			display: block;
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
			color: inherit;
		}

		.brand-title {
			font-size: 28px;
			font-weight: 800;
			color: #ffd84d;
			line-height: 1;
			display: block;
			gap: 0;
			letter-spacing: 0;
		}

		.brand-subtitle {
			color: rgba(255,255,255,0.78);
			font-size: 12px;
			display: block;
			margin-top: 2px;
			white-space: normal;
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

		.collapse .submenu {
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
		}

		.alerts-page-wrap {
			padding: 24px;
		}

		.alerts-page-header {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			gap: 16px;
			flex-wrap: wrap;
			margin-bottom: 24px;
		}

		.alerts-page-title {
			margin: 0 0 6px;
			font-size: 30px;
			font-weight: 700;
			color: #111827;
		}

		.alerts-page-subtitle {
			margin: 0;
			font-size: 14px;
			color: #6b7280;
		}

		.alerts-page-meta {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.live-badge {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			background: #fff;
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

		.alerts-summary-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(220px, 280px));
			gap: 18px;
			margin-bottom: 22px;
			justify-content: center;
		}

		.alerts-summary-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 16px;
			padding: 20px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
			transition: 0.2s ease;
		}

		.alerts-summary-card:hover {
			transform: translateY(-2px);
		}

		.summary-top {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 14px;
		}

		.summary-label {
			font-size: 15px;
			font-weight: 600;
			color: #111827;
		}

		.summary-icon {
			width: 40px;
			height: 40px;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 16px;
		}

		.soft-red {
			background: #fdecec;
			color: #dc2626;
		}

		.soft-green {
			background: #eafaf1;
			color: #16a34a;
		}

		.soft-green-lite {
			background: #ecfdf3;
			color: #16a34a;
		}

		.summary-number {
			margin: 0 0 4px;
			font-size: 34px;
			font-weight: 700;
			line-height: 1.1;
		}

		.summary-text {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
		}

		.alerts-panel-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 20px;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
			margin-bottom: 22px;
			overflow: hidden;
		}

		.alerts-panel-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 16px;
			flex-wrap: wrap;
			padding: 22px;
			border-bottom: 1px solid #e5e7eb;
		}

		.alerts-panel-title-wrap {
			display: flex;
			align-items: flex-start;
			gap: 12px;
		}

		.section-icon {
			width: 42px;
			height: 42px;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 16px;
			flex-shrink: 0;
		}

		.alerts-panel-header h3 {
			margin: 0 0 4px;
			font-size: 18px;
			font-weight: 700;
		}

		.alerts-panel-header p {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
		}

		.alerts-panel-actions {
			display: flex;
			align-items: center;
			gap: 10px;
			flex-wrap: wrap;
		}

		.alerts-search-input,
		.alerts-filter-select {
			height: 42px;
			border: 1px solid #e5e7eb;
			background: #fff;
			border-radius: 10px;
			padding: 0 14px;
			font-size: 14px;
			color: #1f2937;
			outline: none;
		}

		.alerts-search-input {
			min-width: 220px;
		}

		.alerts-search-input:focus,
		.alerts-filter-select:focus {
			border-color: #c7d2fe;
			box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08);
		}

		.alerts-list {
			padding: 20px 22px 22px;
		}

		.alert-visitor-item {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 18px;
			background: #f9fafb;
			border: 1px solid #e5e7eb;
			border-radius: 16px;
			padding: 18px;
			transition: 0.2s ease;
			flex-wrap: wrap;
		}

		.alert-visitor-item.completed {
			background: #f0fdf4;
			border-color: #86efac;
		}

		.alert-visitor-item:hover {
			transform: translateY(-1px);
		}

		.alert-visitor-left {
			display: flex;
			align-items: center;
			gap: 14px;
		}

		.alert-avatar {
			width: 48px;
			height: 48px;
			border-radius: 50%;
			background: #eef2ff;
			color: #3949ab;
			font-weight: 700;
			font-size: 14px;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.alert-visitor-info h4 {
			margin: 0 0 4px;
			font-size: 18px;
			font-weight: 700;
			color: #111827;
		}

		.alert-visitor-info p {
			margin: 0 0 4px;
			font-size: 14px;
			color: #374151;
		}

		.alert-visitor-info span {
			font-size: 13px;
			color: #6b7280;
		}

		.alert-visitor-right {
			display: flex;
			align-items: center;
			gap: 12px;
			flex-wrap: wrap;
		}

		.alert-status-badge {
			display: inline-flex;
			align-items: center;
			padding: 8px 12px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 700;
		}

		.alert-status-badge.ready {
			background: #dcfce7;
			color: #15803d;
		}

		.alert-action-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			height: 40px;
			padding: 0 16px;
			border-radius: 10px;
			background: #111827;
			color: #fff;
			text-decoration: none;
			font-size: 13px;
			font-weight: 600;
			transition: 0.2s ease;
		}

		.alert-action-btn:hover {
			background: #1f2937;
			color: #fff;
		}

		.alerts-empty-card {
			background: #ffffff;
			border: 1px dashed #d1d5db;
			border-radius: 20px;
			padding: 32px 24px;
			text-align: center;
			box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
		}

		.alerts-empty-icon {
			width: 56px;
			height: 56px;
			margin: 0 auto 14px;
			border-radius: 50%;
			background: #f3f4f6;
			color: #6b7280;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 22px;
		}

		.alerts-empty-card h3 {
			margin: 0 0 8px;
			font-size: 18px;
			font-weight: 700;
			color: #111827;
		}

		.alerts-empty-card p {
			margin: 0;
			font-size: 14px;
			color: #6b7280;
		}

		.alert-modal {
			display: none;
			position: fixed;
			inset: 0;
			z-index: 60;
			background: rgba(15, 23, 42, 0.35);
			overflow-y: auto;
			padding: 14px;
		}

		.alert-modal-card {
			width: 940px;
			max-width: calc(100% - 24px);
			margin: 12px auto;
			background: #ffffff;
			border-radius: 16px;
			box-shadow: 0 10px 30px rgba(2, 6, 23, 0.2);
			overflow: hidden;
		}

		.alert-modal-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 16px 22px;
			border-bottom: 1px solid #e9edf3;
		}

		.alert-modal-title-wrap small {
			color: #64748b;
			font-size: 12px;
		}

		.alert-modal-title {
			margin: 0;
			font-size: 24px;
			line-height: 1.1;
			font-weight: 700;
			color: #0f172a;
		}

		.alert-modal-close {
			background: #eef2f7;
			border: 1px solid #d9e1ec;
			width: 34px;
			height: 34px;
			border-radius: 999px;
			font-size: 18px;
			line-height: 1;
			color: #64748b;
			cursor: pointer;
		}

		.alert-modal-body {
			padding: 18px 22px;
			background: #f8fafc;
			max-height: calc(100vh - 170px);
			overflow-y: auto;
		}

		.alert-info-card {
			background: #fff;
			border-radius: 14px;
			box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
			margin-bottom: 14px;
			overflow: hidden;
		}

		.alert-info-card .card-title {
			margin: 0;
			font-size: 18px;
			font-weight: 700;
			padding: 14px 16px;
			border-bottom: 1px solid #eef2f7;
		}

		.alert-info-card .card-content {
			padding: 14px 16px;
		}

		.detail-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 12px 16px;
		}

		.detail-grid .full { grid-column: 1 / -1; }
		.detail-label { font-size: 12px; color: #64748b; margin-bottom: 4px; }
		.detail-value { font-size: 14px; font-weight: 600; color: #0f172a; word-break: break-word; }

		.badge-pill { display: inline-flex; align-items: center; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
		.badge-danger { background: #fee2e2; color: #b91c1c; }
		.badge-success { background: #dcfce7; color: #166534; }
		.badge-high { background: #ffedd5; color: #c2410c; }
		.badge-medium { background: #fef3c7; color: #92400e; }
		.badge-low { background: #e5e7eb; color: #374151; }
		.badge-critical { background: #fee2e2; color: #b91c1c; }

		.alert-modal-footer {
			padding: 14px 22px;
			background: #fff;
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			border-top: 1px solid #e9edf3;
		}

		.resolve-btn {
			background: #2563eb;
			color: #fff;
			border: 0;
			border-radius: 10px;
			padding: 10px 18px;
			font-size: 14px;
			font-weight: 700;
			cursor: pointer;
		}

		.btn-secondary {
			background: #eef2f7;
			color: #334155;
			border: 1px solid #d9e1ec;
			border-radius: 10px;
			padding: 10px 18px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
		}

		.resolve-flow-modal {
			display: none;
			position: fixed;
			inset: 0;
			z-index: 70;
			background: rgba(15, 23, 42, 0.4);
			overflow-y: auto;
			padding: 14px;
		}

		.resolve-flow-card {
			width: 620px;
			max-width: calc(100% - 24px);
			margin: 24px auto;
			background: #ffffff;
			border-radius: 16px;
			box-shadow: 0 10px 30px rgba(2, 6, 23, 0.2);
			overflow: hidden;
		}

		.resolve-flow-body { padding: 18px 22px; }
		.resolve-summary { padding: 0 0 8px; }
		.modal-line { margin: 0 0 8px; font-size: 13px; line-height: 1.25; color: #475569; }
		.modal-line .value { font-weight: 600; color: #0f172a; }
		.resolve-severity-badge {
			display: inline-flex;
			align-items: center;
			padding: 4px 10px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 700;
			color: #92400e;
			background: #fef3c7;
		}
		.resolve-divider { border: 0; height: 1px; background: #e5e7eb; margin: 12px 0 14px; }
		.resolve-notes-label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 8px; color: #111827; }
		.resolve-notes-input { width: 100%; min-height: 88px; resize: vertical; border: 1px solid #d7e0eb; border-radius: 8px; padding: 9px 11px; font-size: 13px; }
		.resolve-warning { margin: 8px 0 0; font-size: 12px; color: #6b7280; }
		.resolve-flow-footer { padding: 14px 22px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #e9edf3; }
		.resolve-action-btn { border-radius: 999px; padding: 7px 16px; font-size: 12px; font-weight: 700; border: 0; cursor: pointer; }
		.resolve-action-btn.cancel { background: #6b7280; color: #fff; }
		.resolve-action-btn.confirm { background: #16a34a; color: #fff; }

		.g-alert-modal,
		.g-resolve-modal {
			position: fixed;
			inset: 0;
			background: rgba(2, 6, 23, 0.45);
			display: none;
			align-items: center;
			justify-content: center;
			z-index: 1100;
			padding: 16px;
		}

		.g-alert-modal.show,
		.g-resolve-modal.show {
			display: flex;
		}

		.g-modal-card {
			width: min(920px, 100%);
			background: #fff;
			border-radius: 16px;
			border: 1px solid #e5e7eb;
			box-shadow: 0 20px 45px rgba(15, 23, 42, 0.22);
			overflow: hidden;
		}

		.g-modal-head {
			padding: 14px 18px;
			border-bottom: 1px solid #e5e7eb;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}

		.g-modal-head h3 {
			margin: 0;
			font-size: 20px;
			font-weight: 700;
		}

		.g-modal-close {
			border: none;
			background: transparent;
			font-size: 24px;
			line-height: 1;
			color: #64748b;
			cursor: pointer;
		}

		.g-modal-body {
			padding: 16px 18px;
		}

		.g-details-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 14px 20px;
		}

		.g-line .k {
			font-size: 12px;
			color: #64748b;
			margin-bottom: 4px;
			display: block;
			text-transform: uppercase;
			letter-spacing: 0.04em;
		}

		.g-line .v {
			font-size: 15px;
			font-weight: 600;
			color: #0f172a;
			word-break: break-word;
		}

		.g-modal-foot {
			padding: 14px 18px;
			border-top: 1px solid #e5e7eb;
			display: flex;
			justify-content: flex-end;
			gap: 10px;
		}

		.g-btn {
			border: none;
			border-radius: 10px;
			padding: 10px 14px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
		}

		.g-btn.secondary {
			background: #eef2ff;
			color: #1e3a8a;
		}

		.g-btn.primary {
			background: #1d4ed8;
			color: #fff;
		}

		.g-notes {
			width: 100%;
			min-height: 120px;
			resize: vertical;
			border: 1px solid #d1d5db;
			border-radius: 12px;
			padding: 10px 12px;
			font-size: 14px;
		}

		@media (max-width: 1024px) {
			.sidebar {
				width: 100%;
				min-height: 100vh;
				position: relative;
			}

			.main {
				display: block;
				margin-left: 0;
			}
		}

		@media (max-width: 992px) {
			.alerts-summary-grid {
				grid-template-columns: 1fr 1fr;
				justify-content: stretch;
			}
		}

		@media (max-width: 768px) {
			.alerts-page-wrap {
				padding: 16px;
			}

			.alerts-summary-grid {
				grid-template-columns: 1fr;
			}

			.alerts-panel-actions {
				width: 100%;
				flex-direction: column;
			}

			.alerts-search-input,
			.alerts-filter-select {
				width: 100%;
			}

			.alert-visitor-item {
				align-items: flex-start;
			}

			.alert-visitor-right {
				width: 100%;
				justify-content: flex-start;
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

				<nav class="menu" aria-label="Sidebar Navigation">
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

						<a href="/guard/register?type=normal" class="quick-action-btn">
							<i class="bi bi-qr-code-scan"></i>
							<span>Register Visitor</span>
						</a>
					</div>
				</nav>
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

				<a href="{{ route('logout') }}"
				   class="logout-btn"
				   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
					<i class="bi bi-box-arrow-right"></i>
					<span>Logout</span>
				</a>

				<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
					@csrf
				</form>
			</div>
		</aside>

		<main class="main">
			<div class="alerts-page-wrap">
				<div class="alerts-page-header">
					<div>
						<h1 class="alerts-page-title">Active Alerts</h1>
						<p class="alerts-page-subtitle">Monitor visitor alerts and completed visits that are ready for exit processing.</p>
					</div>

					<div class="alerts-page-meta">
						<div class="live-badge">
							<span class="dot"></span>
							Live Status
						</div>
					</div>
				</div>

				<div class="alerts-summary-grid">
					<div class="alerts-summary-card">
						<div class="summary-top">
							<span class="summary-label">All Alerts</span>
							<div class="summary-icon soft-red">
								<i class="fas fa-circle-xmark"></i>
							</div>
						</div>
						<h2 class="summary-number">{{ number_format((int) ($unresolvedAlertsCount ?? 0)) }}</h2>
						<p class="summary-text">Unresolved alerts</p>
					</div>

					<div class="alerts-summary-card">
						<div class="summary-top">
							<span class="summary-label">Completed</span>
							<div class="summary-icon soft-green">
								<i class="fas fa-circle-check"></i>
							</div>
						</div>
						<h2 class="summary-number">{{ number_format((int) ($readyToExitCount ?? 0)) }}</h2>
						<p class="summary-text">Ready to exit</p>
					</div>
				</div>

				<div class="alerts-panel-card">
					<div class="alerts-panel-header">
						<div class="alerts-panel-title-wrap">
							<div class="section-icon soft-green-lite">
								<i class="fas fa-check-circle"></i>
							</div>
							<div>
								<h3>Completed Visitors</h3>
								<p>Visitors who have completed their business and are ready to exit.</p>
							</div>
						</div>

						<div class="alerts-panel-actions">
							<input type="text" class="alerts-search-input" placeholder="Search visitor..." />
							<select class="alerts-filter-select">
								<option>All</option>
								<option>Ready to Exit</option>
								<option>Completed</option>
								<option>Unresolved Alerts</option>
							</select>
						</div>
					</div>

					<div class="alerts-list">
						@forelse(($completedVisitors ?? []) as $visitor)
							<div class="alert-visitor-item completed">
								<div class="alert-visitor-left">
									<div class="alert-avatar">{{ $visitor['initials'] ?? 'NA' }}</div>
									<div class="alert-visitor-info">
										<h4>{{ $visitor['visitor_name'] ?? 'Unknown Visitor' }}</h4>
										<p>{{ $visitor['office_name'] ?? 'No office assigned' }} • {{ $visitor['pass_number'] ?? 'No pass/control number' }}</p>
										<span>Completed at: {{ $visitor['completed_at'] ?? '—' }}</span>
									</div>
								</div>

								<div class="alert-visitor-right">
									<span class="alert-status-badge ready">{{ $visitor['status'] ?? 'Ready to Exit' }}</span>
									<a href="/guard/exit" class="alert-action-btn">Process Exit</a>
								</div>
							</div>
						@empty
							<div class="alerts-empty-card">
								<div class="alerts-empty-icon">
									<i class="fas fa-user-check"></i>
								</div>
								<h3>No completed visitors yet</h3>
								<p>Visitors with "Ready to Exit" or "Completed" status will appear here.</p>
							</div>
						@endforelse
					</div>
				</div>

				<div class="alerts-panel-card">
					<div class="alerts-panel-header">
						<div class="alerts-panel-title-wrap">
							<div class="section-icon soft-red">
								<i class="fas fa-triangle-exclamation"></i>
							</div>
							<div>
								<h3>All Alerts (Unresolved)</h3>
								<p>Shows all unresolved alerts across all alert types.</p>
							</div>
						</div>
					</div>

					<div class="alerts-list">
						@forelse(($unresolvedAlerts ?? []) as $alert)
							<div class="alert-visitor-item">
								<div class="alert-visitor-left">
									<div class="alert-avatar">{{ strtoupper(substr((string) ($alert['visitor_name'] ?? 'NA'), 0, 2)) }}</div>
									<div class="alert-visitor-info">
										<h4>[{{ $alert['severity'] ?? 'High' }}] {{ $alert['alert_type'] ?? 'General Alert' }}</h4>
										<p>Visitor: {{ $alert['visitor_name'] ?? 'Unknown Visitor' }}</p>
										<p>Pass No: {{ $alert['pass_number'] ?? 'No pass/control number' }}</p>
										<p>Expected Office: {{ $alert['expected_office'] ?? 'No expected office' }}</p>
										<p>Scanned Office: {{ $alert['scanned_office'] ?? 'No scanned office' }}</p>
										<p>Message: {{ $alert['message'] ?? 'Visitor scanned at wrong office' }}</p>
										<p>Status: {{ $alert['status'] ?? 'Unresolved' }}</p>
										<span>Time: {{ $alert['time'] ?? '—' }}</span>
									</div>
								</div>

								<div class="alert-visitor-right">
									<button type="button" class="alert-action-btn view-btn" data-alert-id="{{ $alert['alert_id'] ?? 0 }}">View Details</button>
								</div>
							</div>
						@empty
							<div class="alerts-empty-card">
								<div class="alerts-empty-icon">
									<i class="fas fa-shield-check"></i>
								</div>
								<h3>No unresolved alerts</h3>
								<p>All alerts are currently resolved.</p>
							</div>
						@endforelse
					</div>
				</div>
			</div>
		</main>
	</div>

	<div id="alertModal" class="alert-modal">
		<div class="alert-modal-card" role="dialog" aria-modal="true" aria-labelledby="alertDetailsTitle">
			<div class="alert-modal-header">
				<div class="alert-modal-title-wrap">
					<h3 id="alertDetailsTitle" class="alert-modal-title">Alert Details</h3>
					<small>View complete alert information</small>
				</div>
				<button id="closeAlertBtn" class="alert-modal-close" type="button" aria-label="Close">×</button>
			</div>

			<div class="alert-modal-body">
				<section class="alert-info-card">
					<h4 class="card-title">Alert Information</h4>
					<div class="card-content detail-grid">
						<div><div class="detail-label">Alert ID</div><div class="detail-value" id="m_alert_id">-</div></div>
						<div><div class="detail-label">Alert Type</div><div class="detail-value" id="m_type">-</div></div>
						<div><div class="detail-label">Severity</div><div><span id="m_severity" class="badge-pill badge-medium">Medium</span></div></div>
						<div><div class="detail-label">Status</div><div><span id="m_status" class="badge-pill badge-danger">Unresolved</span></div></div>
						<div class="full"><div class="detail-label">Message</div><div class="detail-value" id="m_message">-</div></div>
						<div class="full"><div class="detail-label">Created At</div><div class="detail-value" id="m_created_at">-</div></div>
					</div>
				</section>

				<section class="alert-info-card">
					<h4 class="card-title">Visitor Information</h4>
					<div class="card-content detail-grid">
						<div><div class="detail-label">Visitor Name</div><div class="detail-value" id="m_visitor_name">-</div></div>
						<div><div class="detail-label">Pass Number</div><div class="detail-value" id="m_pass_no">-</div></div>
						<div><div class="detail-label">Control Number</div><div class="detail-value" id="m_control_no">-</div></div>
						<div><div class="detail-label">Contact Number</div><div class="detail-value" id="m_contact">-</div></div>
					</div>
				</section>

				<section class="alert-info-card">
					<h4 class="card-title">Visit Information</h4>
					<div class="card-content detail-grid">
						<div><div class="detail-label">Visit ID</div><div class="detail-value" id="m_visit_id">-</div></div>
						<div><div class="detail-label">Visit Type</div><div class="detail-value" id="m_visit_type">-</div></div>
						<div><div class="detail-label">Purpose</div><div class="detail-value" id="m_purpose">-</div></div>
						<div><div class="detail-label">Entry Time</div><div class="detail-value" id="m_entry_time">-</div></div>
						<div><div class="detail-label">Exit Time</div><div class="detail-value" id="m_exit_time">-</div></div>
						<div><div class="detail-label">Duration Minutes</div><div class="detail-value" id="m_duration">-</div></div>
						<div class="full"><div class="detail-label">Assigned Primary Office</div><div class="detail-value" id="m_primary_office">-</div></div>
					</div>
				</section>

				<section class="alert-info-card">
					<h4 class="card-title">Scan Information</h4>
					<div class="card-content detail-grid">
						<div><div class="detail-label">Scan ID</div><div class="detail-value" id="m_scan_id">-</div></div>
						<div><div class="detail-label">Scanned Office</div><div class="detail-value" id="m_scanned_office">-</div></div>
						<div><div class="detail-label">Scanned By Guard/Staff</div><div class="detail-value" id="m_scanned_by">-</div></div>
						<div><div class="detail-label">Scan Time</div><div class="detail-value" id="m_scan_time">-</div></div>
						<div><div class="detail-label">Validation Status</div><div class="detail-value" id="m_validation_status">-</div></div>
						<div><div class="detail-label">Remarks</div><div class="detail-value" id="m_remarks">-</div></div>
					</div>
				</section>

				<section class="alert-info-card">
					<h4 class="card-title">Resolution Information</h4>
					<div class="card-content">
						<div id="m_unresolved_text" class="detail-value" style="font-style: italic; color:#64748b;">Not yet resolved</div>
						<div id="m_resolved_details" style="display:none;">
							<div class="detail-grid">
								<div><div class="detail-label">Resolved By</div><div class="detail-value" id="m_resolved_by">-</div></div>
								<div><div class="detail-label">Resolved At</div><div class="detail-value" id="m_resolved_at">-</div></div>
								<div class="full"><div class="detail-label">Resolution Notes</div><div class="detail-value" id="m_resolution_notes">-</div></div>
							</div>
						</div>
					</div>
				</section>
			</div>

			<div class="alert-modal-footer">
				<button class="btn-secondary js-close-alert" type="button">Close</button>
				<button id="resolveAlertBtn" class="resolve-btn" type="button">Resolve Alert</button>
			</div>
		</div>
	</div>

	<div id="resolveModal" class="resolve-flow-modal">
		<div class="resolve-flow-card" role="dialog" aria-modal="true" aria-labelledby="resolveAlertTitle">
			<div class="alert-modal-header">
				<h3 id="resolveAlertTitle" class="alert-modal-title" style="font-size:22px;">Resolve Alert</h3>
				<button id="closeResolveModalBtn" class="alert-modal-close" type="button" aria-label="Close">×</button>
			</div>
			<div class="resolve-flow-body">
				<div class="resolve-summary">
					<p class="modal-line"><strong>Alert ID:</strong> <span id="r_alert_id" class="value">-</span></p>
					<p class="modal-line"><strong>Visitor:</strong> <span id="r_visitor" class="value">-</span></p>
					<p class="modal-line"><strong>Type:</strong> <span id="r_alert_type" class="value">-</span></p>
					<p class="modal-line"><strong>Severity:</strong> <span id="r_severity" class="resolve-severity-badge">Medium</span></p>
				</div>
				<hr class="resolve-divider">
				<label for="resolveNotes" class="resolve-notes-label">Resolution Notes</label>
				<textarea id="resolveNotes" class="resolve-notes-input" rows="4" placeholder="Enter how the alert was resolved..."></textarea>
				<p class="resolve-warning">⚠️ Please describe how this alert was resolved.</p>
			</div>
			<div class="resolve-flow-footer">
				<button id="cancelResolveBtn" class="resolve-action-btn cancel" type="button">Cancel</button>
				<button id="confirmResolveBtn" class="resolve-action-btn confirm" type="button">Resolve</button>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		const ALERTS = @json(($unresolvedAlerts ?? []));
		const csrfToken = '{{ csrf_token() }}';
		let pendingResolveAlertId = null;

		function formatDateTime(iso) {
			if (!iso) return ['-', ''];
			const d = new Date(iso);
			if (Number.isNaN(d.getTime())) return ['-', ''];
			return [
				d.toLocaleDateString(undefined, { month: 'short', day: '2-digit', year: 'numeric' }),
				d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' }),
			];
		}

		function getSeverityClass(severity) {
			const s = String(severity || '').toLowerCase();
			if (s === 'critical') return 'badge-pill badge-critical';
			if (s === 'high') return 'badge-pill badge-high';
			if (s === 'low') return 'badge-pill badge-low';
			return 'badge-pill badge-medium';
		}

		function getStatusClass(status) {
			return String(status || '').toLowerCase() === 'resolved' ? 'badge-pill badge-success' : 'badge-pill badge-danger';
		}

		function getResolveSeverityStyle(severity) {
			const s = String(severity || '').toLowerCase();
			if (s === 'critical') return { background: '#fee2e2', color: '#b91c1c' };
			if (s === 'high') return { background: '#ffedd5', color: '#c2410c' };
			if (s === 'low') return { background: '#e5e7eb', color: '#374151' };
			return { background: '#fef3c7', color: '#92400e' };
		}

		function openAlertModal(alertId) {
			const alert = ALERTS.find(a => String(a.alert_id) === String(alertId));
			if (!alert) return;
			const modal = document.getElementById('alertModal');
			if (!modal) return;

			const [createdDate, createdTime] = formatDateTime(alert.created_at);
			const [entryDate, entryTime] = formatDateTime(alert.entry_time);
			const [exitDate, exitTime] = formatDateTime(alert.exit_time);
			const [resolvedDate, resolvedTime] = formatDateTime(alert.resolved_at);
			const isResolved = String(alert.status || '').toLowerCase() === 'resolved';

			document.getElementById('m_alert_id').textContent = alert.alert_id || '-';
			document.getElementById('m_type').textContent = alert.alert_type || '-';
			document.getElementById('m_message').textContent = alert.message || '-';
			document.getElementById('m_created_at').textContent = createdDate === '-' ? '-' : `${createdDate} ${createdTime}`;
			const severityEl = document.getElementById('m_severity');
			severityEl.textContent = alert.severity || 'Medium';
			severityEl.className = getSeverityClass(alert.severity);
			const statusEl = document.getElementById('m_status');
			statusEl.textContent = alert.status || 'Unresolved';
			statusEl.className = getStatusClass(alert.status);

			document.getElementById('m_visitor_name').textContent = alert.visitor_name || '-';
			document.getElementById('m_pass_no').textContent = alert.pass_number || '-';
			document.getElementById('m_control_no').textContent = alert.control_number || '-';
			document.getElementById('m_contact').textContent = alert.contact_no || '-';

			document.getElementById('m_visit_id').textContent = alert.visit_id || '-';
			document.getElementById('m_visit_type').textContent = '-';
			document.getElementById('m_purpose').textContent = alert.purpose_reason || '-';
			document.getElementById('m_entry_time').textContent = entryDate === '-' ? '-' : `${entryDate} ${entryTime}`;
			document.getElementById('m_exit_time').textContent = exitDate === '-' ? '-' : `${exitDate} ${exitTime}`;
			document.getElementById('m_duration').textContent = alert.duration_minutes !== null && alert.duration_minutes !== undefined ? `${alert.duration_minutes} mins` : '-';
			document.getElementById('m_primary_office').textContent = alert.expected_office || '-';

			document.getElementById('m_scan_id').textContent = alert.scan_id || '-';
			document.getElementById('m_scanned_office').textContent = alert.scanned_office || '-';
			document.getElementById('m_scanned_by').textContent = alert.scanned_by || '-';
			document.getElementById('m_scan_time').textContent = alert.time || '-';
			document.getElementById('m_validation_status').textContent = isResolved ? 'Resolved' : 'Unresolved';
			document.getElementById('m_remarks').textContent = alert.scan_remarks || '-';

			document.getElementById('m_unresolved_text').style.display = isResolved ? 'none' : 'block';
			document.getElementById('m_resolved_details').style.display = isResolved ? 'block' : 'none';
			document.getElementById('m_resolved_by').textContent = isResolved ? 'Guard Officer' : '-';
			document.getElementById('m_resolved_at').textContent = resolvedDate === '-' ? '-' : `${resolvedDate} ${resolvedTime}`;
			document.getElementById('m_resolution_notes').textContent = isResolved ? (alert.resolution_notes || '-') : '-';

			const resolveBtn = document.getElementById('resolveAlertBtn');
			resolveBtn.dataset.alertId = alert.alert_id;
			resolveBtn.style.display = isResolved ? 'none' : 'inline-block';
			resolveBtn.disabled = isResolved;

			modal.style.display = 'block';
		}

		function closeAlertModal() {
			const modal = document.getElementById('alertModal');
			if (modal) modal.style.display = 'none';
		}

		function openResolveModal(alertId) {
			const alert = ALERTS.find(a => String(a.alert_id) === String(alertId));
			if (!alert) return;
			pendingResolveAlertId = alertId;
			document.getElementById('resolveNotes').value = '';
			document.getElementById('r_alert_id').textContent = alert.alert_id || '-';
			document.getElementById('r_visitor').textContent = alert.visitor_name || '-';
			document.getElementById('r_alert_type').textContent = alert.alert_type || '-';
			const severityEl = document.getElementById('r_severity');
			const severityText = alert.severity || 'Medium';
			const severityStyle = getResolveSeverityStyle(severityText);
			severityEl.textContent = severityText;
			severityEl.style.backgroundColor = severityStyle.background;
			severityEl.style.color = severityStyle.color;
			document.getElementById('resolveModal').style.display = 'block';
		}

		function closeResolveModal() {
			document.getElementById('resolveModal').style.display = 'none';
			pendingResolveAlertId = null;
		}

		async function resolveAlertClient(alertId, notes) {
			if (!notes) {
				alert('Please add Resolution Notes before resolving this alert.');
				return;
			}

			const response = await fetch(`/guard/alerts/${encodeURIComponent(alertId)}/resolve`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					'X-CSRF-TOKEN': csrfToken,
				},
				body: JSON.stringify({ resolution_notes: notes }),
			});

			const payload = await response.json().catch(() => ({}));
			if (!response.ok) {
				throw new Error(payload.message || 'Failed to resolve alert.');
			}
		}

		document.addEventListener('click', async function (e) {
			if (e.target && e.target.matches('.view-btn')) {
				openAlertModal(e.target.dataset.alertId);
			}
			if (e.target && (e.target.matches('#closeAlertBtn') || e.target.matches('.js-close-alert') || e.target.id === 'alertModal')) {
				closeAlertModal();
			}
			if (e.target && e.target.matches('#resolveAlertBtn')) {
				openResolveModal(e.target.dataset.alertId);
			}
			if (e.target && (e.target.matches('#closeResolveModalBtn') || e.target.matches('#cancelResolveBtn') || e.target.id === 'resolveModal')) {
				closeResolveModal();
			}
			if (e.target && e.target.matches('#confirmResolveBtn')) {
				if (!pendingResolveAlertId) return;
				const notes = (document.getElementById('resolveNotes')?.value || '').trim();
				e.target.disabled = true;
				e.target.textContent = 'Resolving...';
				try {
					await resolveAlertClient(pendingResolveAlertId, notes);
					closeResolveModal();
					closeAlertModal();
					window.location.reload();
				} catch (error) {
					alert(error.message || 'Unable to resolve alert at the moment.');
				} finally {
					e.target.disabled = false;
					e.target.textContent = 'Resolve';
				}
			}
		});
	</script>
</body>
</html>