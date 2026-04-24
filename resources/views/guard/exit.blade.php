<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Exit Scan</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
	<style>
		:root {
			font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
		}

		* {
			box-sizing: border-box;
		}

		body {
			margin: 0;
			background: #dfe1e8;
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

		.menu {
			display: block;
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
		}

		.exit-page-wrap {
			padding: 28px;
		}

		.exit-page-header {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			gap: 16px;
			flex-wrap: wrap;
			margin-bottom: 24px;
		}

		.exit-page-title {
			margin: 0 0 6px;
			font-size: 32px;
			font-weight: 700;
			color: #111827;
		}

		.exit-page-subtitle {
			margin: 0;
			font-size: 14px;
			color: #6b7280;
		}

		.scan-status-pill {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 999px;
			padding: 10px 14px;
			font-size: 13px;
			font-weight: 600;
			box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
		}

		.status-dot {
			width: 8px;
			height: 8px;
			background: #22c55e;
			border-radius: 50%;
		}

		.exit-scan-layout {
			display: grid;
			grid-template-columns: 1.5fr 0.9fr;
			gap: 22px;
			align-items: start;
		}

		.scanner-panel {
			min-width: 0;
		}

		.scanner-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 22px;
			padding: 18px;
			box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
		}

		.scanner-card-top {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			gap: 16px;
			flex-wrap: wrap;
			margin-bottom: 16px;
		}

		.scanner-card-top h3 {
			margin: 0 0 6px;
			font-size: 20px;
			font-weight: 700;
		}

		.scanner-card-top p {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
		}

		.scanner-mode-badge {
			display: inline-flex;
			align-items: center;
			padding: 8px 12px;
			border-radius: 999px;
			background: #eef2ff;
			color: #3f4db8;
			font-size: 12px;
			font-weight: 700;
		}

		.scanner-box {
			margin-bottom: 16px;
		}

		.scanner-preview {
			position: relative;
			width: 100%;
			min-height: 430px;
			border-radius: 18px;
			background: #dfe4ec;
			border: 1px solid #d2d8e2;
			overflow: hidden;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.camera-feed {
			position: absolute;
			inset: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: none;
			background: #cbd2dc;
			z-index: 1;
		}

		.scanner-zone.camera-on .camera-feed {
			display: block;
		}

		.scan-frame {
			position: relative;
			width: 230px;
			height: 230px;
			z-index: 2;
		}

		.corner {
			position: absolute;
			width: 40px;
			height: 40px;
			border-color: #3f4db8;
			border-style: solid;
			border-width: 0;
		}

		.top-left {
			top: 0;
			left: 0;
			border-top-width: 5px;
			border-left-width: 5px;
			border-top-left-radius: 16px;
		}

		.top-right {
			top: 0;
			right: 0;
			border-top-width: 5px;
			border-right-width: 5px;
			border-top-right-radius: 16px;
		}

		.bottom-left {
			bottom: 0;
			left: 0;
			border-bottom-width: 5px;
			border-left-width: 5px;
			border-bottom-left-radius: 16px;
		}

		.bottom-right {
			bottom: 0;
			right: 0;
			border-bottom-width: 5px;
			border-right-width: 5px;
			border-bottom-right-radius: 16px;
		}

		.scanner-overlay-text {
			position: absolute;
			bottom: 22px;
			left: 50%;
			transform: translateX(-50%);
			background: rgba(17, 24, 39, 0.72);
			color: #ffffff;
			padding: 8px 14px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 600;
			letter-spacing: 0.02em;
			z-index: 2;
		}

		.scanner-helper-text {
			text-align: center;
			margin-bottom: 18px;
		}

		.helper-normal {
			margin: 0 0 8px;
			font-size: 13px;
			color: #6b7280;
		}

		.helper-error {
			margin: 0;
			font-size: 13px;
			color: #dc2626;
			font-weight: 500;
			min-height: 18px;
		}

		.scan-result.success {
			color: #15803d;
		}

		.scan-result.error {
			color: #dc2626;
		}

		.scanner-actions {
			display: flex;
			justify-content: center;
			gap: 12px;
			flex-wrap: wrap;
		}

		.manual-entry-wrap {
			margin-top: 12px;
			display: none;
			gap: 8px;
			justify-content: center;
			flex-wrap: wrap;
		}

		.manual-entry-wrap.show {
			display: flex;
		}

		.manual-input {
			height: 44px;
			min-width: 240px;
			border: 1px solid #d1d5db;
			border-radius: 10px;
			padding: 0 12px;
			font-size: 14px;
			outline: none;
		}

		.manual-input:focus {
			border-color: #3f4db8;
			box-shadow: 0 0 0 3px rgba(63, 77, 184, 0.12);
		}

		.scan-btn-primary,
		.scan-btn-secondary {
			border: none;
			outline: none;
			height: 48px;
			padding: 0 20px;
			border-radius: 12px;
			font-size: 14px;
			font-weight: 600;
			display: inline-flex;
			align-items: center;
			gap: 10px;
			cursor: pointer;
			transition: 0.2s ease;
		}

		.scan-btn-primary {
			background: linear-gradient(135deg, #3f4db8, #33419e);
			color: #ffffff;
			min-width: 170px;
			justify-content: center;
		}

		.scan-btn-primary:hover {
			transform: translateY(-1px);
		}

		.scan-btn-secondary {
			background: #ffffff;
			color: #111827;
			border: 1px solid #e5e7eb;
			min-width: 150px;
			justify-content: center;
		}

		.scan-btn-secondary:hover {
			background: #f9fafb;
		}

		.scan-side-panel {
			display: flex;
			flex-direction: column;
			gap: 18px;
		}

		.side-info-card,
		.recent-scan-card {
			background: #ffffff;
			border: 1px solid #e5e7eb;
			border-radius: 18px;
			box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
			padding: 18px;
		}

		.side-info-card {
			display: flex;
			gap: 14px;
			align-items: flex-start;
		}

		.side-info-icon {
			width: 42px;
			height: 42px;
			min-width: 42px;
			border-radius: 12px;
			background: #eef2ff;
			color: #3f4db8;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 16px;
		}

		.side-info-icon.soft-green {
			background: #ecfdf3;
			color: #16a34a;
		}

		.side-info-card h4,
		.recent-header h4 {
			margin: 0 0 8px;
			font-size: 16px;
			font-weight: 700;
		}

		.side-info-card p,
		.side-info-card ul,
		.recent-empty-state p {
			margin: 0;
			font-size: 13px;
			color: #6b7280;
			line-height: 1.6;
		}

		.side-info-card ul {
			padding-left: 18px;
		}

		.recent-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 14px;
		}

		.recent-header span {
			font-size: 12px;
			color: #6b7280;
			font-weight: 600;
		}

		.recent-empty-state {
			border: 1px dashed #d1d5db;
			border-radius: 14px;
			padding: 24px 16px;
			text-align: center;
		}

		.recent-empty-icon {
			width: 52px;
			height: 52px;
			margin: 0 auto 12px;
			border-radius: 50%;
			background: #f3f4f6;
			color: #6b7280;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 20px;
		}

		.exit-modal-overlay {
			position: fixed;
			inset: 0;
			background: rgba(15, 23, 42, 0.45);
			display: none;
			align-items: center;
			justify-content: center;
			z-index: 9999;
			padding: 24px;
			font-family: Inter, "Segoe UI", sans-serif;
		}

		.exit-modal-overlay.show {
			display: flex;
		}

		.exit-success-modal {
			width: 100%;
			max-width: 820px;
			background: #ffffff;
			border: 2px solid #3b6df6;
			border-radius: 22px;
			padding: 38px 42px 32px;
			box-shadow: 0 25px 70px rgba(15, 23, 42, 0.25);
			animation: modalFadeIn 0.25s ease;
		}

		.success-icon-wrapper {
			position: relative;
			display: flex;
			justify-content: center;
			margin-bottom: 18px;
		}

		.success-ring {
			width: 104px;
			height: 104px;
			border-radius: 50%;
			background: rgba(34, 197, 94, 0.12);
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.success-ring::before {
			content: "";
			position: absolute;
			width: 78px;
			height: 78px;
			border-radius: 50%;
			background: rgba(34, 197, 94, 0.16);
		}

		.success-icon {
			position: relative;
			width: 64px;
			height: 64px;
			border-radius: 50%;
			background: linear-gradient(135deg, #5bd46f, #16a34a);
			color: #ffffff;
			font-size: 34px;
			font-weight: 800;
			display: flex;
			align-items: center;
			justify-content: center;
			box-shadow: 0 10px 22px rgba(22, 163, 74, 0.35);
		}

		.dot {
			position: absolute;
			width: 7px;
			height: 7px;
			background: #22c55e;
			border-radius: 50%;
		}

		.dot-1 {
			top: 10px;
			left: 31%;
		}

		.dot-2 {
			top: 48px;
			left: 26%;
		}

		.dot-3 {
			top: 18px;
			right: 32%;
		}

		.dot-4 {
			top: 55px;
			right: 25%;
		}

		.dot-5 {
			top: 72px;
			right: 36%;
			width: 4px;
			height: 4px;
		}

		.modal-header-text {
			text-align: center;
		}

		.modal-header-text h2 {
			margin: 0;
			color: #0f172a;
			font-size: 34px;
			font-weight: 800;
			letter-spacing: -0.5px;
		}

		.modal-header-text p {
			margin-top: 6px;
			color: #64748b;
			font-size: 16px;
		}

		.modal-divider {
			height: 1px;
			background: #e5e7eb;
			margin: 24px 0 26px;
		}

		.visitor-details {
			display: grid;
			grid-template-columns: 150px 1fr;
			gap: 30px;
			align-items: center;
		}

		.visitor-photo-card {
			width: 150px;
			height: 180px;
			border-radius: 18px;
			background: #f1f5f9;
			overflow: hidden;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.visitor-photo-card img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: none;
		}

		.photo-placeholder {
			text-align: center;
			color: #64748b;
		}

		.user-icon {
			font-size: 54px;
			line-height: 1;
			color: #86a2d9;
			margin-bottom: 14px;
		}

		.photo-placeholder span {
			font-size: 14px;
			font-weight: 600;
		}

		.details-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			column-gap: 46px;
			row-gap: 22px;
		}

		.detail-item {
			display: flex;
			align-items: flex-start;
			gap: 13px;
		}

		.detail-icon {
			min-width: 34px;
			height: 34px;
			border-radius: 50%;
			background: #eef4ff;
			color: #2563eb;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 16px;
		}

		.detail-label {
			display: block;
			color: #64748b;
			font-size: 13px;
			font-weight: 600;
			margin-bottom: 3px;
		}

		.detail-item strong {
			display: block;
			color: #0f172a;
			font-size: 19px;
			font-weight: 800;
			line-height: 1.25;
			word-break: break-word;
		}

		.registered-by {
			text-align: center;
			color: #64748b;
			font-size: 15px;
			margin: 28px 0 18px;
		}

		.modal-action {
			display: flex;
			justify-content: center;
		}

		.done-btn {
			width: 330px;
			height: 58px;
			border: none;
			border-radius: 14px;
			background: linear-gradient(135deg, #2563eb, #1d4ed8);
			color: #ffffff;
			font-size: 18px;
			font-weight: 800;
			cursor: pointer;
			display: flex;
			gap: 10px;
			align-items: center;
			justify-content: center;
			box-shadow: 0 12px 26px rgba(37, 99, 235, 0.32);
			transition: 0.2s ease;
		}

		.done-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 16px 32px rgba(37, 99, 235, 0.42);
		}

		.done-btn:active {
			transform: scale(0.98);
		}

		@keyframes modalFadeIn {
			from {
				opacity: 0;
				transform: translateY(18px) scale(0.97);
			}

			to {
				opacity: 1;
				transform: translateY(0) scale(1);
			}
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

		@media (max-width: 1100px) {
			.exit-scan-layout {
				grid-template-columns: 1fr;
			}
		}

		@media (max-width: 768px) {
			.exit-page-wrap {
				padding: 16px;
			}

			.scanner-preview {
				min-height: 320px;
			}

			.scan-frame {
				width: 180px;
				height: 180px;
			}

			.scanner-actions {
				flex-direction: column;
			}

			.scan-btn-primary,
			.scan-btn-secondary {
				width: 100%;
			}

			.exit-success-modal {
				padding: 30px 24px;
			}

			.visitor-details {
				grid-template-columns: 1fr;
				justify-items: center;
			}

			.details-grid {
				width: 100%;
				grid-template-columns: 1fr;
			}

			.done-btn {
				width: 100%;
			}

			.modal-header-text h2 {
				font-size: 28px;
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
					<div class="sidebar-section">
						<p class="sidebar-label">MAIN</p>
						<a href="/guard/dashboard" class="sidebar-link {{ request()->is('guard/dashboard') ? 'active' : '' }}">
							<span class="sidebar-icon"><i class="bi bi-grid-1x2-fill"></i></span>
							<span class="sidebar-text">Dashboard</span>
						</a>
					</div>

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

						<a href="/guard/exit" class="sidebar-link {{ request()->is('guard/exit') ? 'active' : '' }}">
							<span class="sidebar-icon"><i class="bi bi-box-arrow-right"></i></span>
							<span class="sidebar-text">Exit Scan</span>
						</a>
					</div>

					<div class="sidebar-section">
						<p class="sidebar-label">MONITORING</p>
						<a href="/guard/alert" class="sidebar-link {{ request()->is('guard/alert') ? 'active' : '' }}">
							<span class="sidebar-icon"><i class="bi bi-bell-fill"></i></span>
							<span class="sidebar-text">Active Alerts</span>
							<span class="sidebar-badge">{{ $activeAlertsCount ?? 0 }}</span>
						</a>
					</div>

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
			<div class="exit-page-wrap">
				<div class="exit-page-header">
					<div>
						<h1 class="exit-page-title">Exit Scan</h1>
						<p class="exit-page-subtitle">Scan visitor QR code to process exit quickly and securely.</p>
					</div>

					<div class="scan-status-pill">
						<span class="status-dot"></span>
						Scanner Ready
					</div>
				</div>

				<div class="exit-scan-layout">
					<div class="scanner-panel">
						<div class="scanner-card">
							<div class="scanner-card-top">
								<div>
									<h3>QR Scanner</h3>
									<p>Align the QR code inside the frame to continue exit processing.</p>
								</div>
								<span class="scanner-mode-badge">Live Camera</span>
							</div>

							<div class="scanner-box">
								<div id="qr-reader" class="scanner-preview scanner-zone">
									<video id="cameraFeed" class="camera-feed" autoplay playsinline muted></video>
									<div class="scan-frame">
										<span class="corner top-left"></span>
										<span class="corner top-right"></span>
										<span class="corner bottom-left"></span>
										<span class="corner bottom-right"></span>
									</div>

									<div class="scanner-overlay-text">
										Position QR code here
									</div>
								</div>
							</div>

							<div class="scanner-helper-text">
								<p class="helper-normal" id="cameraStatus">Starting camera...</p>
								<p class="helper-error scan-result" id="scanResult" aria-live="polite"></p>
							</div>

							<div class="scanner-actions">
								<button type="button" class="scan-btn-primary" id="scanButton">
									<i class="fas fa-camera-retro"></i>
									<span id="scanButtonText">Scan Exit QR</span>
								</button>

								<button type="button" class="scan-btn-secondary" id="manualEntryButton">
									<i class="fas fa-keyboard"></i>
									Manual Entry
								</button>
							</div>

							<div class="manual-entry-wrap" id="manualEntryWrap">
								<input
									type="text"
									class="manual-input"
									id="manualQrInput"
									placeholder="Enter QR code / control number"
									autocomplete="off"
								>
								<button type="button" class="scan-btn-primary" id="submitManualEntryButton">
									<i class="fas fa-check"></i>
									Submit Code
								</button>
							</div>

							<canvas id="scanCanvas" style="display:none;"></canvas>
						</div>
					</div>

					<div class="scan-side-panel">
						<div class="side-info-card">
							<div class="side-info-icon">
								<i class="fas fa-circle-info"></i>
							</div>
							<div>
								<h4>Scanning Tips</h4>
								<ul>
									<li>Keep the QR code inside the scanning frame.</li>
									<li>Use proper lighting for faster detection.</li>
									<li>Hold the device steady for better camera focus.</li>
								</ul>
							</div>
						</div>

						<div class="side-info-card">
							<div class="side-info-icon soft-green">
								<i class="fas fa-shield-check"></i>
							</div>
							<div>
								<h4>Exit Validation</h4>
								<p>The system will verify visitor record, completion status, and exit readiness before checkout.</p>
							</div>
						</div>

						<div class="recent-scan-card">
							<div class="recent-header">
								<h4>Recent Scan</h4>
								<span>Latest</span>
							</div>

							<div class="recent-empty-state">
								<div class="recent-empty-icon">
									<i class="fas fa-qrcode"></i>
								</div>
								<p>No recent successful scan yet.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>
	</div>

	<div class="exit-modal-overlay" id="exitResultModal" aria-hidden="true">
		<div class="exit-success-modal" role="dialog" aria-modal="true" aria-labelledby="exitResultTitle">
			<div class="success-icon-wrapper">
				<span class="dot dot-1"></span>
				<span class="dot dot-2"></span>
				<span class="dot dot-3"></span>
				<span class="dot dot-4"></span>
				<span class="dot dot-5"></span>
				<div class="success-ring">
					<div class="success-icon">✓</div>
				</div>
			</div>

			<div class="modal-header-text">
				<h2 id="exitResultTitle">Exit Processed!</h2>
				<p>Visitor has left the campus</p>
			</div>

			<div class="modal-divider"></div>

			<div class="visitor-details">
				<div class="visitor-photo-card">
					<img id="exitResultPhoto" alt="Visitor Photo">
					<div class="photo-placeholder" id="exitResultPhotoFallback">
						<div class="user-icon">👤</div>
						<span>Visitor Photo</span>
					</div>
				</div>

				<div class="details-grid">
					<div class="detail-item">
						<div class="detail-icon">👤</div>
						<div>
							<span class="detail-label">Control Number</span>
							<strong id="exitResultControlNumber">-</strong>
						</div>
					</div>

					<div class="detail-item">
						<div class="detail-icon">🏢</div>
						<div>
							<span class="detail-label">Office Visited</span>
							<strong id="exitResultOffice">-</strong>
						</div>
					</div>

					<div class="detail-item">
						<div class="detail-icon">👤</div>
						<div>
							<span class="detail-label">Full Name</span>
							<strong id="exitResultVisitorName">-</strong>
						</div>
					</div>

					<div class="detail-item">
						<div class="detail-icon">⏱</div>
						<div>
							<span class="detail-label">Duration</span>
							<strong id="exitResultDuration">-</strong>
						</div>
					</div>

					<div class="detail-item">
						<div class="detail-icon">🕒</div>
						<div>
							<span class="detail-label">Time In</span>
							<strong id="exitResultTimeIn">-</strong>
						</div>
					</div>

					<div class="detail-item">
						<div class="detail-icon">🪪</div>
						<div>
							<span class="detail-label">ID Pass Number</span>
							<strong id="exitResultPassNumber">-</strong>
						</div>
					</div>
				</div>
			</div>

			<p class="registered-by">Registered by Officer Martinez</p>

			<div class="modal-action">
				<button type="button" class="done-btn" id="exitResultDoneButton">
					<span>✓</span>
					Done
				</button>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
	<script>
		const scannerZone = document.querySelector('.scanner-zone');
		const cameraFeed = document.getElementById('cameraFeed');
		const scanButton = document.getElementById('scanButton');
		const scanButtonText = document.getElementById('scanButtonText');
		const manualEntryButton = document.getElementById('manualEntryButton');
		const submitManualEntryButton = document.getElementById('submitManualEntryButton');
		const manualEntryWrap = document.getElementById('manualEntryWrap');
		const manualQrInput = document.getElementById('manualQrInput');
		const cameraStatus = document.getElementById('cameraStatus');
		const scanResult = document.getElementById('scanResult');
		const scanCanvas = document.getElementById('scanCanvas');
		const exitResultModal = document.getElementById('exitResultModal');
		const exitResultDoneButton = document.getElementById('exitResultDoneButton');
		const exitResultPhoto = document.getElementById('exitResultPhoto');
		const exitResultPhotoFallback = document.getElementById('exitResultPhotoFallback');
		const exitResultControlNumber = document.getElementById('exitResultControlNumber');
		const exitResultOffice = document.getElementById('exitResultOffice');
		const exitResultVisitorName = document.getElementById('exitResultVisitorName');
		const exitResultDuration = document.getElementById('exitResultDuration');
		const exitResultTimeIn = document.getElementById('exitResultTimeIn');
		const exitResultPassNumber = document.getElementById('exitResultPassNumber');
		const canvasContext = scanCanvas.getContext('2d', { willReadFrequently: true });
		const csrfToken = '{{ csrf_token() }}';

		let activeStream = null;
		let barcodeDetector = null;
		let scanTimer = null;
		let isProcessingScan = false;
		let lastProcessedQr = '';
		let resumeScanTimeout = null;

		if ('BarcodeDetector' in window) {
			try {
				barcodeDetector = new BarcodeDetector({ formats: ['qr_code'] });
			} catch (error) {
				barcodeDetector = null;
			}
		}

		const setScannerState = (isOn, message) => {
			scannerZone.classList.toggle('camera-on', isOn);
			scanButtonText.textContent = isOn ? 'Scan Exit QR' : 'Retry Camera';
			cameraStatus.textContent = message;
			scanButton.disabled = false;
		};

		const setResult = (message, type = '') => {
			scanResult.textContent = message;
			scanResult.className = 'scan-result';
			if (type) {
				scanResult.classList.add(type);
			}
		};

		const formatDuration = (minutes) => {
			const value = Number(minutes);
			if (!Number.isFinite(value) || value < 0) {
				return '-';
			}

			if (value < 60) {
				return `${Math.round(value)}m`;
			}

			const hrs = Math.floor(value / 60);
			const mins = value % 60;
			return mins > 0 ? `${hrs}h ${mins}m` : `${hrs}h`;
		};

		const formatDateTime = (value) => {
			if (!value) {
				return '-';
			}

			const parsed = new Date(value);
			if (Number.isNaN(parsed.getTime())) {
				return '-';
			}

			return parsed.toLocaleString('en-US', {
				month: 'short',
				day: 'numeric',
				year: 'numeric',
				hour: 'numeric',
				minute: '2-digit',
				hour12: true
			});
		};

		const closeExitResultModal = () => {
			if (!exitResultModal) {
				return;
			}

			exitResultModal.classList.remove('show');
			exitResultModal.setAttribute('aria-hidden', 'true');
		};

		const resolveVisitorPhotoUrl = (rawPath) => {
			const value = String(rawPath || '').trim();
			if (!value) {
				return '';
			}

			if (/^https?:\/\//i.test(value) || value.startsWith('data:image/')) {
				return value;
			}

			if (value.startsWith('/')) {
				return value;
			}

			if (value.startsWith('storage/')) {
				return `/${value}`;
			}

			if (value.startsWith('public/')) {
				return `/${value.replace(/^public\//, 'storage/')}`;
			}

			return `/storage/${value.replace(/^\/+/, '')}`;
		};

		const showExitResultModal = (scanData) => {
			if (!exitResultModal || !scanData) {
				return;
			}

			const destination = String(scanData.office_name || scanData.destination_text || '').trim() || '-';
			const photoUrl = resolveVisitorPhotoUrl(scanData.visitor_photo_preview_url || scanData.visitor_photo_with_id_url);
			const controlNo = String(scanData.control_number || '').trim() || String(scanData.qr_data || '').trim() || '-';

			exitResultControlNumber.textContent = controlNo;
			exitResultOffice.textContent = destination;
			exitResultVisitorName.textContent = String(scanData.visitor_name || '').trim() || '-';
			exitResultDuration.textContent = formatDuration(scanData.duration_minutes);
			exitResultTimeIn.textContent = formatDateTime(scanData.entry_time);
			exitResultPassNumber.textContent = String(scanData.pass_number || '').trim() || '-';

			if (photoUrl) {
				exitResultPhoto.src = photoUrl;
				exitResultPhoto.style.display = 'block';
				exitResultPhotoFallback.style.display = 'none';
			} else {
				exitResultPhoto.removeAttribute('src');
				exitResultPhoto.style.display = 'none';
				exitResultPhotoFallback.style.display = 'block';
			}

			exitResultModal.classList.add('show');
			exitResultModal.setAttribute('aria-hidden', 'false');
		};

		const resetScanLoop = () => {
			if (scanTimer) {
				clearInterval(scanTimer);
				scanTimer = null;
			}
		};

		const releaseCamera = () => {
			resetScanLoop();
			if (resumeScanTimeout) {
				clearTimeout(resumeScanTimeout);
				resumeScanTimeout = null;
			}

			if (!activeStream) {
				return;
			}

			activeStream.getTracks().forEach((track) => track.stop());
			activeStream = null;
			cameraFeed.srcObject = null;
		};

		const processQrData = async (qrData) => {
			if (!qrData || isProcessingScan || qrData === lastProcessedQr) {
				return;
			}

			isProcessingScan = true;
			lastProcessedQr = qrData;
			setResult('QR detected. Processing exit...', '');

			try {
				const response = await fetch('/guard/exit/scan', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						'X-CSRF-TOKEN': csrfToken
					},
					body: JSON.stringify({ qr_data: qrData })
				});

				const payload = await response.json();
				if (!response.ok || payload.status !== 'ok') {
					throw new Error(payload.message || 'Unable to process scanned QR.');
				}

				setResult(payload.message + ' (' + payload.qr_data + ')', 'success');
				cameraStatus.textContent = 'Scan completed. Ready for next QR.';
				showExitResultModal({
					...(payload.data || {}),
					qr_data: payload.qr_data || ''
				});
				if (manualQrInput) {
					manualQrInput.value = '';
				}
			} catch (error) {
				lastProcessedQr = '';
				setResult(error.message || 'Scan failed. Please try again.', 'error');
			}

			isProcessingScan = false;
			if (resumeScanTimeout) {
				clearTimeout(resumeScanTimeout);
			}

			resumeScanTimeout = setTimeout(() => {
				lastProcessedQr = '';
				setResult('');
			}, 3000);
		};

		const detectWithJsQr = () => {
			if (!window.jsQR || !cameraFeed.videoWidth || !cameraFeed.videoHeight) {
				return;
			}

			scanCanvas.width = cameraFeed.videoWidth;
			scanCanvas.height = cameraFeed.videoHeight;
			canvasContext.drawImage(cameraFeed, 0, 0, scanCanvas.width, scanCanvas.height);
			const imageData = canvasContext.getImageData(0, 0, scanCanvas.width, scanCanvas.height);
			const decoded = window.jsQR(imageData.data, imageData.width, imageData.height, {
				inversionAttempts: 'dontInvert'
			});

			if (decoded && decoded.data) {
				processQrData(decoded.data);
			}
		};

		const detectWithBarcodeDetector = async () => {
			if (!barcodeDetector || !cameraFeed.videoWidth || !cameraFeed.videoHeight) {
				return false;
			}

			try {
				const detections = await barcodeDetector.detect(cameraFeed);
				if (detections.length > 0 && detections[0].rawValue) {
					processQrData(detections[0].rawValue);
					return true;
				}
			} catch (error) {
				// Fallback to jsQR on detector errors.
			}

			return false;
		};

		const startScanLoop = () => {
			resetScanLoop();
			scanTimer = setInterval(async () => {
				if (!activeStream || isProcessingScan) {
					return;
				}

				const detected = await detectWithBarcodeDetector();
				if (!detected) {
					detectWithJsQr();
				}
			}, 350);
		};

		const startCamera = async () => {
			if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
				setScannerState(false, 'Camera access is not supported in this browser.');
				return;
			}

			try {
				releaseCamera();

				const stream = await navigator.mediaDevices.getUserMedia({
					video: { facingMode: 'environment' },
					audio: false
				});

				activeStream = stream;
				cameraFeed.srcObject = stream;
				setScannerState(true, 'Scanner is ready. Position the QR inside the frame.');
				setResult('');
				startScanLoop();
			} catch (error) {
				setScannerState(false, 'Camera permission denied or unavailable. Click Retry Camera after allowing access.');
				setResult('Camera is required for QR scanning.', 'error');
			}
		};

		scanButton?.addEventListener('click', () => {
			if (!activeStream) {
				startCamera();
				return;
			}

			cameraStatus.textContent = 'Scanner is live. Align QR code inside the frame.';
			setResult('Waiting for QR code...');
			lastProcessedQr = '';
		});

		const normalizeManualCode = (rawValue) => String(rawValue || '').trim();

		const submitManualEntry = () => {
			if (!manualQrInput) {
				return;
			}

			const code = normalizeManualCode(manualQrInput.value);
			if (!code) {
				setResult('Please enter the QR code or control number first.', 'error');
				manualQrInput.focus();
				return;
			}

			cameraStatus.textContent = 'Processing manual code...';
			processQrData(code);
		};

		manualEntryButton?.addEventListener('click', () => {
			if (!manualEntryWrap) {
				return;
			}

			const isShown = manualEntryWrap.classList.toggle('show');
			if (isShown && manualQrInput) {
				manualQrInput.focus();
			}
		});

		submitManualEntryButton?.addEventListener('click', submitManualEntry);

		manualQrInput?.addEventListener('keydown', (event) => {
			if (event.key === 'Enter') {
				event.preventDefault();
				submitManualEntry();
			}
		});

		exitResultDoneButton?.addEventListener('click', () => {
			closeExitResultModal();
		});

		exitResultPhoto?.addEventListener('error', () => {
			exitResultPhoto.removeAttribute('src');
			exitResultPhoto.style.display = 'none';
			if (exitResultPhotoFallback) {
				exitResultPhotoFallback.style.display = 'block';
			}
		});

		exitResultModal?.addEventListener('click', (event) => {
			if (event.target === exitResultModal) {
				closeExitResultModal();
			}
		});

		window.addEventListener('beforeunload', () => {
			releaseCamera();
		});

		startCamera();
	</script>
</body>
</html>