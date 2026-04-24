<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Visitor Monitoring</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
			background: #eef2ff;
			color: #0f172a;
		}

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
			bottom: 0;
			height: 100vh;
			overflow-y: auto;
			z-index: 1000;
		}

		.sidebar::-webkit-scrollbar {
			width: 6px;
		}

		.sidebar::-webkit-scrollbar-thumb {
			background: rgba(255, 255, 255, 0.18);
			border-radius: 10px;
		}

		.sidebar-brand {
			gap: 12px;
			padding: 10px 10px 18px;
			margin-bottom: 10px;
			border-bottom: 1px solid rgba(255, 255, 255, 0.12);
		}

		.brand-icon {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			background: rgba(255, 255, 255, 0.14);
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
			color: rgba(255, 255, 255, 0.78);
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
			color: rgba(255, 255, 255, 0.55);
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
			background: rgba(255, 255, 255, 0.10);
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
			cursor: pointer;
		}

		.dropdown-arrow {
			transition: transform 0.25s ease;
			font-size: 13px;
		}

		.sidebar-dropdown.open .dropdown-arrow,
		.sidebar-toggle[aria-expanded="true"] .dropdown-arrow {
			transform: rotate(180deg);
		}

		.submenu {
			display: none;
			margin: 6px 0 8px 14px;
			padding-left: 14px;
			border-left: 1px solid rgba(255, 255, 255, 0.15);
		}

		.sidebar-dropdown.open .submenu {
			display: block;
		}

		.submenu-link {
			display: flex;
			align-items: center;
			gap: 10px;
			color: rgba(255, 255, 255, 0.88);
			text-decoration: none;
			padding: 10px 12px;
			border-radius: 10px;
			margin-bottom: 5px;
			font-size: 14px;
			transition: all 0.2s ease;
		}

		.submenu-link:hover {
			background: rgba(255, 255, 255, 0.10);
			color: #fff;
			transform: translateX(3px);
		}

		.submenu-link.active {
			background: rgba(255, 255, 255, 0.16);
			color: #ffd84d;
			font-weight: 600;
		}

		.sidebar-footer {
			padding-top: 16px;
			margin-top: 20px;
			border-top: 1px solid rgba(255, 255, 255, 0.12);
		}

		.admin-card {
			display: flex;
			align-items: center;
			gap: 12px;
			background: rgba(255, 255, 255, 0.08);
			border-radius: 14px;
			padding: 12px;
			margin-bottom: 12px;
		}

		.admin-avatar {
			width: 42px;
			height: 42px;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.15);
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
			color: rgba(255, 255, 255, 0.72);
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
			background: #f7f8ff;
			padding: 24px 32px;
			margin-left: 260px;
			min-height: 100vh;
			height: 100vh;
			overflow-y: auto;
		}

		.page-title {
			margin: 0;
			font-size: 28px;
			font-weight: 700;
			color: #0f172a;
		}

		.filters-card,
		.table-card,
		.panel-card {
			background: #ffffff;
			border-radius: 12px;
			border: 1px solid #e8ecf1;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
		}

		.filters-card {
			padding: 20px;
			margin-top: 16px;
		}

		.filters-row {
			display: grid;
			grid-template-columns: 1.2fr repeat(5, minmax(0, 1fr)) auto;
			gap: 10px;
			align-items: center;
		}

		.filters-label {
			margin: 0 0 8px;
			font-size: 18px;
			font-weight: 700;
			color: #111827;
		}

		.filter-input,
		.filter-select {
			height: 38px;
			border: 1px solid #d6dde8;
			outline: none;
			background: #f3f4f6;
			border-radius: 8px;
			padding: 0 12px;
			font-size: 14px;
			color: #1f2a44;
		}

		.filter-date {
			height: 38px;
			border: 1px solid #d6dde8;
			outline: none;
			background: #f3f4f6;
			border-radius: 8px;
			padding: 0 12px;
			font-size: 14px;
			color: #1f2a44;
		}

		.filter-input::placeholder {
			color: #94a3b8;
		}

		.search-wrap {
			position: relative;
		}

		.search-wrap svg {
			position: absolute;
			left: 14px;
			top: 50%;
			transform: translateY(-50%);
			width: 16px;
			height: 16px;
			color: #4256b4;
		}

		.search-wrap .filter-input {
			padding-left: 38px;
		}

		.filters-count {
			margin: 12px 0 0;
			font-size: 14px;
			color: #111827;
		}

		.summary-cards {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 12px;
			margin-top: 14px;
		}

		.summary-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 10px;
			padding: 12px 14px;
			min-height: 78px;
			display: flex;
			flex-direction: column;
			justify-content: center;
		}

		.summary-card-label {
			margin: 0;
			font-size: 12px;
			font-weight: 700;
			color: #64748b;
			letter-spacing: 0.02em;
			text-transform: uppercase;
		}

		.summary-card-value {
			margin: 4px 0 0;
			font-size: 28px;
			line-height: 1;
			font-weight: 800;
			color: #0f172a;
		}

		.summary-card.total .summary-card-value {
			color: #1d4ed8;
		}

		.summary-card.active .summary-card-value {
			color: #0369a1;
		}

		.summary-card.completed .summary-card-value {
			color: #15803d;
		}

		.summary-card.alerts .summary-card-value {
			color: #b91c1c;
		}

		.clear-filters-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			height: 38px;
			padding: 0 14px;
			border-radius: 8px;
			background: #eef2ff;
			color: #334155;
			border: 1px solid #d6deef;
			font-size: 13px;
			font-weight: 700;
			text-decoration: none;
			white-space: nowrap;
		}

		.clear-filters-btn:hover {
			background: #e2e8f0;
		}

		.clear-filters-btn.disabled {
			opacity: 0.5;
			pointer-events: none;
		}

		.filter-actions {
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.export-excel-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			height: 38px;
			padding: 0 14px;
			border-radius: 8px;
			background: #1d4ed8;
			color: #ffffff;
			border: 1px solid #1e40af;
			font-size: 13px;
			font-weight: 700;
			text-decoration: none;
			white-space: nowrap;
		}

		.export-excel-btn:hover {
			background: #1e40af;
		}

		.table-card {
			margin-top: 22px;
			overflow-x: auto;
		}

		.visitor-table {
			width: 100%;
			border-collapse: collapse;
			min-width: 1450px;
		}

		.visitor-table th {
			text-align: left;
			font-size: 15px;
			color: #1f2937;
			font-weight: 600;
			padding: 14px 16px;
			background: #ffffff;
		}

		.visitor-table td {
			font-size: 14px;
			color: #2f3640;
			padding: 14px 16px;
			border-top: 1px solid #d5d8de;
			vertical-align: middle;
		}

		.visitor-cell {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.avatar-icon {
			width: 18px;
			height: 18px;
			color: #000;
			flex-shrink: 0;
		}

		.visitor-name {
			font-size: 14px;
			font-weight: 700;
			line-height: 1.1;
		}

		.visitor-id {
			font-size: 12px;
			color: #111827;
		}

		.status-pill {
			display: inline-block;
			padding: 6px 10px;
			border-radius: 6px;
			font-size: 11px;
			line-height: 1;
		}

		.status-arrived {
			background: #c7f0dd;
			color: #15803d;
		}

		.status-transit {
			background: #bfeef7;
			color: #0e7490;
		}

		.status-completed {
			background: #e2e8f0;
			color: #334155;
		}

		.status-overstay {
			background: #fee2e2;
			color: #b91c1c;
		}

		.fetch-error {
			margin-top: 12px;
			padding: 10px 14px;
			border-radius: 8px;
			background: #fef2f2;
			border: 1px solid #fecaca;
			color: #b91c1c;
			font-size: 13px;
		}

		.empty-row {
			text-align: center;
			color: #64748b;
			font-size: 13px;
		}

		.pagination-wrap {
			display: flex;
			justify-content: flex-end;
			align-items: center;
			gap: 8px;
			padding: 12px 16px 16px;
		}

		.page-link {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-width: 34px;
			height: 34px;
			padding: 0 10px;
			border: 1px solid #dbe1ea;
			border-radius: 8px;
			background: #fff;
			color: #334155;
			font-size: 13px;
			font-weight: 600;
			text-decoration: none;
		}

		.page-link:hover {
			background: #f8fafc;
		}

		.page-link.active {
			background: #4256b4;
			color: #fff;
			border-color: #4256b4;
		}

		.page-link.disabled {
			opacity: 0.45;
			pointer-events: none;
		}

		.action-link {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			text-decoration: none;
			color: #273b9e;
			font-size: 14px;
			font-weight: 600;
		}

		.action-link svg {
			width: 16px;
			height: 16px;
		}

		/* Visitor details modal (based on provided example UI) */
		.visitor-detail-modal {
			display: none;
			position: fixed;
			inset: 0;
			z-index: 100;
			background: rgba(15, 23, 42, 0.45);
			overflow-y: auto;
			padding: 18px;
		}

		.visitor-detail-modal.open {
			display: block;
		}

		.visitor-detail-dialog {
			max-width: 1200px;
			margin: 0 auto;
			background: #fff;
			border-radius: 12px;
			box-shadow: 0 12px 32px rgba(2, 6, 23, 0.24);
			overflow: hidden;
		}

		.vd-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 14px;
			padding: 16px 20px;
			border-bottom: 1px solid #e5e7eb;
		}

		.vd-header-left h2 {
			margin: 0;
			font-size: 28px;
			line-height: 1.1;
		}

		.vd-header-meta {
			margin-top: 4px;
			font-size: 13px;
			color: #64748b;
		}

		.vd-close {
			border: 0;
			background: #f1f5f9;
			color: #334155;
			padding: 8px 12px;
			border-radius: 8px;
			font-weight: 600;
			cursor: pointer;
		}

		.vd-status-badge {
			display: inline-flex;
			align-items: center;
			padding: 6px 10px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 700;
		}

		.vd-status-arrived { background: #dcfce7; color: #166534; }
		.vd-status-transit { background: #dbeafe; color: #1d4ed8; }
		.vd-status-completed { background: #e2e8f0; color: #334155; }
		.vd-status-overstay { background: #fee2e2; color: #b91c1c; }
		.vd-status-pending { background: #fef3c7; color: #92400e; }

		.vd-body {
			padding: 18px 20px 20px;
			max-height: calc(100vh - 90px);
			overflow-y: auto;
		}

		.vd-grid {
			display: grid;
			grid-template-columns: minmax(280px, 1fr) minmax(400px, 2fr);
			gap: 16px;
		}

		.vd-card {
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			overflow: hidden;
			background: #fff;
		}

		.vd-card-title {
			margin: 0;
			padding: 12px 14px;
			font-size: 14px;
			font-weight: 700;
			background: #f8fafc;
			border-bottom: 1px solid #e2e8f0;
		}

		.vd-card-body {
			padding: 14px;
		}

		.vd-photo {
			height: 180px;
			border: 1px dashed #cbd5e1;
			border-radius: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 13px;
			color: #64748b;
			margin-bottom: 12px;
			background: #f8fafc;
		}

		.vd-photo.has-image {
			border: 0;
			background: transparent;
		}

		.vd-photo img {
			max-width: 100%;
			max-height: 100%;
			object-fit: contain;
			border-radius: 8px;
		}

		.vd-table {
			width: 100%;
			border-collapse: collapse;
		}

		.vd-table th,
		.vd-table td {
			font-size: 13px;
			padding: 8px 6px;
			border-bottom: 1px solid #e2e8f0;
			text-align: left;
			vertical-align: top;
		}

		.vd-table th {
			width: 40%;
			color: #334155;
			font-weight: 700;
		}

		.vd-table-sm th,
		.vd-table-sm td {
			font-size: 12px;
			padding: 7px 6px;
		}

		.vd-stack {
			display: grid;
			gap: 16px;
		}

		.vd-badge {
			display: inline-flex;
			align-items: center;
			padding: 4px 8px;
			border-radius: 999px;
			font-size: 11px;
			font-weight: 700;
		}

		.vd-badge-success { background: #dcfce7; color: #166534; }
		.vd-badge-warning { background: #fef3c7; color: #92400e; }
		.vd-badge-danger { background: #fee2e2; color: #991b1b; }
		.vd-badge-secondary { background: #e2e8f0; color: #334155; }

		.vd-alert-box {
			border: 1px solid #e2e8f0;
			border-radius: 10px;
			padding: 10px;
		}

		.vd-alert-box p {
			margin: 0 0 6px;
			font-size: 12px;
			line-height: 1.45;
		}

		.vd-alert-box p:last-child {
			margin-bottom: 0;
		}

		@media (max-width: 980px) {
			.vd-grid {
				grid-template-columns: 1fr;
			}

			.vd-header {
				flex-wrap: wrap;
			}
		}

		.bottom-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 12px;
			margin-top: 22px;
			align-items: stretch;
		}

		.right-stack {
			display: grid;
			grid-template-columns: 1fr;
			grid-template-rows: 1fr 1fr;
			gap: 12px;
			height: 100%;
			min-height: 360px;
		}

		.panel-card {
			overflow: hidden;
		}

		.recent-panel,
		.stack-panel {
			display: flex;
			flex-direction: column;
			height: 100%;
		}

		.recent-panel {
			min-height: 360px;
		}

		.panel-title {
			margin: 0;
			padding: 14px 16px;
			font-size: 20px;
			font-weight: 600;
			color: #1f2937;
			border-bottom: 1px solid #d5d8de;
		}

		.recent-list,
		.office-list {
			list-style: none;
			margin: 0;
			padding: 0;
		}

		.recent-panel .recent-list {
			flex: 1;
			display: flex;
			flex-direction: column;
		}

		.recent-item {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			padding: 14px 16px;
			border-bottom: 1px solid #d5d8de;
			flex: 0 0 auto;
			min-height: 74px;
		}

		.recent-item:last-child {
			border-bottom: 0;
		}

		.recent-left {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.recent-meta {
			font-size: 12px;
			color: #4b5563;
			margin-top: 2px;
		}

		.office-item {
			padding: 12px 16px;
			border-bottom: 1px solid #d5d8de;
		}

		.office-item:last-child {
			border-bottom: 0;
		}

		.office-row {
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-size: 12px;
			color: #334155;
			margin-bottom: 6px;
		}

		.office-label {
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		.office-label svg {
			width: 12px;
			height: 12px;
			color: #5b66a7;
		}

		.bar-track {
			height: 4px;
			background: #e5e7eb;
			border-radius: 999px;
			overflow: hidden;
		}

		.bar-fill {
			height: 100%;
			background: #111827;
			border-radius: 999px;
		}

		.bar-fill.w-33 {
			width: 33%;
		}

		.correct-list {
			list-style: none;
			margin: 0;
			padding: 0;
			flex: 1;
			display: flex;
			flex-direction: column;
		}

		.correct-item {
			padding: 12px 16px;
			border-bottom: 1px solid #d5d8de;
			flex: 0 0 auto;
			min-height: 70px;
		}

		.correct-item:last-child {
			border-bottom: 0;
		}

		.correct-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 8px;
			font-size: 13px;
			color: #111827;
			font-weight: 600;
		}

		.correct-meta {
			margin-top: 4px;
			font-size: 12px;
			color: #4b5563;
		}

		.correct-pill {
			display: inline-flex;
			align-items: center;
			padding: 5px 8px;
			border-radius: 999px;
			background: #dcfce7;
			color: #15803d;
			font-size: 10px;
			font-weight: 700;
			letter-spacing: 0.02em;
		}

		@media (max-width: 1180px) {
			.filters-row {
				grid-template-columns: 1fr;
			}

			.summary-cards {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}

			.bottom-grid {
				grid-template-columns: 1fr;
			}

			.right-stack {
				grid-template-rows: auto;
				height: auto;
			}

			.recent-item,
			.correct-item {
				flex: initial;
			}
		}

		@media (max-width: 1024px) {
			.sidebar {
				width: 100%;
				min-height: 100vh;
			}

			.main {
				display: none;
			}
		}

		@media (max-width: 480px) {
			.summary-cards {
				grid-template-columns: 1fr;
			}

			.sidebar-link,
			.admin-info h6,
			.logout-btn {
				font-size: 16px;
			}

			.brand-title {
				font-size: 20px;
			}
		}
	</style>
</head>
<body>
	<div class="layout">
		<aside class="sidebar d-flex flex-column justify-content-between">
			<div>
				<div class="sidebar-brand d-flex align-items-center">
					<div class="brand-icon">
						<i class="bi bi-person-badge-fill"></i>
					</div>
					<div>
						<h4 class="brand-title mb-0">SVMS Admin</h4>
						<small class="brand-subtitle">Smart Visitor Monitoring System</small>
					</div>
				</div>

				<div class="sidebar-section">
					<p class="sidebar-label">MAIN</p>
					<a href="/admin/dashboard" class="sidebar-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-grid-1x2-fill"></i></span>
						<span class="sidebar-text">Dashboard</span>
					</a>
				</div>

				<div class="sidebar-section">
					<p class="sidebar-label">MONITORING</p>
					<a href="/admin/visitor" class="sidebar-link {{ request()->is('admin/visitor*') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-people-fill"></i></span>
						<span class="sidebar-text">Visitor Monitoring</span>
					</a>
					<a href="/admin/alerts" class="sidebar-link {{ request()->is('admin/alerts*') ? 'active' : '' }}">
						<span class="sidebar-icon"><i class="bi bi-exclamation-triangle-fill"></i></span>
						<span class="sidebar-text">Alerts</span>
						<span class="sidebar-badge">5</span>
					</a>
				</div>

				@php
					$isUserMgmtOpen = request()->is('admin/user/guards*') || request()->is('admin/user/offices*');
				@endphp
				<div class="sidebar-section">
					<p class="sidebar-label">MANAGEMENT</p>
					<div class="sidebar-dropdown {{ $isUserMgmtOpen ? 'open' : '' }}" id="userMenuGroup">
						<button class="sidebar-link sidebar-toggle {{ $isUserMgmtOpen ? 'active' : '' }}"
							type="button"
							id="userMenuToggle"
							aria-expanded="{{ $isUserMgmtOpen ? 'true' : 'false' }}">
							<span class="d-flex align-items-center gap-2">
								<span class="sidebar-icon"><i class="bi bi-person-lines-fill"></i></span>
								<span class="sidebar-text">User Management</span>
							</span>
							<span class="dropdown-arrow"><i class="bi bi-chevron-down"></i></span>
						</button>
						<div class="submenu" id="userSubmenu">
							<a href="/admin/user/guards" class="submenu-link {{ request()->is('admin/user/guards*') ? 'active' : '' }}">
								<i class="bi bi-shield-fill-check"></i>
								<span>Guards</span>
							</a>
							<a href="/admin/user/offices" class="submenu-link {{ request()->is('admin/user/offices*') ? 'active' : '' }}">
								<i class="bi bi-building"></i>
								<span>Offices</span>
							</a>
						</div>
					</div>
				</div>
			</div>

			<div class="sidebar-footer">
				<div class="admin-card">
					<div class="admin-avatar">
						<i class="bi bi-person-circle"></i>
					</div>
					@php
						$sidebarAuthUser = auth()->user();
						$sidebarDisplayName = trim(((string) ($sidebarAuthUser->first_name ?? '')).' '.((string) ($sidebarAuthUser->last_name ?? '')));
						$sidebarDisplayName = $sidebarDisplayName !== ''
							? $sidebarDisplayName
							: ((string) ($sidebarAuthUser->name ?? $sidebarAuthUser->email ?? 'User'));
						$sidebarRoleLabel = ((int) ($sidebarAuthUser->role_id ?? 0) === 4) ? 'Guard' : 'System Administrator';
					@endphp
					<div class="admin-info">
						<h6 class="mb-0">{{ $sidebarDisplayName }}</h6>
						<small>{{ $sidebarRoleLabel }}</small>
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
			<h1 class="page-title">Visitor Monitoring</h1>
			@if (!empty($fetchError))
				<p class="fetch-error">{{ $fetchError }}</p>
			@endif

			<div class="filters-card">
				<p class="filters-label">Filters:</p>
				@php
					$hasActiveFilters = !empty($filters['search'])
						|| !empty($filters['status'])
						|| !empty($filters['office'])
						|| !empty($filters['visit_type'])
						|| !empty($filters['date_from'])
						|| !empty($filters['date_to']);
					$exportQuery = array_filter($filters ?? [], fn ($value) => $value !== null && $value !== '');
				@endphp
				<form method="GET" action="{{ route('admin.visitor') }}">
					<div class="filters-row">
						<div class="search-wrap">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2.5"/>
								<path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
							</svg>
							<input class="filter-input" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search Visitor" aria-label="Search visitor">
						</div>
						<select class="filter-select" name="status" aria-label="Filter by status" onchange="this.form.submit()">
							<option value="">Status</option>
							@foreach(($statusOptions ?? []) as $statusOption)
								<option value="{{ $statusOption }}" @selected(($filters['status'] ?? '') === $statusOption)>{{ $statusOption }}</option>
							@endforeach
						</select>
						<select class="filter-select" name="office" aria-label="Filter by office" onchange="this.form.submit()">
							<option value="">Office</option>
							@foreach(($officeOptions ?? []) as $officeOption)
								<option value="{{ $officeOption }}" @selected(($filters['office'] ?? '') === $officeOption)>{{ $officeOption }}</option>
							@endforeach
						</select>
						<select class="filter-select" name="visit_type" aria-label="Filter by visit type" onchange="this.form.submit()">
							<option value="">Visit Type</option>
							@foreach(($visitTypeOptions ?? []) as $visitTypeOption)
								<option value="{{ $visitTypeOption }}" @selected(($filters['visit_type'] ?? '') === $visitTypeOption)>{{ $visitTypeOption }}</option>
							@endforeach
						</select>
						<input class="filter-date" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" aria-label="Date from" onchange="this.form.submit()">
						<input class="filter-date" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" aria-label="Date to" onchange="this.form.submit()">
						<div class="filter-actions">
							<a href="{{ route('admin.visitor.export', $exportQuery) }}" class="export-excel-btn" aria-label="Export visitors to Excel">Export Excel</a>
							<a href="{{ route('admin.visitor') }}" class="clear-filters-btn {{ $hasActiveFilters ? '' : 'disabled' }}" aria-label="Clear filters">Clear</a>
						</div>
					</div>
				</form>
				<p class="filters-count">Showing {{ ($rows ?? collect())->count() }} of {{ $filteredCount ?? 0 }} filtered visitors ({{ $totalRows ?? 0 }} total)</p>
			</div>

			<div class="summary-cards" aria-label="Visitor summary cards">
				@foreach(($summaryCards ?? []) as $card)
					<div class="summary-card {{ $card['modifier'] ?? '' }}">
						<p class="summary-card-label">{{ $card['label'] ?? '' }}</p>
						<p class="summary-card-value">{{ number_format((int) ($card['value'] ?? 0)) }}</p>
					</div>
				@endforeach
			</div>

			<div class="table-card">
				<table class="visitor-table" aria-label="Visitors table">
					<thead>
						<tr>
							<th>Visitor</th>
							<th>Control #</th>
							<th>Contact No.</th>
							<th>Visit Type</th>
							<th>Purpose</th>
							<th>Destination</th>
							<th>Time In</th>
							<th>Duration</th>
							<th>Status</th>
							<th>Alert</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						@forelse(($rows ?? []) as $row)
							<tr>
								<td>
									<div class="visitor-cell">
										<svg class="avatar-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
											<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z"/>
										</svg>
										<div>
											<div class="visitor-name">{{ $row['visitor_name'] }}</div>
											<div class="visitor-id">{{ $row['pass_number'] }}</div>
										</div>
									</div>
								</td>
								<td>{{ $row['control_number'] }}</td>
								<td>{{ $row['contact_no'] }}</td>
								<td>{{ $row['visit_type'] }}</td>
								<td>{{ $row['purpose'] }}</td>
								<td>{{ $row['destination'] }}</td>
								<td>{{ $row['entry_time_label_date'] }}<br>{{ $row['entry_time_label_time'] }}</td>
								<td>{{ $row['duration_label'] }}</td>
								<td><span class="status-pill {{ $row['status_class'] }}">{{ $row['status'] }}</span></td>
								<td>{{ $row['alert'] }}</td>
								<td>
									<a
										href="#"
										class="action-link js-open-visitor-modal"
										data-visitor-id="{{ $row['visitor_id'] ?? '—' }}"
										data-visitor-name="{{ $row['visitor_name'] ?? '—' }}"
										data-pass-number="{{ $row['pass_number'] ?? '—' }}"
										data-control-number="{{ $row['control_number'] ?? '—' }}"
										data-contact-no="{{ $row['contact_no'] ?? '—' }}"
										data-address="{{ $row['address'] ?? '—' }}"
										data-photo-url="{{ $row['visitor_photo_with_id_url'] ?? '' }}"
										data-visit-id="{{ $row['visit_id'] ?? '—' }}"
										data-visit-type="{{ $row['visit_type'] ?? '—' }}"
										data-purpose="{{ $row['purpose'] ?? '—' }}"
										data-primary-office="{{ $row['destination'] ?? '—' }}"
										data-entry-time="{{ trim(($row['entry_time_label_date'] ?? '') . ' ' . ($row['entry_time_label_time'] ?? '')) }}"
										data-exit-time="{{ $row['exit_time_label'] ?? '—' }}"
										data-duration="{{ $row['duration_label'] ?? '—' }}"
										data-exit-status="{{ $row['exit_status'] ?? '—' }}"
										data-registered-by="{{ $row['registered_by_guard'] ?? '—' }}"
										data-status="{{ $row['status'] ?? 'Pending' }}"
										data-alert="{{ $row['alert'] ?? 'None' }}"
										data-alert-id="{{ $row['alert_id'] ?? '' }}"
										data-alert-type="{{ $row['alert_type'] ?? 'None' }}"
										data-alert-severity="{{ $row['alert_severity'] ?? '—' }}"
										data-alert-message="{{ $row['alert_message'] ?? '—' }}"
										data-alert-status="{{ $row['alert_status'] ?? '—' }}"
										data-alert-created-at="{{ $row['alert_created_at'] ?? '—' }}"
										data-alert-resolved-at="{{ $row['alert_resolved_at'] ?? '—' }}"
										data-alert-resolved-by="{{ $row['alert_resolved_by'] ?? '—' }}"
										data-alert-resolution-notes="{{ $row['alert_resolution_notes'] ?? '—' }}"
										data-scan-id="{{ $row['scan_id'] ?? '—' }}"
										data-scan-time="{{ $row['scan_time_label'] ?? '—' }}"
										data-scan-remarks="{{ $row['scan_remarks'] ?? '—' }}"
										data-scanned-office="{{ $row['scanned_office'] ?? '—' }}"
										data-scanned-by="{{ $row['scanned_by'] ?? '—' }}"
										data-validation-status="{{ $row['validation_status'] ?? 'Unknown' }}"
										data-office-route='@json($row['office_route'] ?? [])'
									>
										<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z" stroke="currentColor" stroke-width="2"/>
											<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
										</svg>
										View
									</a>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="11" class="empty-row">No visitor records found.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
				@if(($rows ?? null) && method_exists($rows, 'lastPage') && $rows->lastPage() > 1)
					<div class="pagination-wrap" aria-label="Visitor table pagination">
						<a class="page-link {{ $rows->onFirstPage() ? 'disabled' : '' }}" href="{{ $rows->onFirstPage() ? '#' : $rows->previousPageUrl() }}">Prev</a>

						@for($page = 1; $page <= $rows->lastPage(); $page++)
							<a class="page-link {{ $rows->currentPage() === $page ? 'active' : '' }}" href="{{ $rows->url($page) }}">{{ $page }}</a>
						@endfor

						<a class="page-link {{ $rows->hasMorePages() ? '' : 'disabled' }}" href="{{ $rows->hasMorePages() ? $rows->nextPageUrl() : '#' }}">Next</a>
					</div>
				@endif
			</div>

			<div class="bottom-grid">
				<section class="panel-card recent-panel">
					<h2 class="panel-title">Recent Visitors</h2>
					<ul class="recent-list">
						@forelse(($recentVisitors ?? []) as $recent)
							<li class="recent-item">
								<div class="recent-left">
									<svg class="avatar-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z"/>
									</svg>
									<div>
										<div>{{ $recent['visitor_name'] }}</div>
										<div class="recent-meta">{{ $recent['destination'] }} • {{ $recent['time_label'] }}</div>
									</div>
								</div>
								<span class="status-pill {{ $recent['status_class'] }}">{{ $recent['status'] }}</span>
							</li>
						@empty
							<li class="recent-item">
								<div class="recent-left">
									<div class="recent-meta">No recent visitor data.</div>
								</div>
							</li>
						@endforelse
					</ul>
				</section>

				<div class="right-stack">
					<section class="panel-card stack-panel">
						<h2 class="panel-title">Active by Office</h2>
						<ul class="office-list">
							@forelse(($activeByOffice ?? []) as $officeActivity)
								<li class="office-item">
									<div class="office-row">
										<span class="office-label">
											<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M7 21h10M9 21V8h6v13M8 8h8M8 4h8v4H8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
											</svg>
											{{ $officeActivity['office_name'] }}
										</span>
										<span>{{ $officeActivity['count'] }}</span>
									</div>
									<div class="bar-track">
										@php
											$barWidth = (int) round((($officeActivity['count'] ?? 0) / max(1, ($maxOfficeCount ?? 1))) * 100);
										@endphp
										<div class="bar-fill" style="width: {{ max(8, $barWidth) }}%;"></div>
									</div>
								</li>
							@empty
								<li class="office-item">
									<div class="office-row">
										<span class="office-label">No active office data</span>
									</div>
								</li>
							@endforelse
						</ul>
					</section>

					<section class="panel-card stack-panel">
						<h2 class="panel-title">Correct Office Scans</h2>
						<ul class="correct-list" aria-label="Correct office scan results">
							@forelse(($correctOfficeScans ?? []) as $scan)
								<li class="correct-item">
									<div class="correct-head">
										<span>{{ $scan['visitor_name'] }}</span>
										<span class="correct-pill">{{ $scan['result'] }}</span>
									</div>
									<p class="correct-meta">{{ $scan['destination'] }} • {{ $scan['control_number'] }} • {{ $scan['time_label'] }}</p>
								</li>
							@empty
								<li class="correct-item">
									<p class="correct-meta">No matched office scans yet.</p>
								</li>
							@endforelse
						</ul>
					</section>
				</div>
			</div>
		</main>
	</div>

	<div id="visitorDetailModal" class="visitor-detail-modal" aria-hidden="true">
		<div class="visitor-detail-dialog" role="dialog" aria-modal="true" aria-labelledby="vdTitle">
			<div class="vd-header">
				<div class="vd-header-left">
					<h2 id="vdTitle">Visitor Details</h2>
					<div class="vd-header-meta"><span id="vdHeaderName">—</span> | <span id="vdHeaderControl">—</span></div>
				</div>
				<div>
					<span id="vdStatusBadge" class="vd-status-badge vd-status-pending">Pending</span>
				</div>
				<button type="button" id="vdClose" class="vd-close">Close</button>
			</div>

			<div class="vd-body">
				<div class="vd-grid">
					<div class="vd-stack">
						<section class="vd-card">
							<h3 class="vd-card-title">Visitor Information</h3>
							<div class="vd-card-body">
								<div class="vd-photo" id="vdPhoto">No Photo Available</div>
								<table class="vd-table">
									<tbody>
										<tr><th>Visitor ID</th><td id="vdVisitorId">—</td></tr>
										<tr><th>Full Name</th><td id="vdVisitorName">—</td></tr>
										<tr><th>Pass Number</th><td id="vdPassNumber">—</td></tr>
										<tr><th>Control Number</th><td id="vdControlNumber">—</td></tr>
										<tr><th>Contact Number</th><td id="vdContactNo">—</td></tr>
										<tr><th>Address</th><td id="vdAddress">—</td></tr>
									</tbody>
								</table>
							</div>
						</section>

						<section class="vd-card">
							<h3 class="vd-card-title">Alert Information</h3>
							<div class="vd-card-body">
								<div id="vdNoAlertsBox" class="vd-badge vd-badge-success">No alerts found.</div>
								<div id="vdAlertBox" class="vd-alert-box" style="display:none; margin-top:10px;">
									<p><strong>Alert #</strong><span id="vdAlertId">3001</span></p>
									<p><strong>Status:</strong> <span id="vdAlertStatusBadge" class="vd-badge vd-badge-danger">Unresolved</span></p>
									<p><strong>Type:</strong> <span id="vdAlertType">Wrong Office</span></p>
									<p><strong>Severity:</strong> <span id="vdAlertSeverityBadge" class="vd-badge vd-badge-warning">Medium</span></p>
									<p><strong>Message:</strong> <span id="vdAlertMessage">Visitor scanned at wrong office.</span></p>
									<p><strong>Created At:</strong> <span id="vdAlertCreatedAt">—</span></p>
									<p><strong>Resolved At:</strong> <span id="vdResolvedAt">—</span></p>
									<p><strong>Resolved By:</strong> <span id="vdResolvedBy">—</span></p>
									<p><strong>Resolution Notes:</strong> <span id="vdResolutionNotes">—</span></p>
								</div>
							</div>
						</section>
					</div>

					<div class="vd-stack">
						<section class="vd-card">
							<h3 class="vd-card-title">Visit Information</h3>
							<div class="vd-card-body">
								<table class="vd-table">
									<tbody>
										<tr><th>Visit ID</th><td id="vdVisitId">—</td></tr>
										<tr><th>Visit Type</th><td id="vdVisitType">—</td></tr>
										<tr><th>Purpose</th><td id="vdPurpose">—</td></tr>
										<tr><th>Primary Office</th><td id="vdPrimaryOffice">—</td></tr>
										<tr><th>Entry Time</th><td id="vdEntryTime">—</td></tr>
										<tr><th>Exit Time</th><td id="vdExitTime">—</td></tr>
										<tr><th>Duration</th><td id="vdDuration">—</td></tr>
										<tr><th>Exit Status</th><td id="vdExitStatus">Still Inside</td></tr>
										<tr><th>Registered By Guard</th><td id="vdRegisteredByGuard">—</td></tr>
									</tbody>
								</table>
							</div>
						</section>

						<section class="vd-card">
							<h3 class="vd-card-title">Office Route / Expected Offices</h3>
							<div class="vd-card-body">
								<table class="vd-table vd-table-sm">
									<thead>
										<tr>
											<th>Expected Office</th>
											<th>Order</th>
											<th>Status</th>
											<th>Arrived At</th>
										</tr>
									</thead>
									<tbody id="vdOfficeRouteBody"></tbody>
								</table>
							</div>
						</section>

						<section class="vd-card">
							<h3 class="vd-card-title">Scan Information</h3>
							<div class="vd-card-body">
								<table class="vd-table vd-table-sm">
									<thead>
										<tr>
											<th>Scan ID</th>
											<th>Scanned Office</th>
											<th>Scanned By</th>
											<th>Scan Time</th>
											<th>Validation</th>
											<th>Remarks</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td id="vdScanId">—</td>
											<td id="vdScannedOffice">—</td>
											<td id="vdScannedBy">Admin Reyes</td>
											<td id="vdScanTime">—</td>
											<td><span id="vdValidationBadge" class="vd-badge vd-badge-secondary">Unknown</span></td>
											<td id="vdRemarks">—</td>
										</tr>
									</tbody>
								</table>
							</div>
						</section>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		const userMenuGroup = document.getElementById('userMenuGroup');
		const userMenuToggle = document.getElementById('userMenuToggle');
		const visitorDetailModal = document.getElementById('visitorDetailModal');
		const vdClose = document.getElementById('vdClose');

		const getText = (value, fallback = '—') => {
			if (value === null || value === undefined) return fallback;
			const v = String(value).trim();
			return v.length ? v : fallback;
		};

		const setTextById = (id, value, fallback = '—') => {
			const el = document.getElementById(id);
			if (!el) return;
			el.textContent = getText(value, fallback);
		};

		const setBadgeClass = (el, className) => {
			if (!el) return;
			el.className = className;
		};

		const statusClassFromText = (status) => {
			const s = getText(status, 'Pending').toLowerCase();
			if (s.includes('arrived')) return 'vd-status-badge vd-status-arrived';
			if (s.includes('transit')) return 'vd-status-badge vd-status-transit';
			if (s.includes('completed')) return 'vd-status-badge vd-status-completed';
			if (s.includes('overstay')) return 'vd-status-badge vd-status-overstay';
			return 'vd-status-badge vd-status-pending';
		};

		const severityBadgeClass = (severity) => {
			const s = getText(severity, 'Medium').toLowerCase();
			if (s === 'critical' || s === 'high') return 'vd-badge vd-badge-danger';
			if (s === 'medium') return 'vd-badge vd-badge-warning';
			if (s === 'low') return 'vd-badge vd-badge-secondary';
			return 'vd-badge vd-badge-secondary';
		};

		const expectationBadgeClass = (status) => {
			const s = getText(status, 'Pending').toLowerCase();
			if (s.includes('arrived') || s.includes('completed') || s.includes('matched') || s.includes('done')) {
				return 'vd-badge vd-badge-success';
			}
			if (s.includes('overstay') || s.includes('missed') || s.includes('failed')) {
				return 'vd-badge vd-badge-danger';
			}
			return 'vd-badge vd-badge-warning';
		};

		const validationBadgeClass = (status) => {
			const s = getText(status, 'Unknown').toLowerCase();
			if (s.includes('match') || s.includes('valid') || s.includes('approved')) {
				return 'vd-badge vd-badge-success';
			}
			if (s.includes('review') || s.includes('pending') || s.includes('unknown')) {
				return 'vd-badge vd-badge-warning';
			}
			if (s.includes('unauthor') || s.includes('wrong') || s.includes('fail') || s.includes('invalid') || s.includes('overstay')) {
				return 'vd-badge vd-badge-danger';
			}

			return 'vd-badge vd-badge-secondary';
		};

		const renderOfficeRouteRows = (trigger, status) => {
			const tbody = document.getElementById('vdOfficeRouteBody');
			if (!tbody) return;

			let routeRows = [];
			const raw = getText(trigger.dataset.officeRoute, '[]');
			try {
				routeRows = JSON.parse(raw);
				if (!Array.isArray(routeRows)) routeRows = [];
			} catch (error) {
				routeRows = [];
			}

			tbody.innerHTML = '';

			if (!routeRows.length) {
				const tr = document.createElement('tr');
				const td = document.createElement('td');
				td.colSpan = 4;
				td.textContent = 'No expected office route found.';
				td.style.color = '#64748b';
				tr.appendChild(td);
				tbody.appendChild(tr);
				return;
			}

			routeRows.forEach((row) => {
				const tr = document.createElement('tr');

				const officeTd = document.createElement('td');
				officeTd.textContent = getText(row.expected_office, '—');

				const orderTd = document.createElement('td');
				orderTd.textContent = getText(row.expected_order, '—');

				const statusTd = document.createElement('td');
				const statusBadge = document.createElement('span');
				const expectationStatus = getText(row.expectation_status, 'Pending');
				statusBadge.textContent = expectationStatus;
				statusBadge.className = expectationBadgeClass(expectationStatus);
				statusTd.appendChild(statusBadge);

				const arrivedTd = document.createElement('td');
				arrivedTd.textContent = getText(row.arrived_at, '—');

				tr.appendChild(officeTd);
				tr.appendChild(orderTd);
				tr.appendChild(statusTd);
				tr.appendChild(arrivedTd);

				tbody.appendChild(tr);
			});
		};

		const SUPABASE_URL = @json(rtrim((string) env('SUPABASE_URL', ''), '/'));

		const normalizePhotoUrl = (rawValue) => {
			const value = getText(rawValue, '');
			if (!value || value === '—') return '';

			if (/^https?:\/\//i.test(value)) {
				return value;
			}

			if (!SUPABASE_URL) {
				return value;
			}

			if (value.startsWith('/storage/v1/object/public/')) {
				return `${SUPABASE_URL}${value}`;
			}

			if (value.startsWith('storage/v1/object/public/')) {
				return `${SUPABASE_URL}/${value}`;
			}

			if (value.startsWith('http')) {
				return value;
			}

			return `${SUPABASE_URL}/storage/v1/object/public/${value.replace(/^\/+/, '')}`;
		};

		const openVisitorDetailModal = (trigger) => {
			if (!visitorDetailModal || !trigger) return;

			const status = getText(trigger.dataset.status, 'Pending');
			const alertText = getText(trigger.dataset.alert, 'None');

			setTextById('vdHeaderName', trigger.dataset.visitorName);
			setTextById('vdHeaderControl', trigger.dataset.controlNumber);

			setTextById('vdVisitorId', trigger.dataset.visitorId);
			setTextById('vdVisitorName', trigger.dataset.visitorName);
			setTextById('vdPassNumber', trigger.dataset.passNumber);
			setTextById('vdControlNumber', trigger.dataset.controlNumber);
			setTextById('vdContactNo', trigger.dataset.contactNo);
			setTextById('vdAddress', trigger.dataset.address, 'Address not available');
			const photoContainer = document.getElementById('vdPhoto');
			if (photoContainer) {
				const photoUrl = normalizePhotoUrl(trigger.dataset.photoUrl);
				photoContainer.innerHTML = '';
				photoContainer.classList.remove('has-image');
				if (photoUrl && photoUrl !== '—') {
					const img = document.createElement('img');
					img.src = photoUrl;
					img.alt = 'Visitor photo with ID';
					img.loading = 'lazy';
					img.referrerPolicy = 'no-referrer';
					img.onerror = () => {
						photoContainer.classList.remove('has-image');
						photoContainer.innerHTML = '';
						photoContainer.textContent = 'No Photo Available';
					};
					photoContainer.classList.add('has-image');
					photoContainer.appendChild(img);
				} else {
					photoContainer.classList.remove('has-image');
					photoContainer.textContent = 'No Photo Available';
				}
			}

			setTextById('vdVisitId', trigger.dataset.visitId);
			setTextById('vdVisitType', trigger.dataset.visitType);
			setTextById('vdPurpose', trigger.dataset.purpose);
			setTextById('vdPrimaryOffice', trigger.dataset.primaryOffice);
			setTextById('vdEntryTime', trigger.dataset.entryTime);
			setTextById('vdExitTime', trigger.dataset.exitTime);
			setTextById('vdDuration', trigger.dataset.duration);
			setTextById('vdExitStatus', trigger.dataset.exitStatus, status);
			setTextById('vdRegisteredByGuard', trigger.dataset.registeredBy, '—');

			renderOfficeRouteRows(trigger, status);

			const scanId = getText(trigger.dataset.scanId, '—');
			const scannedOffice = getText(trigger.dataset.scannedOffice, getText(trigger.dataset.primaryOffice, '—'));
			const scannedBy = getText(trigger.dataset.scannedBy, getText(trigger.dataset.registeredBy, '—'));
			const scanTime = getText(trigger.dataset.scanTime, getText(trigger.dataset.entryTime, '—'));
			const scanRemarks = getText(trigger.dataset.scanRemarks, alertText === 'None' ? '—' : alertText);
			const validationStatus = getText(trigger.dataset.validationStatus, alertText === 'None' ? 'Matched' : 'Requires Review');

			setTextById('vdScanId', scanId);
			setTextById('vdScannedOffice', scannedOffice);
			setTextById('vdScannedBy', scannedBy);
			setTextById('vdScanTime', scanTime);
			setTextById('vdRemarks', scanRemarks);

			const validationEl = document.getElementById('vdValidationBadge');
			if (validationEl) {
				validationEl.textContent = validationStatus;
				validationEl.className = validationBadgeClass(validationStatus);
			}

			const noAlertsBox = document.getElementById('vdNoAlertsBox');
			const alertBox = document.getElementById('vdAlertBox');
			const hasAlert = getText(trigger.dataset.alertId, '') !== '' && getText(trigger.dataset.alertId, '') !== '—';

			if (!hasAlert) {
				if (noAlertsBox) noAlertsBox.style.display = 'inline-flex';
				if (alertBox) alertBox.style.display = 'none';
			} else {
				if (noAlertsBox) noAlertsBox.style.display = 'none';
				if (alertBox) alertBox.style.display = 'block';

				setTextById('vdAlertId', trigger.dataset.alertId, '—');
				setTextById('vdAlertType', trigger.dataset.alertType, '—');
				setTextById('vdAlertMessage', trigger.dataset.alertMessage, '—');
				setTextById('vdAlertCreatedAt', trigger.dataset.alertCreatedAt, '—');
				setTextById('vdResolvedAt', trigger.dataset.alertResolvedAt, '—');
				setTextById('vdResolvedBy', trigger.dataset.alertResolvedBy, '—');
				setTextById('vdResolutionNotes', trigger.dataset.alertResolutionNotes, '—');

				const alertStatusBadge = document.getElementById('vdAlertStatusBadge');
				if (alertStatusBadge) {
					const alertStatusText = getText(trigger.dataset.alertStatus, 'Unresolved');
					alertStatusBadge.textContent = alertStatusText;
					const isResolved = alertStatusText.toLowerCase().includes('resolved');
					setBadgeClass(alertStatusBadge, isResolved ? 'vd-badge vd-badge-success' : 'vd-badge vd-badge-danger');
				}

				const severityEl = document.getElementById('vdAlertSeverityBadge');
				if (severityEl) {
					const severityText = getText(trigger.dataset.alertSeverity, 'Medium');
					severityEl.textContent = severityText;
					setBadgeClass(severityEl, severityBadgeClass(severityText));
				}
			}

			const headerStatus = document.getElementById('vdStatusBadge');
			if (headerStatus) {
				headerStatus.textContent = status;
				setBadgeClass(headerStatus, statusClassFromText(status));
			}

			visitorDetailModal.classList.add('open');
			visitorDetailModal.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';
		};

		const closeVisitorDetailModal = () => {
			if (!visitorDetailModal) return;
			visitorDetailModal.classList.remove('open');
			visitorDetailModal.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';
		};

		if (userMenuGroup && userMenuToggle) {
			userMenuToggle.addEventListener('click', () => {
				const isOpen = userMenuGroup.classList.toggle('open');
				userMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		}

		document.addEventListener('click', (event) => {
			const trigger = event.target.closest('.js-open-visitor-modal');
			if (trigger) {
				event.preventDefault();
				openVisitorDetailModal(trigger);
				return;
			}

			if (event.target === visitorDetailModal || event.target === vdClose) {
				closeVisitorDetailModal();
			}
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				closeVisitorDetailModal();
			}
		});
	</script>
</body>
</html>
