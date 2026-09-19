<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<title>Exit Scan</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
	<style nonce="{{ $cspNonce }}">
		
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
			min-height: 100dvh;
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
			margin: 0;
			font-size: 0;
			line-height: 1;
			font-weight: 800;
			letter-spacing: -0.02em;
			display: flex;
			gap: 6px;
			align-items: baseline;
		}

		.brand-title span:first-child {
			color: #ffd84d;
			font-size: 28px;
		}

		.brand-title span:last-child {
			color: #ffffff;
			font-size: 26px;
			font-weight: 700;
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

		.sidebar-link.active,
		.sidebar-toggle[aria-expanded="true"] {
			background: linear-gradient(90deg, #4f62ff, #6678ff);
			color: #fff;
			box-shadow: 0 8px 20px rgba(46, 78, 255, 0.28);
		}

		.sidebar-link.active::before,
		.sidebar-toggle[aria-expanded="true"]::before {
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

		.scanner-input-zone {
			width: 100%;
			border-radius: 18px;
			background: #eef2ff;
			border: 1px solid #cdd7ff;
			padding: 26px 24px;
			display: grid;
			gap: 14px;
		}

		.scanner-instruction {
			margin: 0;
			font-size: 14px;
			color: #475569;
			line-height: 1.5;
		}

		.scanner-input {
			height: 60px;
			border: 2px solid #c7d2fe;
			border-radius: 14px;
			background: #fff;
			padding: 0 16px;
			font-size: 20px;
			font-weight: 600;
			letter-spacing: 0.02em;
			outline: none;
			transition: 0.15s ease;
		}

		.scanner-input:focus {
			border-color: #3f4db8;
			box-shadow: 0 0 0 4px rgba(63, 77, 184, 0.15);
		}

		.scanner-helper-text {
			text-align: left;
			margin-bottom: 18px;
		}

		.helper-normal {
			margin: 0 0 8px;
			font-size: 13px;
			color: #3f4db8;
			font-weight: 600;
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

		.scan-feedback-card {
			display: none;
			border-radius: 14px;
			padding: 12px 14px;
			margin-bottom: 12px;
			border: 1px solid transparent;
		}

		.scan-feedback-card.show {
			display: block;
		}

		.scan-feedback-card h5 {
			margin: 0 0 4px;
			font-size: 14px;
			font-weight: 700;
		}

		.scan-feedback-card p {
			margin: 0;
			font-size: 13px;
		}

		.scan-feedback-card.success {
			background: #ecfdf3;
			border-color: #86efac;
			color: #166534;
		}

		.scan-feedback-card.error {
			background: #fef2f2;
			border-color: #fca5a5;
			color: #991b1b;
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

		.recent-scan-details {
			display: none;
			border: 1px solid #e5e7eb;
			border-radius: 12px;
			padding: 12px;
			background: #f8fafc;
		}

		.recent-scan-details.show {
			display: block;
		}

		.recent-scan-row {
			display: flex;
			justify-content: space-between;
			gap: 10px;
			padding: 6px 0;
			border-bottom: 1px solid #e5e7eb;
			font-size: 13px;
		}

		.recent-scan-row:last-child {
			border-bottom: none;
		}

		.recent-scan-row span {
			color: #64748b;
			font-weight: 600;
		}

		.recent-scan-row strong {
			color: #0f172a;
			text-align: right;
			word-break: break-word;
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

		@include('guard.partials.guard-responsive-styles')

		@media (max-width: 1100px) {
			.exit-scan-layout {
				grid-template-columns: 1fr;
			}
		}

		@media (max-width: 991.98px) {
			.exit-page-title {
				display: none;
			}

			.exit-page-wrap {
				padding: 16px max(12px, env(safe-area-inset-right)) 20px max(12px, env(safe-area-inset-left));
			}

			.exit-success-modal {
				padding: 28px 20px 24px;
				max-width: min(100%, 820px);
				border-radius: 18px;
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
				max-width: 100%;
			}
		}

		@media (max-width: 768px) {
			.exit-page-wrap {
				padding: 14px max(12px, env(safe-area-inset-right)) 18px max(12px, env(safe-area-inset-left));
			}

			.scanner-preview {
				min-height: 280px;
			}

			.scan-frame {
				width: min(180px, 55vw);
				height: min(180px, 55vw);
			}

			.scanner-actions {
				flex-direction: column;
			}

			.scan-btn-primary,
			.scan-btn-secondary {
				width: 100%;
				min-width: 0;
			}

			.exit-modal-overlay {
				padding: 12px;
				align-items: flex-end;
			}

			.exit-success-modal {
				padding: 24px 16px 20px;
				border-radius: 18px 18px 0 0;
				max-height: min(92dvh, 900px);
				overflow-y: auto;
			}

			.modal-header-text h2 {
				font-size: clamp(1.35rem, 6vw, 1.75rem);
			}

			.registered-by {
				margin: 18px 0 14px;
				font-size: 14px;
			}
		}

		@media (max-width: 390px) {
			.scan-frame {
				width: min(150px, 60vw);
				height: min(150px, 60vw);
			}
		}
	</style>
</head>
<body>
	<div class="layout">
		<div class="guard-nav-backdrop" id="guardNavBackdrop" aria-hidden="true"></div>
		@php
			$guardSidebarUser = auth()->user();
			$guardSidebarName = trim(((string) ($guardSidebarUser->first_name ?? '')).' '.((string) ($guardSidebarUser->last_name ?? '')));
			$guardSidebarName = $guardSidebarName !== '' ? $guardSidebarName : ((string) ($guardSidebarUser->name ?? $guardSidebarUser->email ?? 'Guard Officer'));
		@endphp
		<aside class="sidebar d-flex flex-column justify-content-between" id="guardSidebarNav">
			<div>
				<div class="sidebar-brand d-flex align-items-center">
					<div class="brand-icon">
						<i class="bi bi-shield-lock-fill"></i>
					</div>
					<div>
						<h4 class="brand-title mb-0"><span>VMS</span> <span>Guard</span></h4>
						<small class="brand-subtitle">Visitor Monitoring System</small>
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
									<a href="/guard/register?type=normal" class="submenu-link js-privacy-consent-link {{ request()->is('guard/register*') && request('type') === 'normal' ? 'active' : '' }}">
										<i class="bi bi-person"></i>
										<span>Normal Visitor</span>
									</a>
									<a href="/guard/register?type=enrollee" class="submenu-link js-privacy-consent-link {{ request()->is('guard/register*') && request('type') === 'enrollee' ? 'active' : '' }}">
										<i class="bi bi-mortarboard-fill"></i>
										<span>Enrollee</span>
									</a>
									<a href="/guard/register?type=contractor" class="submenu-link js-privacy-consent-link {{ request()->is('guard/register*') && request('type') === 'contractor' ? 'active' : '' }}">
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
						<a href="/guard/register?type=normal" class="quick-action-btn js-privacy-consent-link">
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
			</div>
		</aside>

		<main class="main">
			<div class="exit-page-wrap">
				@include('guard.partials.guard-topbar', ['title' => 'Exit Scan'])
				<div class="exit-page-header">
					<div>
						<p class="exit-page-subtitle">Use the handheld QR/barcode scanner to process visitor exit quickly and securely.</p>
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
									<h3>Scan Visitor QR / Barcode</h3>
									<p>Scan the visitor pass using the handheld scanner. The code will auto-submit when Enter is received.</p>
								</div>
								<span class="scanner-mode-badge">Hardware Scanner Mode</span>
							</div>

							<div class="scanner-box">
								<div class="scanner-input-zone">
									<p class="scanner-instruction">
										Keep the scanner aimed at the visitor QR/barcode. Most scanners automatically press Enter after scan.
									</p>
									<input
										type="text"
										class="scanner-input"
										id="scannerInput"
										placeholder="Waiting for scan..."
										autocomplete="off"
										inputmode="text"
									>
								</div>
							</div>

							<div class="scanner-helper-text">
								<p class="helper-normal" id="scannerStatus">Scanner input ready.</p>
								<p class="helper-error scan-result" id="scanResult" aria-live="polite"></p>
							</div>

							<div id="scanSuccessCard" class="scan-feedback-card success" aria-live="polite">
								<h5>Exit Successful</h5>
								<p id="scanSuccessText">Visitor checked out successfully.</p>
							</div>

							<div id="scanErrorCard" class="scan-feedback-card error" aria-live="polite">
								<h5>Scan Error</h5>
								<p id="scanErrorText">Unable to process the scanned code.</p>
							</div>

							<div class="scanner-actions">
								<button type="button" class="scan-btn-secondary" id="manualEntryButton">
									<i class="fas fa-keyboard"></i>
									Manual Entry
								</button>

								<button type="button" class="scan-btn-secondary" id="clearScanButton">
									<i class="fas fa-eraser"></i>
									Clear
								</button>

								<button type="button" class="scan-btn-secondary" id="testScannerButton">
									<i class="fas fa-vial"></i>
									Test Scanner
								</button>
							</div>

							<div class="manual-entry-wrap" id="manualEntryWrap">
								<input
									type="text"
									class="manual-input"
									id="manualQrInput"
									placeholder="QR-… / 2026-… / or full QR JSON"
									autocomplete="off"
									spellcheck="false"
								>
								<button type="button" class="scan-btn-primary" id="submitManualEntryButton">
									<i class="fas fa-check"></i>
									Submit Code
								</button>
							</div>
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
									<li>Aim the handheld scanner directly at the visitor QR/barcode.</li>
									<li>Wait for the scanner beep before moving to the next pass.</li>
									<li>If a scan fails, use Manual Entry and submit the code.</li>
								</ul>
							</div>
						</div>

						<div class="side-info-card">
							<div class="side-info-icon soft-green">
								<i class="fas fa-shield-check"></i>
							</div>
							<div>
								<h4>Exit Validation</h4>
								<p>The system validates active visitor records before checkout and blocks invalid or already exited passes.</p>
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

							<div class="recent-scan-details" id="recentScanDetails">
								<div class="recent-scan-row"><span>Name</span><strong id="recentScanName">-</strong></div>
								<div class="recent-scan-row"><span>Control No.</span><strong id="recentScanControl">-</strong></div>
								<div class="recent-scan-row"><span>Purpose</span><strong id="recentScanPurpose">-</strong></div>
								<div class="recent-scan-row"><span>Time In</span><strong id="recentScanTimeIn">-</strong></div>
								<div class="recent-scan-row"><span>Time Out</span><strong id="recentScanTimeOut">-</strong></div>
								<div class="recent-scan-row"><span>Status</span><strong id="recentScanStatus">-</strong></div>
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
							<span class="detail-label">Purpose</span>
							<strong id="exitResultPurpose">-</strong>
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
							<span class="detail-label">Status</span>
							<strong id="exitResultStatus">-</strong>
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
							<span class="detail-label">Time Out</span>
							<strong id="exitResultTimeOut">-</strong>
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
	@include('guard.partials.guard-privacy-consent')
	<script nonce="{{ $cspNonce }}">
		const scannerInput = document.getElementById('scannerInput');
		const manualEntryButton = document.getElementById('manualEntryButton');
		const clearScanButton = document.getElementById('clearScanButton');
		const testScannerButton = document.getElementById('testScannerButton');
		const submitManualEntryButton = document.getElementById('submitManualEntryButton');
		const manualEntryWrap = document.getElementById('manualEntryWrap');
		const manualQrInput = document.getElementById('manualQrInput');
		const scannerStatus = document.getElementById('scannerStatus');
		const scanResult = document.getElementById('scanResult');
		const scanSuccessCard = document.getElementById('scanSuccessCard');
		const scanSuccessText = document.getElementById('scanSuccessText');
		const scanErrorCard = document.getElementById('scanErrorCard');
		const scanErrorText = document.getElementById('scanErrorText');
		const recentScanDetails = document.getElementById('recentScanDetails');
		const recentScanName = document.getElementById('recentScanName');
		const recentScanControl = document.getElementById('recentScanControl');
		const recentScanPurpose = document.getElementById('recentScanPurpose');
		const recentScanTimeIn = document.getElementById('recentScanTimeIn');
		const recentScanTimeOut = document.getElementById('recentScanTimeOut');
		const recentScanStatus = document.getElementById('recentScanStatus');
		const recentScanEmptyState = document.querySelector('.recent-empty-state');
		const exitResultModal = document.getElementById('exitResultModal');
		const exitResultDoneButton = document.getElementById('exitResultDoneButton');
		const exitResultPhoto = document.getElementById('exitResultPhoto');
		const exitResultPhotoFallback = document.getElementById('exitResultPhotoFallback');
		const exitResultControlNumber = document.getElementById('exitResultControlNumber');
		const exitResultPurpose = document.getElementById('exitResultPurpose');
		const exitResultVisitorName = document.getElementById('exitResultVisitorName');
		const exitResultStatus = document.getElementById('exitResultStatus');
		const exitResultTimeIn = document.getElementById('exitResultTimeIn');
		const exitResultTimeOut = document.getElementById('exitResultTimeOut');
		const csrfToken = '{{ csrf_token() }}';

		let isProcessingScan = false;
		let resumeScanTimeout = null;
		let processingCooldownUntil = 0;

		const focusScannerInput = () => {
			if (!scannerInput) {
				return;
			}
			scannerInput.focus({ preventScroll: true });
		};

		const setResult = (message, type = '') => {
			scanResult.textContent = message;
			scanResult.className = 'scan-result';
			if (type) {
				scanResult.classList.add(type);
			}
		};

		const toggleFeedbackCard = (card, shouldShow) => {
			if (!card) {
				return;
			}
			card.classList.toggle('show', shouldShow);
		};

		const showSuccessFeedback = (message) => {
			if (scanSuccessText) {
				scanSuccessText.textContent = message;
			}
			toggleFeedbackCard(scanSuccessCard, true);
			toggleFeedbackCard(scanErrorCard, false);
		};

		const showErrorFeedback = (message) => {
			if (scanErrorText) {
				scanErrorText.textContent = message;
			}
			toggleFeedbackCard(scanErrorCard, true);
			toggleFeedbackCard(scanSuccessCard, false);
		};

		const hideFeedbackCards = () => {
			toggleFeedbackCard(scanSuccessCard, false);
			toggleFeedbackCard(scanErrorCard, false);
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

			// Signed/public Supabase URLs and inline captures.
			if (/^https?:\/\//i.test(value) || value.startsWith('data:image/')) {
				return value;
			}

			// Local Laravel public storage only (not Supabase storage/v1 paths).
			if (value.startsWith('/storage/') && !value.includes('storage/v1/')) {
				return value;
			}

			if (value.startsWith('storage/') && !value.includes('storage/v1/')) {
				return `/${value}`;
			}

			// Bucket-relative path like visitor-file/Face_ID_Picture/capture_....jpg
			// Prefer server-provided preview URL; do not invent a local /storage path.
			return '';
		};

		const updateRecentScanCard = (scanData) => {
			if (!scanData) {
				return;
			}

			if (recentScanName) {
				recentScanName.textContent = String(scanData.visitor_name || '').trim() || '-';
			}

			if (recentScanControl) {
				recentScanControl.textContent = String(scanData.control_number || scanData.qr_data || '').trim() || '-';
			}

			if (recentScanPurpose) {
				recentScanPurpose.textContent = String(scanData.purpose_reason || '').trim() || '-';
			}

			if (recentScanTimeIn) {
				recentScanTimeIn.textContent = formatDateTime(scanData.entry_time);
			}

			if (recentScanTimeOut) {
				recentScanTimeOut.textContent = formatDateTime(scanData.exit_time);
			}

			if (recentScanStatus) {
				recentScanStatus.textContent = 'Exited';
			}

			recentScanEmptyState?.classList.add('d-none');
			recentScanDetails?.classList.add('show');
		};

		const showExitResultModal = (scanData) => {
			if (!exitResultModal || !scanData) {
				return;
			}

			const photoUrl = resolveVisitorPhotoUrl(scanData.visitor_photo_preview_url || scanData.visitor_photo_with_id_url);
			const controlNo = String(scanData.control_number || '').trim() || String(scanData.qr_data || '').trim() || '-';

			exitResultControlNumber.textContent = controlNo;
			exitResultPurpose.textContent = String(scanData.purpose_reason || '').trim() || '-';
			exitResultVisitorName.textContent = String(scanData.visitor_name || '').trim() || '-';
			exitResultStatus.textContent = 'Exited';
			exitResultTimeIn.textContent = formatDateTime(scanData.entry_time);
			exitResultTimeOut.textContent = formatDateTime(scanData.exit_time);

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

		const processQrData = async (rawQrData) => {
			const qrData = String(rawQrData || '').trim();
			if (!qrData) {
				return;
			}

			const now = Date.now();
			if (isProcessingScan || now < processingCooldownUntil) {
				setResult('Please wait. Still processing previous scan...', 'error');
				focusScannerInput();
				return;
			}

			isProcessingScan = true;
			processingCooldownUntil = now + 1200;
			setResult('Scan received. Processing exit...', '');
			hideFeedbackCards();
			if (scannerStatus) {
				scannerStatus.textContent = 'Validating scan against active visitor records...';
			}

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

				const successData = {
					...(payload.data || {}),
					qr_data: payload.qr_data || ''
				};

				setResult(payload.message + ' (' + payload.qr_data + ')', 'success');
				showSuccessFeedback(payload.message || 'Exit completed successfully.');
				if (scannerStatus) {
					scannerStatus.textContent = 'Exit completed. Ready for next scan.';
				}
				updateRecentScanCard(successData);
				showExitResultModal(successData);
			} catch (error) {
				setResult(error.message || 'Scan failed. Please try again.', 'error');
				showErrorFeedback(error.message || 'Scan failed. Please try again.');
				if (scannerStatus) {
					scannerStatus.textContent = 'Scan failed. Please retry scanning the pass.';
				}
			} finally {
				isProcessingScan = false;
				if (scannerInput) {
					scannerInput.value = '';
				}
				if (manualQrInput) {
					manualQrInput.value = '';
				}
			}

			if (resumeScanTimeout) {
				clearTimeout(resumeScanTimeout);
			}

			resumeScanTimeout = setTimeout(() => {
				setResult('');
				hideFeedbackCards();
				if (scannerStatus) {
					scannerStatus.textContent = 'Scanner input ready.';
				}
				focusScannerInput();
			}, 3000);

			focusScannerInput();
		};

		const normalizeManualCode = (rawValue) => String(rawValue || '').trim();

		const submitManualEntry = () => {
			if (!manualQrInput) {
				return;
			}

			const code = normalizeManualCode(manualQrInput.value);
			if (!code) {
				setResult('Please enter the QR code or control number first.', 'error');
				showErrorFeedback('Please enter the QR code or control number first.');
				manualQrInput.focus();
				return;
			}

			if (scannerStatus) {
				scannerStatus.textContent = 'Processing manual entry...';
			}
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

		scannerInput?.addEventListener('keydown', (event) => {
			if (event.key === 'Enter') {
				event.preventDefault();
				processQrData(scannerInput.value);
			}
		});

		manualQrInput?.addEventListener('keydown', (event) => {
			if (event.key === 'Enter') {
				event.preventDefault();
				submitManualEntry();
			}
		});

		clearScanButton?.addEventListener('click', () => {
			if (scannerInput) {
				scannerInput.value = '';
			}
			if (manualQrInput) {
				manualQrInput.value = '';
			}
			setResult('');
			hideFeedbackCards();
			if (scannerStatus) {
				scannerStatus.textContent = 'Scanner input ready.';
			}
			focusScannerInput();
		});

		testScannerButton?.addEventListener('click', () => {
			if (!scannerInput) {
				return;
			}
			scannerInput.value = 'TEST-SCANNER-INPUT';
			setResult('Scanner input is active. Press Enter or scan a real pass.', '');
			focusScannerInput();
		});

		exitResultDoneButton?.addEventListener('click', () => {
			closeExitResultModal();
			focusScannerInput();
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
				focusScannerInput();
			}
		});

		document.addEventListener('visibilitychange', () => {
			if (!document.hidden) {
				focusScannerInput();
			}
		});

		document.addEventListener('click', (event) => {
			const target = event.target;
			if (target instanceof HTMLElement && target.closest('button, a, input, textarea, select')) {
				return;
			}
			focusScannerInput();
		});

		window.addEventListener('load', () => {
			focusScannerInput();
			setTimeout(focusScannerInput, 150);
		});
	</script>
	@include('guard.partials.guard-responsive-script')
	@include('partials.live-auto-refresh', [
		'liveRefreshIntervalMs' => 12000,
		'liveRefreshPauseSelectors' => [
			'#exitResultModal',
			'#exitResultModal.show',
			'#exitResultModal.open',
			'.modal.show',
		],
	])
</body>
</html>