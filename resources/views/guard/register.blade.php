<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Register Visitor</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<style>
		:root {
			font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
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

		.confirmation-modal {
			position: fixed;
			inset: 0;
			z-index: 1200;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 20px;
			background: rgba(15, 23, 42, 0.62);
			backdrop-filter: blur(4px);
		}

		.confirmation-modal.is-hidden {
			display: none;
		}

		.confirmation-modal-card {
			width: min(100%, 520px);
			background: #ffffff;
			border-radius: 18px;
			box-shadow: 0 24px 80px rgba(15, 23, 42, 0.3);
			overflow: hidden;
			border: 1px solid rgba(148, 163, 184, 0.22);
		}

		.confirmation-modal-header {
			padding: 20px 24px 10px;
			border-bottom: 1px solid #e5e7eb;
		}

		.confirmation-modal-title {
			margin: 0;
			font-size: 20px;
			font-weight: 700;
			color: #0f172a;
		}

		.confirmation-modal-subtitle {
			margin: 8px 0 0;
			font-size: 14px;
			line-height: 1.5;
			color: #475569;
		}

		.confirmation-modal-body {
			padding: 18px 24px 8px;
		}

		.confirmation-photo-panel {
			display: grid;
			grid-template-columns: 132px 1fr;
			gap: 16px;
			align-items: center;
			padding: 14px;
			border-radius: 14px;
			background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
			border: 1px solid #dbe4ff;
		}

		.confirmation-photo-frame {
			width: 132px;
			height: 132px;
			border-radius: 16px;
			overflow: hidden;
			background: #0f172a;
			border: 1px solid rgba(148, 163, 184, 0.28);
		}

		.confirmation-photo-frame img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: block;
		}

		.confirmation-photo-frame.is-hidden,
		.confirmation-photo-placeholder.is-hidden {
			display: none;
		}

		.confirmation-photo-placeholder {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 100%;
			height: 100%;
			padding: 12px;
			text-align: center;
			font-size: 13px;
			font-weight: 600;
			line-height: 1.4;
			color: #e2e8f0;
			background: linear-gradient(145deg, #111827 0%, #1e293b 100%);
		}

		.confirmation-photo-copy {
			display: grid;
			gap: 8px;
		}

		.confirmation-photo-badge {
			display: inline-flex;
			align-items: center;
			width: fit-content;
			padding: 6px 10px;
			border-radius: 999px;
			background: rgba(59, 68, 151, 0.12);
			color: #3b4497;
			font-size: 12px;
			font-weight: 700;
			letter-spacing: 0.02em;
			text-transform: uppercase;
		}

		.confirmation-photo-note {
			margin: 0;
			font-size: 13px;
			line-height: 1.5;
			color: #334155;
		}

		.confirmation-summary {
			display: grid;
			gap: 10px;
			margin: 14px 0 0;
			padding: 14px;
			border-radius: 14px;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
		}

		.confirmation-summary-row {
			display: flex;
			justify-content: space-between;
			gap: 12px;
			font-size: 14px;
			line-height: 1.4;
		}

		.confirmation-summary-label {
			color: #64748b;
			font-weight: 600;
			flex-shrink: 0;
		}

		.confirmation-summary-value {
			color: #0f172a;
			font-weight: 600;
			text-align: right;
		}

		.confirmation-modal-footer {
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			padding: 18px 24px 24px;
		}

		.confirmation-modal-btn {
			border: 0;
			border-radius: 12px;
			padding: 11px 18px;
			font-size: 14px;
			font-weight: 700;
			cursor: pointer;
			min-width: 118px;
		}

		.confirmation-modal-btn.secondary {
			background: #e2e8f0;
			color: #0f172a;
		}

		.confirmation-modal-btn.primary {
			background: #3b4497;
			color: #ffffff;
		}

		.confirmation-modal-btn:hover {
			filter: brightness(0.97);
		}

		.menu {
			display: block;
		}

		.main {
			flex: 1;
			background: #f7f8ff;
			padding: 24px 32px;
			overflow-y: auto;
			margin-left: 260px;
		}

		.page-title {
			margin: 0;
			font-size: 28px;
			font-weight: 700;
			color: #0f172a;
		}

		.register-flow {
			max-width: 720px;
			margin: 0 auto;
		}

		.flow-head {
			display: flex;
			justify-content: center;
			align-items: flex-start;
			margin: 6px 0 14px;
		}

		.flow-step-meta {
			margin-top: 6px;
			text-align: left;
		}

		.flow-step-meta p {
			margin: 0;
		}

		.flow-step-name {
			font-size: 28px;
			font-weight: 700;
			color: #1f2937;
			line-height: 1;
		}

		.flow-step-count {
			font-size: 14px;
			color: #475569;
			margin-top: 6px;
		}

		.scanner-card {
			width: 100%;
			background: #ffffff;
			border: 1px solid #d9dde4;
			border-radius: 14px;
			padding: 16px;
			box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
		}

		.scanner-zone {
			background: #111827;
			border-radius: 10px;
			padding: 50px 32px;
			display: flex;
			align-items: center;
			justify-content: center;
			position: relative;
			overflow: hidden;
			width: 100%;
		}

		.camera-feed {
			position: absolute;
			inset: 0;
			width: 100%;
			height: 100%;
			object-fit: contain;
			display: block;
			background: #111827;
		}

		.frozen-frame {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			display: none;
			z-index: 3;
			max-width: 100%;
			max-height: 100%;
			object-fit: contain;
		}

		.frozen-frame.visible {
			display: block;
		}

		.scanner-overlay {
			position: absolute;
			inset: 0;
			z-index: 4;
			display: flex;
			align-items: center;
			justify-content: center;
			pointer-events: none;
		}

		.camera-status {
			margin: 10px 0 0;
			text-align: center;
			font-size: 13px;
			color: #4f5b75;
		}

		.is-hidden {
			display: none !important;
		}

		.picture-guide {
			width: min(78%, 300px);
			aspect-ratio: 3 / 4;
			position: relative;
			border-radius: 18px;
			border: 2px solid rgba(99, 116, 241, 0.9);
			background: rgba(15, 23, 42, 0.18);
			box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14);
		}

		.picture-guide .corner {
			position: absolute;
			width: 34px;
			height: 34px;
			border-color: #3f4a9f;
			border-style: solid;
		}

		.picture-guide .corner.tl {
			top: 0;
			left: 0;
			border-width: 5px 0 0 5px;
			border-top-left-radius: 16px;
		}

		.picture-guide .corner.tr {
			top: 0;
			right: 0;
			border-width: 5px 5px 0 0;
			border-top-right-radius: 16px;
		}

		.picture-guide .corner.bl {
			bottom: 0;
			left: 0;
			border-width: 0 0 5px 5px;
			border-bottom-left-radius: 16px;
		}

		.picture-guide .corner.br {
			bottom: 0;
			right: 0;
			border-width: 0 5px 5px 0;
			border-bottom-right-radius: 16px;
		}

		.picture-guide .face-oval {
			position: absolute;
			left: 50%;
			top: 50%;
			transform: translate(-50%, -52%);
			width: 58%;
			height: 68%;
			border-radius: 50% / 46%;
			border: 2px dashed rgba(191, 219, 254, 0.85);
		}

		.picture-guide .shoulder-line {
			position: absolute;
			left: 16%;
			right: 16%;
			bottom: 12%;
			height: 2px;
			background: rgba(191, 219, 254, 0.85);
			border-radius: 999px;
		}

		.picture-guide .id-holder {
			position: absolute;
			right: 10%;
			bottom: 18%;
			width: 30%;
			aspect-ratio: 1.58 / 1;
			border: 2px dashed rgba(250, 204, 21, 0.92);
			border-radius: 8px;
			background: rgba(250, 204, 21, 0.12);
		}

		.id-guide {
			width: min(93%, 430px);
			aspect-ratio: 1.58 / 1;
			position: relative;
			border-radius: 16px;
			border: 2px solid rgba(99, 116, 241, 0.95);
			background: rgba(15, 23, 42, 0.2);
			backdrop-filter: blur(1px);
			box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
		}

		.id-guide .corner {
			position: absolute;
			width: 36px;
			height: 36px;
			border-color: #3f4a9f;
			border-style: solid;
			pointer-events: none;
		}

		.id-guide .corner.tl {
			top: 0;
			left: 0;
			border-width: 5px 0 0 5px;
			border-top-left-radius: 16px;
		}

		.id-guide .corner.tr {
			top: 0;
			right: 0;
			border-width: 5px 5px 0 0;
			border-top-right-radius: 16px;
		}

		.id-guide .corner.bl {
			bottom: 0;
			left: 0;
			border-width: 0 0 5px 5px;
			border-bottom-left-radius: 16px;
		}

		.id-guide .corner.br {
			bottom: 0;
			right: 0;
			border-width: 0 5px 5px 0;
			border-bottom-right-radius: 16px;
		}

		.id-guide-layout {
			position: absolute;
			inset: 16px;
			display: grid;
			grid-template-columns: 32% 1fr;
			gap: 12px;
		}

		.id-guide-photo {
			border: 2px solid rgba(191, 219, 254, 0.75);
			border-radius: 10px;
			background: rgba(148, 163, 184, 0.18);
		}

		.id-guide-info {
			display: flex;
			flex-direction: column;
			justify-content: space-between;
		}

		.id-guide-line {
			height: 9px;
			border-radius: 999px;
			background: rgba(191, 219, 254, 0.8);
		}

		.id-guide-line.short {
			width: 64%;
		}

		.id-guide-line.medium {
			width: 82%;
		}

		.id-guide-line.long {
			width: 100%;
		}

		.id-guide-chip {
			width: 42px;
			height: 30px;
			border-radius: 6px;
			border: 2px solid rgba(250, 204, 21, 0.75);
			background: rgba(250, 204, 21, 0.16);
		}

		.scan-action {
			margin: 0;
			width: auto;
			flex: 1;
			height: 56px;
			border: 0;
			border-radius: 10px;
			background: #3e4ba0;
			color: #ffffff;
			font-size: 16px;
			font-weight: 500;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			cursor: pointer;
		}

		.scan-action svg {
			width: 20px;
			height: 20px;
		}

		.scan-action:disabled {
			opacity: 0.75;
			cursor: wait;
		}

		.scan-actions-row {
			margin: 16px auto 4px;
			width: min(100%, 380px);
			display: flex;
			gap: 10px;
		}

		.gallery-action {
			margin: 0;
			width: auto;
			flex: 1;
			height: 56px;
			border: 1px solid #3e4ba0;
			border-radius: 10px;
			background: #eef1ff;
			color: #2f3b8f;
			font-size: 13px;
			font-weight: 600;
			cursor: pointer;
		}

		.gallery-action:disabled {
			opacity: 0.75;
			cursor: wait;
		}

		.gallery-hint {
			margin: 6px auto 0;
			width: min(100%, 380px);
			text-align: center;
			font-size: 12px;
			color: #5c6785;
		}

		.id-types {
			margin: 14px auto 0;
			width: min(100%, 380px);
			background: #d8e0ec;
			border: 1px solid #b8c8e2;
			border-radius: 10px;
			padding: 10px 12px;
			color: #3e4ba0;
		}

		.id-types-title {
			margin: 0 0 8px;
			font-size: 12px;
			font-weight: 500;
		}

		.id-types-list {
			margin: 0;
			padding: 0;
			list-style: none;
		}

		.id-types-list li {
			font-size: 11px;
			line-height: 1.5;
			margin-bottom: 4px;
		}

		.id-types-list li:last-child {
			margin-bottom: 0;
		}

		.visitor-step {
			margin-top: 16px;
		}

		.visitor-card,
		.visitor-field,
		.visitor-textarea-wrap {
			background: #ffffff;
			border: 1px solid #d9dde4;
			border-radius: 12px;
			box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12);
			padding: 14px 16px;
			margin-bottom: 18px;
		}

		.visitor-card-title {
			display: block;
			font-size: 15px;
			font-weight: 600;
			color: #1f2937;
			margin-bottom: 12px;
		}

		.visitor-details-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 12px 14px;
		}

		.visitor-input-group {
			display: flex;
			flex-direction: column;
		}

		.visitor-label {
			font-size: 15px;
			color: #1f2937;
			margin-bottom: 10px;
			display: block;
		}

		.required-mark {
			color: #dc2626;
		}

		.visitor-details-row {
			display: flex;
			gap: 14px;
			align-items: center;
		}

		.visitor-avatar {
			width: 78px;
			height: 78px;
			border-radius: 14px;
			background: #f3f4f6;
			border: 1px solid #d9dde4;
			display: grid;
			place-items: center;
			font-size: 48px;
			flex-shrink: 0;
		}

		.visitor-meta {
			display: grid;
			gap: 8px;
		}

		.visitor-meta-label {
			font-size: 13px;
			color: #374151;
			margin: 0;
		}

		.visitor-meta-value {
			font-size: 34px;
			line-height: 1;
			font-weight: 600;
			color: #111827;
			margin: 0;
		}

		.visitor-meta-value.id {
			font-size: 32px;
		}

		.visitor-select,
		.visitor-input,
		.visitor-textarea {
			width: 100%;
			border: 1px solid #bcc4cf;
			border-radius: 9px;
			background: #ffffff;
			padding: 10px 12px;
			font-size: 16px;
			color: #111827;
			outline: none;
		}

		.visitor-select:focus,
		.visitor-input:focus,
		.visitor-textarea:focus {
			border-color: #5c6bc0;
			box-shadow: 0 0 0 2px rgba(92, 107, 192, 0.12);
		}

		.visitor-textarea {
			min-height: 110px;
			resize: vertical;
		}

		.office-list {
			border: 1px solid #bcc4cf;
			border-radius: 9px;
			background: #ffffff;
			padding: 8px 10px;
			display: grid;
			gap: 2px;
		}

		.office-list-note {
			margin: 2px 0;
			font-size: 14px;
			color: #4b5563;
		}

		.office-option {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 15px;
			color: #1f2937;
			padding: 6px 4px;
			cursor: pointer;
		}

		.office-option input {
			width: 16px;
			height: 16px;
		}

		.visitor-submit {
			margin: 10px auto 0;
			width: min(100%, 420px);
			height: 52px;
			border: 0;
			border-radius: 10px;
			background: #3e4ba0;
			color: #ffffff;
			font-size: 16px;
			font-weight: 700;
			cursor: pointer;
			display: block;
		}

		.registration-complete {
			margin: 12px auto 0;
			max-width: 760px;
			display: grid;
			gap: 16px;
		}

		.complete-head {
			display: grid;
			justify-items: center;
			gap: 6px;
		}

		.complete-icon {
			width: 72px;
			height: 72px;
			border-radius: 999px;
			background: #d4f5dd;
			color: #0a8d3d;
			display: grid;
			place-items: center;
			font-size: 36px;
			font-weight: 700;
		}

		.complete-title {
			margin: 0;
			font-size: 46px;
			font-weight: 700;
			color: #121212;
			line-height: 1.05;
			letter-spacing: -0.02em;
		}

		.complete-subtitle {
			margin: 0;
			color: #666;
			font-size: 14px;
		}

		.ticket-card {
			background: #fff;
			border: 1px solid #d9dde4;
			border-radius: 12px;
			box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1);
			padding: 20px 20px 16px;
		}

		.ticket-qr-area {
			display: grid;
			justify-items: center;
			gap: 8px;
		}

		.qr-box {
			width: 128px;
			height: 128px;
			background: #fff;
			display: grid;
			place-items: center;
		}

		.qr-box canvas,
		.qr-box img {
			width: 128px !important;
			height: 128px !important;
		}

		.ticket-control-label {
			margin: 0;
			font-size: 14px;
			color: #4b5563;
		}

		.ticket-control-value {
			margin: 0;
			font-size: 46px;
			font-weight: 800;
			color: #121212;
			line-height: 1;
			letter-spacing: -0.01em;
		}

		.ticket-separator {
			margin: 16px 0;
			border: 0;
			height: 3px;
			background: #d5d9e0;
		}

		.ticket-info-grid {
			display: grid;
			grid-template-columns: 110px minmax(0, 1fr) minmax(0, 1fr);
			gap: 14px 18px;
			align-items: start;
		}

		.ticket-photo-wrap {
			width: 110px;
			height: 110px;
			border-radius: 12px;
			border: 1px solid #d9dde4;
			background: #f1f5f9;
			overflow: hidden;
			display: grid;
			place-items: center;
		}

		.ticket-photo-image {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.ticket-photo-fallback {
			font-size: 42px;
		}

		.ticket-meta-label {
			margin: 0;
			font-size: 12px;
			color: #6b7280;
		}

		.ticket-meta-value {
			margin: 2px 0 10px;
			font-size: 36px;
			font-weight: 700;
			color: #111827;
			line-height: 1.25;
			word-break: break-word;
		}

		#ticketVisitorName,
		#ticketPassNumber,
		#ticketPurpose,
		#ticketDestination {
			font-size: 16px;
			margin-bottom: 8px;
		}

		.ticket-footer {
			margin: 4px 0 0;
			text-align: center;
			font-size: 12px;
			color: #6b7280;
		}

		.ticket-status {
			margin: 0;
			text-align: center;
			font-size: 13px;
			font-weight: 600;
			color: #0a8d3d;
		}

		.ticket-status.error {
			color: #b91c1c;
		}

		.ticket-actions {
			display: flex;
			justify-content: center;
			gap: 10px;
		}

		.ticket-btn {
			height: 44px;
			border-radius: 10px;
			padding: 0 18px;
			border: 1px solid #d1d5db;
			background: #fff;
			color: #111827;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.15s ease;
		}

		.ticket-btn:hover {
			transform: translateY(-1px);
			box-shadow: 0 3px 8px rgba(15, 23, 42, 0.16);
		}

		.ticket-btn.primary {
			background: #3e4ba0;
			border-color: #3e4ba0;
			color: #fff;
		}

		.type-placeholder {
			max-width: 760px;
			margin: 16px auto 0;
			background: #ffffff;
			border: 1px solid #d9dde4;
			border-radius: 16px;
			padding: 24px;
		}

		.type-placeholder h2 {
			margin: 0 0 8px;
			font-size: 24px;
			color: #0f172a;
		}

		.type-placeholder p {
			margin: 0;
			color: #475569;
		}

		.loading-overlay {
			position: absolute;
			inset: 0;
			background: rgba(0, 0, 0, 0.85);
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 10px;
			z-index: 10;
		}

		.loading-overlay.is-hidden {
			display: none !important;
		}

		.loading-content {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 16px;
		}

		.spinner {
			width: 50px;
			height: 50px;
			border: 4px solid rgba(255, 255, 255, 0.2);
			border-top-color: #3e4ba0;
			border-radius: 50%;
			animation: spin 0.8s linear infinite;
		}

		@keyframes spin {
			to {
				transform: rotate(360deg);
			}
		}

		.loading-text {
			color: #ffffff;
			font-size: 16px;
			font-weight: 500;
			text-align: center;
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

		@media (max-width: 480px) {
			.sidebar-link,
			.submenu-link,
			.logout-btn {
				font-size: 16px;
			}

			.main {
				padding: 18px 14px;
			}

			.flow-head {
				flex-direction: column;
				gap: 8px;
			}

			.flow-step-name {
				font-size: 24px;
			}

			.scanner-zone {
				padding: 28px 12px;
				min-height: 260px;
			}

			.id-guide {
				width: min(94%, 340px);
			}

			.picture-guide {
				width: min(82%, 260px);
			}

			.scan-action {
				height: 50px;
				font-size: 14px;
			}

			.scan-actions-row {
				gap: 8px;
			}

			.gallery-action {
				height: 50px;
				font-size: 12px;
			}

			.visitor-meta-value {
				font-size: 26px;
			}

			.visitor-meta-value.id {
				font-size: 24px;
			}

			.visitor-details-grid {
				grid-template-columns: 1fr;
			}

			.complete-title {
				font-size: 32px;
			}

			.ticket-control-value {
				font-size: 30px;
			}

			.ticket-info-grid {
				grid-template-columns: 1fr;
				justify-items: center;
				text-align: center;
			}

			.ticket-actions {
				flex-wrap: wrap;
			}

			.brand-title {
				font-size: 22px;
			}
		}

		@media print {
			@page {
				size: auto;
				margin: 10mm;
			}

			body.print-ticket-mode {
				background: #ffffff !important;
			}

			body.print-ticket-mode .layout {
				display: block !important;
				height: auto !important;
				overflow: visible !important;
			}

			body.print-ticket-mode .sidebar,
			body.print-ticket-mode .page-title,
			body.print-ticket-mode .flow-head,
			body.print-ticket-mode .scanner-card,
			body.print-ticket-mode .visitor-step,
			body.print-ticket-mode .ticket-status,
			body.print-ticket-mode .ticket-actions,
			body.print-ticket-mode .confirmation-modal {
				display: none !important;
			}

			body.print-ticket-mode .main {
				display: block !important;
				padding: 0 !important;
				margin: 0 !important;
				background: #ffffff !important;
			}

			body.print-ticket-mode .register-flow {
				max-width: none !important;
				margin: 0 !important;
			}

			body.print-ticket-mode #registrationCompletePanel {
				display: grid !important;
				visibility: visible !important;
				gap: 0 !important;
				justify-items: center !important;
				margin: 0 !important;
				padding: 0 !important;
			}

			body.print-ticket-mode #registrationCompletePanel .complete-header,
			body.print-ticket-mode #registrationCompletePanel #ticketSaveStatus,
			body.print-ticket-mode #registrationCompletePanel .ticket-actions {
				display: none !important;
			}

			body.print-ticket-mode #registrationTicketCard {
				display: block !important;
				position: static !important;
				transform: none !important;
				width: 180mm;
				max-width: 100% !important;
				margin: 0 auto !important;
				box-shadow: none;
				border: 1px solid #d1d5db;
				page-break-inside: avoid;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}

			body.print-ticket-mode .ticket-photo-wrap,
			body.print-ticket-mode .ticket-photo-image,
			body.print-ticket-mode .qr-box,
			body.print-ticket-mode .qr-box canvas,
			body.print-ticket-mode .qr-box img {
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
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

	<div class="confirmation-modal is-hidden" id="existingVisitorModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="existingVisitorModalTitle">
		<div class="confirmation-modal-card">
			<div class="confirmation-modal-header">
				<h2 class="confirmation-modal-title" id="existingVisitorModalTitle">Existing Visitor Found</h2>
				<p class="confirmation-modal-subtitle" id="existingVisitorModalSubtitle">We found a matching visitor record. Please confirm whether this is the same person before continuing. Tap Cancel to create a new visitor record instead.</p>
			</div>
			<div class="confirmation-modal-body">
				<div class="confirmation-photo-panel">
					<div class="confirmation-photo-frame" id="existingVisitorModalPhotoFrame">
						<img id="existingVisitorModalPhoto" alt="Saved visitor photo">
						<div class="confirmation-photo-placeholder is-hidden" id="existingVisitorModalPhotoPlaceholder">No saved photo available for validation.</div>
					</div>
					<div class="confirmation-photo-copy">
						<span class="confirmation-photo-badge">Validation Photo</span>
						<p class="confirmation-photo-note" id="existingVisitorModalValidationNote">Compare this saved photo with the person in front of you before continuing.</p>
					</div>
				</div>

				<div class="confirmation-summary">
					<div class="confirmation-summary-row">
						<span class="confirmation-summary-label">Visitor Name</span>
						<span class="confirmation-summary-value" id="existingVisitorModalName">-</span>
					</div>
					<div class="confirmation-summary-row">
						<span class="confirmation-summary-label">Contact Number</span>
						<span class="confirmation-summary-value" id="existingVisitorModalContact">-</span>
					</div>
					<div class="confirmation-summary-row">
						<span class="confirmation-summary-label">Birthday</span>
						<span class="confirmation-summary-value" id="existingVisitorModalBirthday">-</span>
					</div>
					<div class="confirmation-summary-row">
						<span class="confirmation-summary-label">Saved Address</span>
						<span class="confirmation-summary-value" id="existingVisitorModalAddressState">-</span>
					</div>
				</div>
			</div>
			<div class="confirmation-modal-footer">
				<button type="button" class="confirmation-modal-btn secondary" id="existingVisitorModalCancel">Cancel (New Visitor)</button>
				<button type="button" class="confirmation-modal-btn primary" id="existingVisitorModalConfirm">Yes, Continue</button>
			</div>
		</div>
	</div>



		<main class="main">
			@php($registerType = request('type', 'normal'))
			@if (in_array($registerType, ['normal', 'contractor', 'enrollee'], true))
				<h1 class="page-title">Register Visitor</h1>
				<section class="register-flow">
					<div class="flow-head">
						<div class="flow-step-meta">
							<p class="flow-step-name" id="flowStepName">ID Scan</p>
							<p class="flow-step-count" id="flowStepCount">Step 1 of 3</p>
						</div>
					</div>

					<div class="scanner-card">
						<div class="scanner-zone">
							<video id="cameraFeed" class="camera-feed" autoplay playsinline muted></video>
							<canvas id="frozenFrame" class="frozen-frame"></canvas>
							<div class="scanner-overlay" aria-hidden="true">
								<div class="picture-guide" id="pictureGuide">
									<span class="corner tl"></span>
									<span class="corner tr"></span>
									<span class="corner bl"></span>
									<span class="corner br"></span>
									<span class="face-oval"></span>
									<span class="shoulder-line"></span>
									<span class="id-holder"></span>
								</div>
								<div class="id-guide is-hidden" id="idGuide">
									<span class="corner tl"></span>
									<span class="corner tr"></span>
									<span class="corner bl"></span>
									<span class="corner br"></span>
									<div class="id-guide-layout">
										<span class="id-guide-photo"></span>
										<div class="id-guide-info">
											<span class="id-guide-line long"></span>
											<span class="id-guide-line medium"></span>
											<span class="id-guide-line short"></span>
											<span class="id-guide-line long"></span>
											<span class="id-guide-chip"></span>
										</div>
									</div>
								</div>
								<div class="loading-overlay is-hidden" id="loadingOverlay">
									<div class="loading-content">
										<div class="spinner"></div>
										<p class="loading-text" id="loadingText">Processing capture...</p>
									</div>
								</div>
							</div>
						</div>
						<p class="camera-status" id="cameraStatus">Starting camera...</p>
						<canvas id="captureCanvas" style="display:none;"></canvas>

						<div class="scan-actions-row">
							<button type="button" class="scan-action" id="scanAction">
								<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M7 4v3M17 4v3M4 8h16M6 20h12a2 2 0 0 0 2-2V8H4v10a2 2 0 0 0 2 2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
									<rect x="9" y="11" width="6" height="5" rx="1" fill="currentColor"/>
								</svg>
								<span id="scanActionText">Scan ID Card</span>
							</button>

							<button type="button" class="gallery-action is-hidden" id="galleryAction">Import ID from Gallery</button>
						</div>
							<input type="file" id="idGalleryInput" class="is-hidden" accept="image/*">
							<p class="gallery-hint is-hidden" id="galleryHint">If camera is unavailable, you can upload a clear photo of the ID.</p>

						<div class="id-types is-hidden" id="idTypesPanel">
							<p class="id-types-title">Supported ID Types:</p>
							<ul class="id-types-list">
								<li>- National ID / Driver's License</li>
								<li>- Company ID / Employee Badge</li>
								<li>- Passport (Photo page)</li>
							</ul>
						</div>
					</div>

					@if ($registerType === 'enrollee')
						<div class="visitor-step is-hidden" id="enrolleeStepPanel">
							<div class="visitor-card">
								<span class="visitor-card-title">Enrollee Details</span>
								<div class="visitor-details-grid">
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorFirstName">First Name <span class="required-mark">*</span></label>
										<input class="visitor-input" id="visitorFirstName" name="first_name" type="text" placeholder="First name" required>
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorLastName">Last Name <span class="required-mark">*</span></label>
										<input class="visitor-input" id="visitorLastName" name="last_name" type="text" placeholder="Last name" required>
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorBirthday">Birthday <span class="required-mark">*</span></label>
										<input class="visitor-input" id="visitorBirthday" name="birthday" type="date" required>
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorHouseNo">House No. <span class="required-mark">*</span></label>
										<input class="visitor-input" id="visitorHouseNo" name="house_no" type="text" placeholder="House no." required>
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorStreet">Street <span class="required-mark">*</span></label>
										<input class="visitor-input" id="visitorStreet" name="street" type="text" placeholder="Street" required>
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorBarangay">Barangay <span class="required-mark">*</span></label>
										<input class="visitor-input" id="visitorBarangay" name="barangay" type="text" placeholder="Barangay" required>
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorCity">City / Municipality <span class="required-mark">*</span></label>
										<input class="visitor-input" id="visitorCity" name="city_municipality" type="text" placeholder="City / municipality" required>
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorProvince">Province <span class="required-mark">*</span></label>
										<input class="visitor-input" id="visitorProvince" name="province" type="text" placeholder="Province" required>
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorRegion">Region <span class="required-mark">*</span></label>
										<input class="visitor-input" id="visitorRegion" name="region" type="text" placeholder="Region" required>
									</div>
								</div>
							</div>

							<div class="visitor-field">
								<label class="visitor-label" for="visitorPhoneNumber">Phone Number <span class="required-mark">*</span></label>
								<input class="visitor-input" id="visitorPhoneNumber" name="phone_number" type="tel" placeholder="09XXXXXXXXX" inputmode="numeric" minlength="11" maxlength="11" pattern="[0-9]{11}" required>
							</div>

							<div class="visitor-field">
								<label class="visitor-label" for="visitorIdPassNumber">ID Pass Number <span class="required-mark">*</span></label>
								<input class="visitor-input" id="visitorIdPassNumber" name="id_pass_number" type="text" placeholder="" required>
							</div>

							<div class="visitor-field">
								<label class="visitor-label" for="visitorControlNumber">Control Number <span class="required-mark">*</span></label>
								<input class="visitor-input" id="visitorControlNumber" name="control_number" type="text" placeholder="" required>
							</div>

							<button type="button" class="visitor-submit" id="generateQrBtn">Proceed to Face + ID Capture</button>
						</div>
					@else
					<div class="visitor-step is-hidden" id="visitorStepPanel">
						<div class="visitor-card">
							<span class="visitor-card-title">Visitor Details</span>
							<div class="visitor-details-grid">
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorFirstName">First Name <span class="required-mark">*</span></label>
									<input class="visitor-input" id="visitorFirstName" name="first_name" type="text" placeholder="First name" required>
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorLastName">Last Name <span class="required-mark">*</span></label>
									<input class="visitor-input" id="visitorLastName" name="last_name" type="text" placeholder="Last name" required>
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorBirthday">Birthday <span class="required-mark">*</span></label>
									<input class="visitor-input" id="visitorBirthday" name="birthday" type="date" required>
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorHouseNo">House No. <span class="required-mark">*</span></label>
									<input class="visitor-input" id="visitorHouseNo" name="house_no" type="text" placeholder="House no." required>
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorStreet">Street <span class="required-mark">*</span></label>
									<input class="visitor-input" id="visitorStreet" name="street" type="text" placeholder="Street" required>
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorBarangay">Barangay <span class="required-mark">*</span></label>
									<input class="visitor-input" id="visitorBarangay" name="barangay" type="text" placeholder="Barangay" required>
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorCity">City / Municipality <span class="required-mark">*</span></label>
									<input class="visitor-input" id="visitorCity" name="city_municipality" type="text" placeholder="City / municipality" required>
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorProvince">Province <span class="required-mark">*</span></label>
									<input class="visitor-input" id="visitorProvince" name="province" type="text" placeholder="Province" required>
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorRegion">Region <span class="required-mark">*</span></label>
									<input class="visitor-input" id="visitorRegion" name="region" type="text" placeholder="Region" required>
								</div>
							</div>
						</div>

						<div class="visitor-field">
							<label class="visitor-label" for="visitorPhoneNumber">Phone Number <span class="required-mark">*</span></label>
							<input class="visitor-input" id="visitorPhoneNumber" name="phone_number" type="tel" placeholder="09XXXXXXXXX" inputmode="numeric" minlength="11" maxlength="11" pattern="[0-9]{11}" required>
						</div>

						<div class="visitor-field">
							<label class="visitor-label" for="destinationOffice">Destination Office <span class="required-mark">*</span></label>
							@if ($registerType === 'contractor')
								<input class="visitor-input" id="destinationOfficeText" name="destination_office_text" type="text" placeholder="Enter destination office" required>
							@else
								<div class="office-list" id="destinationOffice">
									<p class="office-list-note" id="officeListNote">Loading offices...</p>
								</div>
							@endif
						</div>

						@if ($registerType === 'contractor')
							<div class="visitor-field">
								<label class="visitor-label" for="contactPerson">Contact Person <span class="required-mark">*</span></label>
								<input class="visitor-input" id="contactPerson" name="contact_person" type="text" placeholder="Enter contact person" required>
							</div>
						@endif

						<div class="visitor-field">
							<label class="visitor-label" for="visitorIdPassNumber">ID Pass Number <span class="required-mark">*</span></label>
							<input class="visitor-input" id="visitorIdPassNumber" name="id_pass_number" type="text" placeholder="" required>
						</div>

						<div class="visitor-field">
							<label class="visitor-label" for="visitorControlNumber">Control Number <span class="required-mark">*</span></label>
							<input class="visitor-input" id="visitorControlNumber" name="control_number" type="text" placeholder="" required>
						</div>

						<div class="visitor-textarea-wrap">
							<label class="visitor-label" for="visitReason">Reason For Visit <span class="required-mark">*</span></label>
							<textarea class="visitor-textarea" id="visitReason" required></textarea>
						</div>

						<button type="button" class="visitor-submit" id="generateQrBtn">Proceed to Face + ID Capture</button>
					</div>
					@endif

					<div class="registration-complete is-hidden" id="registrationCompletePanel">
						<div class="complete-head">
							<div class="complete-icon" aria-hidden="true">✓</div>
							<p class="complete-title">Registration Complete!</p>
							<p class="complete-subtitle">QR Ticket Generated Successfully</p>
						</div>

						<div class="ticket-card" id="registrationTicketCard">
							<div class="ticket-qr-area">
								<div class="qr-box" id="qrCodeContainer"></div>
								<p class="ticket-control-label">Control Number</p>
								<p class="ticket-control-value" id="ticketControlNumber">-</p>
							</div>

							<hr class="ticket-separator">

							<div class="ticket-info-grid">
								<div class="ticket-photo-wrap">
									<img id="ticketPhoto" class="ticket-photo-image is-hidden" alt="Visitor face and ID">
									<div id="ticketPhotoFallback" class="ticket-photo-fallback">👤</div>
								</div>

								<div>
									<p class="ticket-meta-label">Full Name</p>
									<p class="ticket-meta-value" id="ticketVisitorName">-</p>
									<p class="ticket-meta-label">ID Pass Number</p>
									<p class="ticket-meta-value" id="ticketPassNumber">-</p>
								</div>

								<div>
									<p class="ticket-meta-label">Purpose</p>
									<p class="ticket-meta-value" id="ticketPurpose">-</p>
									<p class="ticket-meta-label">Destination</p>
									<p class="ticket-meta-value" id="ticketDestination">-</p>
								</div>
							</div>

							<p class="ticket-footer">Registered by Officer Martinez</p>
						</div>

						<p class="ticket-status" id="ticketSaveStatus">Generating QR and saving details...</p>

						<div class="ticket-actions">
							<button type="button" class="ticket-btn" id="downloadQrBtn">Download QR</button>
							<button type="button" class="ticket-btn primary" id="printTicketBtn">Print Ticket</button>
						</div>
					</div>
				</section>
			@endif
		</main>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
	<script>
		const registerMenuGroup = document.getElementById('registerMenuGroup');
		const registerMenuToggle = document.getElementById('registerMenuToggle');
		const flowStepName = document.getElementById('flowStepName');
		const flowStepCount = document.getElementById('flowStepCount');
		const flowHead = document.querySelector('.flow-head');
		const scannerZone = document.querySelector('.scanner-zone');
		const cameraFeed = document.getElementById('cameraFeed');
		const pictureGuide = document.getElementById('pictureGuide');
		const idGuide = document.getElementById('idGuide');
		const cameraStatus = document.getElementById('cameraStatus');
		const captureCanvas = document.getElementById('captureCanvas');
		const frozenFrame = document.getElementById('frozenFrame');
		const scannerCard = document.querySelector('.scanner-card');
		const idTypesPanel = document.getElementById('idTypesPanel');
		const visitorStepPanel = document.getElementById('visitorStepPanel');
		const scanAction = document.getElementById('scanAction');
		const scanActionText = document.getElementById('scanActionText');
		const galleryAction = document.getElementById('galleryAction');
		const idGalleryInput = document.getElementById('idGalleryInput');
		const galleryHint = document.getElementById('galleryHint');
		const loadingOverlay = document.getElementById('loadingOverlay');
		const loadingText = document.getElementById('loadingText');
		const generateQrBtn = document.getElementById('generateQrBtn');
		const enrolleeStepPanel = document.getElementById('enrolleeStepPanel');
		const registrationCompletePanel = document.getElementById('registrationCompletePanel');
		const registrationTicketCard = document.getElementById('registrationTicketCard');
		const qrCodeContainer = document.getElementById('qrCodeContainer');
		const ticketControlNumber = document.getElementById('ticketControlNumber');
		const ticketVisitorName = document.getElementById('ticketVisitorName');
		const ticketPassNumber = document.getElementById('ticketPassNumber');
		const ticketPurpose = document.getElementById('ticketPurpose');
		const ticketDestination = document.getElementById('ticketDestination');
		const ticketPhoto = document.getElementById('ticketPhoto');
		const ticketPhotoFallback = document.getElementById('ticketPhotoFallback');
		const ticketSaveStatus = document.getElementById('ticketSaveStatus');
		const downloadQrBtn = document.getElementById('downloadQrBtn');
		const printTicketBtn = document.getElementById('printTicketBtn');
		const existingVisitorModal = document.getElementById('existingVisitorModal');
		const existingVisitorModalPhotoFrame = document.getElementById('existingVisitorModalPhotoFrame');
		const existingVisitorModalPhoto = document.getElementById('existingVisitorModalPhoto');
		const existingVisitorModalPhotoPlaceholder = document.getElementById('existingVisitorModalPhotoPlaceholder');
		const existingVisitorModalValidationNote = document.getElementById('existingVisitorModalValidationNote');
		const existingVisitorModalName = document.getElementById('existingVisitorModalName');
		const existingVisitorModalContact = document.getElementById('existingVisitorModalContact');
		const existingVisitorModalBirthday = document.getElementById('existingVisitorModalBirthday');
		const existingVisitorModalAddressState = document.getElementById('existingVisitorModalAddressState');
		const existingVisitorModalConfirm = document.getElementById('existingVisitorModalConfirm');
		const existingVisitorModalCancel = document.getElementById('existingVisitorModalCancel');
		const visitorPhoneNumber = document.getElementById('visitorPhoneNumber');
		const destinationOffice = document.getElementById('destinationOffice');
		const destinationOfficeText = document.getElementById('destinationOfficeText');
		const contactPerson = document.getElementById('contactPerson');
		const officeListNote = document.getElementById('officeListNote');
		const visitorFirstName = document.getElementById('visitorFirstName');
		const visitorLastName = document.getElementById('visitorLastName');
		const visitorBirthday = document.getElementById('visitorBirthday');
		const visitorHouseNo = document.getElementById('visitorHouseNo');
		const visitorStreet = document.getElementById('visitorStreet');
		const visitorBarangay = document.getElementById('visitorBarangay');
		const visitorCity = document.getElementById('visitorCity');
		const visitorProvince = document.getElementById('visitorProvince');
		const visitorRegion = document.getElementById('visitorRegion');
		const visitorIdPassNumber = document.getElementById('visitorIdPassNumber');
		const visitorControlNumber = document.getElementById('visitorControlNumber');
		const visitReason = document.getElementById('visitReason');
		const registerType = @json($registerType);
		const hasFinalStepPanel = Boolean(visitorStepPanel || enrolleeStepPanel);
		const hasRegisterFlow = Boolean(
			flowStepName && flowStepCount && scannerCard && pictureGuide && idGuide &&
			idTypesPanel && hasFinalStepPanel && scanAction && scanActionText &&
			galleryAction && galleryHint && loadingOverlay && loadingText
		);
		let activeStream = null;
		let currentStep = 1;
		let capturedPictureData = '';
		let selectedOfficeIds = [];
		let faceIdCapturePublicPath = '';
		let faceIdCapturePreviewUrl = '';
		let hasSavedRegistration = false;
		let shouldResetAfterPrint = false;
		let existingVisitorMatch = null;
		let existingVisitorConfirmed = false;
		let existingVisitorModalResolver = null;
		let autoEnrolleeOfficeNames = [];

		const formatVisitorAddress = (visitor) => {
			if (!visitor) {
				return '-';
			}

			const addressLines = [
				visitor.house_no,
				visitor.street,
				visitor.barangay,
				visitor.city_municipality,
				visitor.province,
				visitor.region,
			].map((value) => String(value || '').trim()).filter(Boolean);

			return addressLines.length ? addressLines.join(', ') : '-';
		};

		if (registerMenuGroup && registerMenuToggle) {
			registerMenuToggle.addEventListener('click', () => {
				const isOpen = registerMenuGroup.classList.toggle('open');
				registerMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		}

		const updateStepUI = () => {
			if (!hasRegisterFlow) {
				return;
			}

			const isIdStep = currentStep === 1;
			const isFormStep = currentStep === 2;
			const isPictureStep = currentStep === 3;
			const isCompleteStep = currentStep === 4;
			const isVisitorInfoStep = isFormStep && registerType !== 'enrollee';
			const isEnrolleeInfoStep = isFormStep && registerType === 'enrollee';

			flowStepName.textContent = isIdStep
				? 'ID Scan'
				: (isFormStep ? (registerType === 'enrollee' ? 'Visitor Information' : 'Visitor Information') : (isPictureStep ? 'Face + ID' : 'Registration Complete'));
			flowStepCount.textContent = isIdStep ? 'Step 1 of 3' : (isFormStep ? 'Step 2 of 3' : (isPictureStep ? 'Step 3 of 3' : 'Completed'));

			scannerCard.classList.toggle('is-hidden', isFormStep || isCompleteStep);
			if (visitorStepPanel) {
				visitorStepPanel.classList.toggle('is-hidden', !isVisitorInfoStep);
			}
			if (enrolleeStepPanel) {
				enrolleeStepPanel.classList.toggle('is-hidden', !isEnrolleeInfoStep);
			}
			if (registrationCompletePanel) {
				registrationCompletePanel.classList.toggle('is-hidden', !isCompleteStep);
			}
			if (flowHead) {
				flowHead.classList.toggle('is-hidden', isCompleteStep);
			}

			if (isFormStep) {
				ensureAutoControlNumber();
			}

			pictureGuide.classList.toggle('is-hidden', !isPictureStep);
			idGuide.classList.toggle('is-hidden', !isIdStep);
			idTypesPanel.classList.toggle('is-hidden', !isIdStep);
			galleryAction.classList.toggle('is-hidden', !isIdStep);
			galleryHint.classList.toggle('is-hidden', !isIdStep);
			scanActionText.textContent = isPictureStep ? 'Capture Face + ID' : 'Scan ID Card';
		};

		const getSelectedDestinationText = () => {
			if (registerType === 'contractor') {
				return (destinationOfficeText?.value || '').trim() || '-';
			}

			if (registerType === 'enrollee') {
				return autoEnrolleeOfficeNames.length ? autoEnrolleeOfficeNames.join(', ') : '-';
			}

			const labels = Array.from(document.querySelectorAll('.office-checkbox:checked')).map((cb) => {
				const text = cb?.parentElement?.textContent || '';
				return text.trim();
			}).filter(Boolean);

			return labels.length ? labels.join(', ') : '-';
		};

		const toTitleCase = (value) => {
			const raw = String(value || '').trim();
			if (!raw) {
				return '';
			}

			return raw
				.toLowerCase()
				.replace(/\b([a-z])/g, (match) => match.toUpperCase());
		};

		const generateControlNumber = () => {
			const year = new Date().getFullYear();
			const serial = String(Date.now()).slice(-6);
			return `${year}-${serial}`;
		};

		const ensureAutoControlNumber = () => {
			if (!visitorControlNumber) {
				return '';
			}

			visitorControlNumber.readOnly = true;
			if (!visitorControlNumber.value.trim()) {
				visitorControlNumber.value = generateControlNumber();
			}

			return visitorControlNumber.value.trim();
		};

		const getPurposeReasonValue = () => {
			if (registerType === 'enrollee') {
				return 'For Enrollment';
			}

			return (visitReason?.value || '').trim();
		};

		const renderQrTicket = (qrMeta) => {
			if (!qrMeta || !qrCodeContainer || typeof QRCode === 'undefined') {
				return false;
			}

			qrCodeContainer.innerHTML = '';
			new QRCode(qrCodeContainer, {
				text: qrMeta.qr_payload,
				width: 128,
				height: 128,
				colorDark: '#000000',
				colorLight: '#ffffff',
				correctLevel: QRCode.CorrectLevel.M,
			});

			ticketControlNumber.textContent = qrMeta.control_number || '-';
			ticketVisitorName.textContent = `${toTitleCase(visitorFirstName?.value)} ${toTitleCase(visitorLastName?.value)}`.trim() || '-';
			ticketPassNumber.textContent = (visitorIdPassNumber?.value || '').trim() || '-';
			ticketPurpose.textContent = getPurposeReasonValue() || '-';
			ticketDestination.textContent = getSelectedDestinationText();

			if (ticketPhoto && faceIdCapturePreviewUrl) {
				ticketPhoto.src = faceIdCapturePreviewUrl;
				ticketPhoto.classList.remove('is-hidden');
				ticketPhotoFallback?.classList.add('is-hidden');
				ticketPhoto.onerror = () => {
					ticketPhoto.classList.add('is-hidden');
					ticketPhotoFallback?.classList.remove('is-hidden');
				};
			} else {
				ticketPhoto?.classList.add('is-hidden');
				ticketPhotoFallback?.classList.remove('is-hidden');
			}

			return true;
		};

		const setCameraState = (isOn, message) => {
			scannerZone.classList.toggle('camera-on', isOn);
			cameraStatus.textContent = message;
			if (!isOn) {
				scanActionText.textContent = 'Retry Camera';
			}
			scanAction.disabled = false;
		};

		const setScannerAspectRatio = (width, height) => {
			if (!width || !height) {
				return;
			}

			scannerZone.style.aspectRatio = `${width} / ${height}`;
		};

		const releaseCamera = () => {
			if (!activeStream) {
				return;
			}

			activeStream.getTracks().forEach((track) => track.stop());
			activeStream = null;
			cameraFeed.srcObject = null;
		};

		const freezeCurrentFrame = () => {
			if (!cameraFeed.videoWidth || !cameraFeed.videoHeight) {
				return false;
			}

			const frozenCtx = frozenFrame.getContext('2d');
			frozenFrame.width = cameraFeed.videoWidth;
			frozenFrame.height = cameraFeed.videoHeight;
			frozenCtx.drawImage(cameraFeed, 0, 0);
			frozenFrame.classList.add('visible');

			cameraFeed.pause();
			releaseCamera();

			return true;
		};

		const freezeDataUrlFrame = (dataUrl, width, height) => {
			const frameImage = new Image();

			return new Promise((resolve, reject) => {
				frameImage.onload = () => {
					const drawWidth = width || frameImage.naturalWidth;
					const drawHeight = height || frameImage.naturalHeight;

					setScannerAspectRatio(drawWidth, drawHeight);
					releaseCamera();

					const frozenCtx = frozenFrame.getContext('2d');
					frozenFrame.width = drawWidth;
					frozenFrame.height = drawHeight;
					frozenCtx.drawImage(frameImage, 0, 0, drawWidth, drawHeight);
					frozenFrame.classList.add('visible');
					resolve();
				};

				frameImage.onerror = () => {
					reject(new Error('Failed to load selected image.'));
				};

				frameImage.src = dataUrl;
			});
		};

		const clearFrozenFrame = () => {
			frozenFrame.classList.remove('visible');
		};

		const startCamera = async () => {
			if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
				setCameraState(false, 'Camera access is not supported in this browser.');
				return;
			}

			scanAction.disabled = true;

			try {
				releaseCamera();

				const stream = await navigator.mediaDevices.getUserMedia({
					video: { facingMode: 'environment' },
					audio: false
				});

				activeStream = stream;
				cameraFeed.srcObject = stream;

				// Set scanner-zone aspect ratio based on camera stream dimensions
				cameraFeed.onloadedmetadata = () => {
					const width = cameraFeed.videoWidth;
					const height = cameraFeed.videoHeight;
					setScannerAspectRatio(width, height);
				};

				setCameraState(true, currentStep === 3
					? 'Camera is ready. Center your face and hold your ID beside it.'
					: 'Camera is ready. Position the ID inside the frame.');
			} catch (error) {
				setCameraState(false, 'Camera permission denied or unavailable. Click Retry Camera after allowing access.');
			}
		};

		const capturePicture = () => {
			if (hasSavedRegistration) {
				cameraStatus.textContent = 'Registration is already completed.';
				return;
			}

			if (!cameraFeed.videoWidth || !cameraFeed.videoHeight) {
				cameraStatus.textContent = 'Waiting for camera feed. Try again in a second.';
				return;
			}

			captureCanvas.width = cameraFeed.videoWidth;
			captureCanvas.height = cameraFeed.videoHeight;
			const context = captureCanvas.getContext('2d');
			context.drawImage(cameraFeed, 0, 0, captureCanvas.width, captureCanvas.height);
			 capturedPictureData = captureCanvas.toDataURL('image/jpeg', 0.70);
			loadingOverlay.classList.remove('is-hidden');
			loadingText.textContent = 'Processing capture...';
			scanAction.disabled = true;

			// Send image to server for saving
			const formData = new FormData();
			formData.append('image', capturedPictureData);
			formData.append('step', 3);

			fetch('/guard/capture', {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
				},
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					faceIdCapturePublicPath = data.bucket_file_path || data.path || '';
					faceIdCapturePreviewUrl = data.preview_url || data.public_url || data.path || '';

					loadingText.textContent = 'Generating QR ticket...';
					const qrMeta = createQrMeta();

					if (!qrMeta) {
						throw new Error('Failed to generate QR data. Please try again.');
					}

					if (!renderQrTicket(qrMeta)) {
						throw new Error('Failed to generate QR ticket. Please try again.');
					}

					currentStep = 4;
					updateStepUI();
					releaseCamera();
					clearFrozenFrame();
					ticketSaveStatus.textContent = 'Saving visitor details...';
					ticketSaveStatus.classList.remove('error');

					loadingText.textContent = 'Saving visitor details...';
					saveNormalVisitorRegistration(qrMeta)
						.then(() => {
							hasSavedRegistration = true;
							ticketSaveStatus.textContent = 'Visitor details saved successfully.';
							ticketSaveStatus.classList.remove('error');
							loadingText.textContent = 'QR generated and visitor saved successfully.';
							setTimeout(() => {
								loadingOverlay.classList.add('is-hidden');
								scanAction.disabled = true;
								cameraStatus.textContent = 'Registration completed successfully.';
							}, 1000);
						})
						.catch((error) => {
							ticketSaveStatus.textContent = error.message || 'Failed to save visitor details.';
							ticketSaveStatus.classList.add('error');
							loadingText.textContent = error.message || 'Failed to save visitor details. Try again.';
							setTimeout(() => {
								loadingOverlay.classList.add('is-hidden');
								scanAction.disabled = true;
							}, 1500);
						});
				} else {
					loadingText.textContent = 'Failed to save. Try again.';
					setTimeout(() => {
						loadingOverlay.classList.add('is-hidden');
						clearFrozenFrame();
						scanAction.disabled = false;
						// Restart camera on failure
						startCamera();
					}, 2000);
				}
			})
			.catch(error => {
				console.error('Capture error:', error);
				loadingText.textContent = 'Error saving capture. Try again.';
				setTimeout(() => {
					loadingOverlay.classList.add('is-hidden');
					clearFrozenFrame();
					scanAction.disabled = false;
					// Restart camera on error
					startCamera();
				}, 2000);
			});
		};

		const createQrMeta = () => {
			const controlNumber = ensureAutoControlNumber();
			if (!controlNumber) {
				return null;
			}

			const qrToken = `QR-${Date.now()}-${Math.random().toString(36).slice(2, 8).toUpperCase()}`;
			const firstName = toTitleCase(visitorFirstName?.value);
			const lastName = toTitleCase(visitorLastName?.value);
			const visitorName = `${firstName} ${lastName}`.trim();
			const passNumber = (visitorIdPassNumber?.value || '').trim();
			const destination = getSelectedDestinationText();
			const issuedAt = new Date().toISOString();

			const qrPayloadData = {
				control_number: controlNumber,
				qr_token: qrToken,
				visitor_name: visitorName || null,
				pass_number: passNumber || null,
				register_type: registerType || 'normal',
				purpose_reason: getPurposeReasonValue() || null,
				destination: destination !== '-' ? destination : null,
				issued_at: issuedAt,
			};

			return {
				control_number: controlNumber,
				qr_token: qrToken,
				qr_payload: JSON.stringify(qrPayloadData),
			};
		};

		const saveNormalVisitorRegistration = async (qrMeta) => {
			const payload = {
				register_type: registerType,
				first_name: toTitleCase(visitorFirstName?.value),
				last_name: toTitleCase(visitorLastName?.value),
				house_no: visitorHouseNo?.value.trim() || '',
				street: visitorStreet?.value.trim() || '',
				barangay: visitorBarangay?.value.trim() || '',
				city_municipality: visitorCity?.value.trim() || '',
				province: visitorProvince?.value.trim() || '',
				region: visitorRegion?.value.trim() || '',
				contact_no: visitorPhoneNumber?.value.trim() || '',
				birthday: visitorBirthday?.value || null,
				pass_number: visitorIdPassNumber?.value.trim() || '',
				control_number: qrMeta?.control_number || ensureAutoControlNumber(),
				purpose_reason: getPurposeReasonValue(),
				destination_office_text: registerType === 'contractor'
					? (destinationOfficeText?.value.trim() || '')
					: null,
				contact_person: registerType === 'contractor'
					? (contactPerson?.value.trim() || '')
					: null,
				office_ids: selectedOfficeIds.map((value) => Number(value)).filter((value) => Number.isInteger(value) && value > 0),
				visitor_photo_with_id_url: faceIdCapturePublicPath || null,
				qr_token: qrMeta?.qr_token || null,
				qr_payload: qrMeta?.qr_payload || null,
				existing_visitor_confirmed: Boolean(existingVisitorConfirmed && existingVisitorMatch?.exists),
				existing_visitor_id: existingVisitorConfirmed && existingVisitorMatch?.exists
					? Number(existingVisitorMatch.visitor_id || 0)
					: null,
			};

			const response = await fetch('/guard/register/visitor', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
				},
				body: JSON.stringify(payload)
			});

			const data = await response.json().catch(() => ({}));
			if (!response.ok || !data.success) {
				throw new Error(data.message || 'Failed to save visitor details.');
			}

			return data;
		};

		const applyExistingVisitorData = (existingVisitor) => {
			if (!existingVisitor) {
				return;
			}

			if (visitorFirstName && existingVisitor.first_name) {
				visitorFirstName.value = toTitleCase(existingVisitor.first_name);
			}
			if (visitorLastName && existingVisitor.last_name) {
				visitorLastName.value = toTitleCase(existingVisitor.last_name);
			}
			if (visitorBirthday && existingVisitor.birthday) {
				visitorBirthday.value = String(existingVisitor.birthday).trim();
			}
			if (visitorHouseNo && existingVisitor.house_no) {
				visitorHouseNo.value = String(existingVisitor.house_no).trim();
			}
			if (visitorStreet && existingVisitor.street) {
				visitorStreet.value = String(existingVisitor.street).trim();
			}
			if (visitorBarangay && existingVisitor.barangay) {
				visitorBarangay.value = String(existingVisitor.barangay).trim();
			}
			if (visitorCity && existingVisitor.city_municipality) {
				visitorCity.value = String(existingVisitor.city_municipality).trim();
			}
			if (visitorProvince && existingVisitor.province) {
				visitorProvince.value = String(existingVisitor.province).trim();
			}
			if (visitorRegion && existingVisitor.region) {
				visitorRegion.value = String(existingVisitor.region).trim();
			}
			if (visitorPhoneNumber && existingVisitor.contact_no) {
				visitorPhoneNumber.value = String(existingVisitor.contact_no).replace(/\D/g, '').slice(0, 11);
			}
			if (visitorControlNumber && existingVisitor.control_number) {
				visitorControlNumber.value = String(existingVisitor.control_number).trim();
			}
		};

		const closeExistingVisitorModal = (result) => {
			if (existingVisitorModal) {
				existingVisitorModal.classList.add('is-hidden');
				existingVisitorModal.setAttribute('aria-hidden', 'true');
			}
			if (existingVisitorModalPhoto) {
				existingVisitorModalPhoto.removeAttribute('src');
				existingVisitorModalPhoto.classList.add('is-hidden');
			}
			if (existingVisitorModalPhotoPlaceholder) {
				existingVisitorModalPhotoPlaceholder.classList.add('is-hidden');
			}

			if (existingVisitorModalResolver) {
				existingVisitorModalResolver(result);
				existingVisitorModalResolver = null;
			}
		};

		const openExistingVisitorModal = (existingVisitor) => {
			if (!existingVisitor || !existingVisitor.exists || !existingVisitorModal) {
				return Promise.resolve(false);
			}

			const fullName = `${toTitleCase(existingVisitor.first_name)} ${toTitleCase(existingVisitor.last_name)}`.trim() || 'Unknown visitor';
			const contactNo = String(existingVisitor.contact_no || '-').trim() || '-';
			const birthday = String(existingVisitor.birthday || '-').trim() || '-';
			const addressText = formatVisitorAddress(existingVisitor);
			const previewUrl = String(existingVisitor.photo_preview_url || existingVisitor.photo_path || '').trim();
			const hasPreviewPhoto = Boolean(previewUrl);

			if (existingVisitorModalName) {
				existingVisitorModalName.textContent = fullName;
			}
			if (existingVisitorModalContact) {
				existingVisitorModalContact.textContent = contactNo;
			}
			if (existingVisitorModalBirthday) {
				existingVisitorModalBirthday.textContent = birthday;
			}
			if (existingVisitorModalAddressState) {
				existingVisitorModalAddressState.textContent = addressText;
			}
			if (existingVisitorModalValidationNote) {
				existingVisitorModalValidationNote.textContent = hasPreviewPhoto
					? 'Compare this saved photo with the person in front of you before continuing.'
					: 'No saved photo was found, so validation is limited to the visitor details.';
			}
			if (existingVisitorModalPhotoFrame) {
				existingVisitorModalPhotoFrame.classList.remove('is-hidden');
			}
			if (existingVisitorModalPhotoPlaceholder) {
				existingVisitorModalPhotoPlaceholder.classList.toggle('is-hidden', hasPreviewPhoto);
			}
			if (existingVisitorModalPhoto) {
				existingVisitorModalPhoto.classList.toggle('is-hidden', !hasPreviewPhoto);
				existingVisitorModalPhoto.onload = () => {
					if (existingVisitorModalPhotoFrame) {
						existingVisitorModalPhotoFrame.classList.remove('is-hidden');
					}
					if (existingVisitorModalPhotoPlaceholder) {
						existingVisitorModalPhotoPlaceholder.classList.add('is-hidden');
					}
					existingVisitorModalPhoto.classList.remove('is-hidden');
				};
				existingVisitorModalPhoto.onerror = () => {
					if (existingVisitorModalPhotoPlaceholder) {
						existingVisitorModalPhotoPlaceholder.classList.remove('is-hidden');
					}
					existingVisitorModalPhoto.classList.add('is-hidden');
					if (existingVisitorModalPhotoFrame) {
						existingVisitorModalPhotoFrame.classList.remove('is-hidden');
					}
				};
				existingVisitorModalPhoto.src = hasPreviewPhoto ? previewUrl : '';
			}
			if (existingVisitorModalConfirm) {
				existingVisitorModalConfirm.disabled = false;
				existingVisitorModalConfirm.title = hasPreviewPhoto
					? 'Continue after visually validating the saved photo.'
					: 'Continue with limited validation because no saved photo is available.';
			}

			existingVisitorModal.classList.remove('is-hidden');
			existingVisitorModal.setAttribute('aria-hidden', 'false');

			return new Promise((resolve) => {
				existingVisitorModalResolver = resolve;
			});
		};

		const completeNormalVisitorRegistration = async (qrMeta) => {
			if (!renderQrTicket(qrMeta)) {
				throw new Error('Failed to generate QR ticket. Please try again.');
			}

			currentStep = 4;
			updateStepUI();
			releaseCamera();
			clearFrozenFrame();
			ticketSaveStatus.textContent = 'Saving visitor details...';
			ticketSaveStatus.classList.remove('error');

			loadingText.textContent = 'Saving visitor details...';
			await saveNormalVisitorRegistration(qrMeta);

			hasSavedRegistration = true;
			ticketSaveStatus.textContent = 'Visitor details saved successfully.';
			ticketSaveStatus.classList.remove('error');
			loadingText.textContent = 'QR generated and visitor saved successfully.';
		};

		const parseIdOnlyAndProceed = (capturedIdData, progressText = 'Parsing ID scan...', options = {}) => {
			const {
				restartCameraOnError = true,
				showFrozenAfterSuccess = false
			} = options;

			loadingOverlay.classList.remove('is-hidden');
			loadingText.textContent = progressText;
			scanAction.disabled = true;
			galleryAction.disabled = true;

			Promise.resolve()
				.then(() => parseAndFillIdData(capturedIdData))
				.then(async (parseResult) => {
					const parsedSuccessfully = Boolean(parseResult?.parsedSuccessfully);
					existingVisitorMatch = parseResult?.existingVisitor || null;
					existingVisitorConfirmed = false;

					if (existingVisitorMatch && existingVisitorMatch.exists) {
						existingVisitorConfirmed = await openExistingVisitorModal(existingVisitorMatch);
						if (existingVisitorConfirmed) {
							applyExistingVisitorData(existingVisitorMatch);
						} else {
							existingVisitorMatch = null;
						}
					}

					releaseCamera();
					if (!showFrozenAfterSuccess) {
						clearFrozenFrame();
					}
					loadingOverlay.classList.add('is-hidden');
					scanAction.disabled = false;
					galleryAction.disabled = false;
					currentStep = 2;
					updateStepUI();
					if (existingVisitorConfirmed) {
						cameraStatus.textContent = 'Existing visitor confirmed. Review the details and generate the QR ticket.';
					} else {
						cameraStatus.textContent = parsedSuccessfully
							? 'ID parsed successfully. Verify details before proceeding.'
							: 'ID parsed with limited data. Please complete missing details manually.';
					}
				})
				.catch(() => {
					existingVisitorMatch = null;
					existingVisitorConfirmed = false;
					loadingText.textContent = 'Failed to parse ID. You can fill details manually.';
					setTimeout(() => {
						loadingOverlay.classList.add('is-hidden');
						if (!showFrozenAfterSuccess) {
							clearFrozenFrame();
						}
						scanAction.disabled = false;
						galleryAction.disabled = false;
						currentStep = 2;
						updateStepUI();
						if (restartCameraOnError) {
							releaseCamera();
						}
					}, 1200);
				});
		};

		const captureIdAndProceed = () => {
			if (!cameraFeed.videoWidth || !cameraFeed.videoHeight) {
				cameraStatus.textContent = 'Waiting for camera feed. Try again in a second.';
				return;
			}

			captureCanvas.width = cameraFeed.videoWidth;
			captureCanvas.height = cameraFeed.videoHeight;
			const context = captureCanvas.getContext('2d');
			context.drawImage(cameraFeed, 0, 0, captureCanvas.width, captureCanvas.height);

			freezeCurrentFrame();

			// Convert canvas to Blob and proceed (avoid base64 encoding)
			captureCanvas.toBlob((blob) => {
				if (!blob) {
					console.error('❌ Failed to create blob from canvas');
					return;
				}
				console.log('✓ Canvas blob created, size:', blob.size);
				parseIdOnlyAndProceed(blob, 'Parsing ID scan...');
			}, 'image/jpeg', 0.85);
		};

		const parseAndFillIdData = (capturedIdData) => {
			console.log('=== parseAndFillIdData START ===');
			console.log('capturedIdData:', typeof capturedIdData, capturedIdData ? 'present' : 'MISSING');
			
			if (!capturedIdData) {
				console.error('❌ FATAL: capturedIdData is missing!');
				return Promise.reject(new Error('Missing ID scan image'));
			}

			const formData = new FormData();
			formData.append('image', capturedIdData, 'id-scan.jpg');
			formData.append('id_type', 'auto');
			formData.append('register_type', registerType || 'normal');

			console.log('✓ FormData prepared with Blob file, size:', capturedIdData.size);

			return fetch('/guard/parse-id', {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
				},
				body: formData
			})
			.then(response => {
				console.log('✓ Got response, status:', response.status);
				return response.json().then(data => ({ status: response.status, data }));
			})
			.then(({ status, data }) => {
				console.log('✓ Response parsed, data:', data);

				if (!data.success) {
					console.warn('❌ OCR parse failed:', data.message);
					console.warn('Raw OCR text:', data.raw_text);
					return {
						parsedSuccessfully: false,
						existingVisitor: null,
					};
				}

				console.log('✓ OCR SUCCESS! Extracted:', data.extracted_data);
				console.log('✓ Form data to fill:', data.form_data);

				// Auto-fill form with extracted data
				const fillData = data.form_data || {};
				console.log('✓ About to call autofillVisitorForm...');
				autofillVisitorForm(fillData);
				console.log('✓ autofillVisitorForm complete');

				const existingVisitor = data.existing_visitor && data.existing_visitor.exists
					? data.existing_visitor
					: null;

				return {
					parsedSuccessfully: true,
					existingVisitor,
				};
			})
			.catch(error => {
				console.error('❌ FATAL FETCH ERROR:', error);
				console.error(error.stack);
				throw error;
			});
		};

		const autofillVisitorForm = (formData) => {
			if (!visitorStepPanel) {
				console.warn('Visitor step panel not found');
				return;
			}

			console.log('Autofilling form with:', formData);

			// Map of form field IDs to data keys
			const fieldMapping = {
				'visitorFirstName': 'first_name',
				'visitorLastName': 'last_name',
				'visitorBirthday': 'birthday',
				'visitorHouseNo': 'house_no',
				'visitorStreet': 'street',
				'visitorBarangay': 'barangay',
				'visitorCity': 'city_municipality',
				'visitorProvince': 'province',
				'visitorRegion': 'region'
			};

			Object.entries(fieldMapping).forEach(([elementId, dataKey]) => {
				const element = document.getElementById(elementId);
				if (element && formData[dataKey]) {
					console.log(`Filling ${elementId} with ${formData[dataKey]}`);
					const normalizedValue = (dataKey === 'first_name' || dataKey === 'last_name')
						? toTitleCase(formData[dataKey])
						: String(formData[dataKey]).trim();
					element.value = normalizedValue;
					element.dispatchEvent(new Event('change', { bubbles: true }));
				} else if (!element) {
					console.warn(`Element ${elementId} not found`);
				}
			});
		};

		const importIdFromGallery = (file) => {
			if (!file || !file.type.startsWith('image/')) {
				alert('Please choose a valid image file.');
				return;
			}

			const reader = new FileReader();
			reader.onload = () => {
				if (typeof reader.result !== 'string') {
					alert('Unable to read selected image. Please try another file.');
					return;
				}

				freezeDataUrlFrame(reader.result)
					.then(() => {
						cameraStatus.textContent = 'Imported image ready. Parsing ID from gallery...';
						parseIdOnlyAndProceed(file, 'Parsing ID from gallery...', {
							restartCameraOnError: false,
							showFrozenAfterSuccess: true
						});
					})
					.catch(() => {
						alert('Unable to preview selected image. Please try another file.');
					});
			};
			reader.onerror = () => {
				alert('Unable to read selected image. Please try another file.');
			};
			reader.readAsDataURL(file);
		};

		scanAction?.addEventListener('click', () => {
			if (!activeStream) {
				startCamera();
				return;
			}

			if (currentStep === 1) {
				captureIdAndProceed();
				return;
			}

			if (currentStep === 3) {
				capturePicture();
			}
		});

		downloadQrBtn?.addEventListener('click', async () => {
			if (!registrationTicketCard) {
				alert('Ticket is not ready yet.');
				return;
			}

			if (typeof html2canvas === 'undefined') {
				alert('Ticket download tool is not ready. Please refresh and try again.');
				return;
			}

			try {
				downloadQrBtn.disabled = true;
				const originalText = downloadQrBtn.textContent;
				downloadQrBtn.textContent = 'Preparing...';

				const canvas = await html2canvas(registrationTicketCard, {
					backgroundColor: '#ffffff',
					scale: 2,
					useCORS: true,
					allowTaint: false,
				});

				const link = document.createElement('a');
				const controlNo = (ticketControlNumber?.textContent || 'visitor-ticket').trim();
				link.href = canvas.toDataURL('image/png');
				link.download = `${controlNo}-ticket.png`;
				link.click();

				downloadQrBtn.textContent = originalText || 'Download QR';
				downloadQrBtn.disabled = false;
			} catch (error) {
				console.error('Failed to download full ticket image:', error);
				alert('Failed to download ticket image. Please try again.');
				downloadQrBtn.disabled = false;
				downloadQrBtn.textContent = 'Download QR';
			}
		});

		const printTicketInNewWindow = () => {
			if (!registrationTicketCard) {
				alert('Ticket is not ready to print yet.');
				shouldResetAfterPrint = false;
				return;
			}

			const sourceStyle = document.querySelector('style')?.textContent || '';
			const ticketMarkup = registrationTicketCard.outerHTML;
			const printWindow = window.open('', '_blank', 'width=980,height=1200');

			if (!printWindow) {
				alert('Unable to open print window. Please allow pop-ups for this site.');
				shouldResetAfterPrint = false;
				return;
			}

			printWindow.document.open();
			printWindow.document.write(`<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>QR Ticket</title>
<style>
${sourceStyle}
body {
	margin: 0;
	padding: 10mm;
	background: #ffffff;
	font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
}
.ticket-card {
	width: 180mm;
	max-width: 100%;
	margin: 0 auto;
	box-shadow: none;
	border: 1px solid #d1d5db;
}
@media print {
	@page { margin: 10mm; }
	body { padding: 0; }
	.ticket-card {
		width: 180mm;
		max-width: 100%;
		page-break-inside: avoid;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
}
</style>
</head>
<body>
${ticketMarkup}
</body>
</html>`);
			printWindow.document.close();

			let resetHandled = false;
			const finalizeAfterPrint = () => {
				if (resetHandled) {
					return;
				}

				resetHandled = true;
				window.removeEventListener('focus', fallbackOnFocus);

				if (shouldResetAfterPrint) {
					shouldResetAfterPrint = false;
					resetRegistrationFlowToStepOne();
				}

				if (!printWindow.closed) {
					printWindow.close();
				}
			};

			const fallbackOnFocus = () => {
				if (!shouldResetAfterPrint) {
					return;
				}

				setTimeout(() => {
					if (shouldResetAfterPrint) {
						finalizeAfterPrint();
					}
				}, 120);
			};

			const tryPrint = () => {
				printWindow.onafterprint = finalizeAfterPrint;
				window.addEventListener('focus', fallbackOnFocus, { once: true });
				printWindow.focus();
				printWindow.print();
			};

			if (printWindow.document.readyState === 'complete') {
				setTimeout(tryPrint, 80);
			} else {
				printWindow.addEventListener('load', () => setTimeout(tryPrint, 80), { once: true });
			}
		};

		printTicketBtn?.addEventListener('click', () => {
			shouldResetAfterPrint = true;
			printTicketInNewWindow();
		});

		galleryAction?.addEventListener('click', () => {
			if (currentStep !== 1) {
				return;
			}

			idGalleryInput?.click();
		});

		idGalleryInput?.addEventListener('change', (event) => {
			if (currentStep !== 1) {
				return;
			}

			const input = event.target;
			const selectedFile = input?.files?.[0];
			if (selectedFile) {
				importIdFromGallery(selectedFile);
			}

			input.value = '';
		});

		generateQrBtn?.addEventListener('click', () => {
			if (hasSavedRegistration) {
				alert('Registration has already been completed.');
				return;
			}

			ensureAutoControlNumber();

			const activeStepPanel = registerType === 'enrollee' ? enrolleeStepPanel : visitorStepPanel;
			const requiredFields = Array.from(activeStepPanel?.querySelectorAll('.visitor-input[required], .visitor-textarea[required]') || []);

			for (const field of requiredFields) {
				if (!field.value.trim()) {
					field.focus();
					alert('Please complete all required fields.');
					return;
				}
			}

			const cleanPhone = (visitorPhoneNumber?.value || '').replace(/\D/g, '');
			if (visitorPhoneNumber && cleanPhone.length !== 11) {
				visitorPhoneNumber.focus();
				alert('Phone Number must be exactly 11 digits.');
				return;
			}

			if (registerType === 'contractor') {
				if (!destinationOfficeText?.value.trim()) {
					destinationOfficeText?.focus();
					alert('Please enter Destination Office.');
					return;
				}
			} else if ((registerType === 'normal' || registerType === 'enrollee') && !selectedOfficeIds.length) {
				destinationOffice?.scrollIntoView({ behavior: 'smooth', block: 'center' });
				alert(registerType === 'enrollee'
					? 'No enrollee destination offices are available. Please check enrollee steps setup.'
					: 'Please select at least one Destination Office.');
				return;
			}

			if (existingVisitorConfirmed && existingVisitorMatch?.exists) {
				const previewUrl = String(existingVisitorMatch.photo_preview_url || existingVisitorMatch.photo_path || '').trim();
				const storagePath = String(existingVisitorMatch.photo_path || existingVisitorMatch.photo_preview_url || '').trim();

				if (!previewUrl) {
					alert('Existing visitor has no saved photo. Please continue to Face + ID capture.');
					existingVisitorConfirmed = false;
					existingVisitorMatch = null;
				} else {
					faceIdCapturePreviewUrl = previewUrl;
					faceIdCapturePublicPath = storagePath;

					loadingOverlay.classList.remove('is-hidden');
					loadingText.textContent = 'Generating QR ticket...';

					const qrMeta = createQrMeta();
					if (!qrMeta) {
						loadingOverlay.classList.add('is-hidden');
						alert('Failed to generate QR data. Please try again.');
						return;
					}

					completeNormalVisitorRegistration(qrMeta)
						.then(() => {
							setTimeout(() => {
								loadingOverlay.classList.add('is-hidden');
								scanAction.disabled = true;
								cameraStatus.textContent = 'Registration completed successfully using existing visitor photo.';
							}, 1000);
						})
						.catch((error) => {
							ticketSaveStatus.textContent = error.message || 'Failed to save visitor details.';
							ticketSaveStatus.classList.add('error');
							loadingText.textContent = error.message || 'Failed to save visitor details. Try again.';
							setTimeout(() => {
								loadingOverlay.classList.add('is-hidden');
								scanAction.disabled = true;
							}, 1500);
						});

					return;
				}
			}

			if (registerType !== 'normal') {
				currentStep = 3;
				updateStepUI();
				clearFrozenFrame();
				cameraStatus.textContent = 'Proceed to final step: capture your face with ID.';
				startCamera();
				return;
			}

			currentStep = 3;
			updateStepUI();
			clearFrozenFrame();
			cameraStatus.textContent = 'Proceed to final step: capture your face with ID.';
			startCamera();
		});

		visitorControlNumber?.addEventListener('focus', () => {
			ensureAutoControlNumber();
		});

		visitorControlNumber?.addEventListener('blur', () => {
			ensureAutoControlNumber();
		});

		visitorPhoneNumber?.addEventListener('input', () => {
			const digitsOnly = visitorPhoneNumber.value.replace(/\D/g, '').slice(0, 11);
			if (visitorPhoneNumber.value !== digitsOnly) {
				visitorPhoneNumber.value = digitsOnly;
			}
		});

		const renderOfficeList = (offices) => {
			autoEnrolleeOfficeNames = [];

			const normalizedOffices = Array.isArray(offices)
				? offices
					.map((office) => ({
						office_id: Number(office?.office_id),
						office_name: String(office?.office_name || '').trim(),
					}))
					.filter((office) => Number.isInteger(office.office_id) && office.office_id > 0 && office.office_name)
				: [];

			if (registerType === 'enrollee') {
				selectedOfficeIds = normalizedOffices.map((office) => String(office.office_id));
				autoEnrolleeOfficeNames = normalizedOffices.map((office) => office.office_name);
				return;
			}

			if (!destinationOffice) {
				return;
			}

			destinationOffice.innerHTML = '';

			if (!normalizedOffices.length) {
				const emptyState = document.createElement('p');
				emptyState.className = 'office-list-note';
				emptyState.textContent = 'No active offices found.';
				destinationOffice.appendChild(emptyState);
				return;
			}

			normalizedOffices.forEach((office) => {
				const label = document.createElement('label');
				label.className = 'office-option';

				const checkbox = document.createElement('input');
				checkbox.type = 'checkbox';
				checkbox.className = 'office-checkbox';
				checkbox.value = String(office.office_id);

				checkbox.addEventListener('change', () => {
					selectedOfficeIds = Array.from(destinationOffice.querySelectorAll('.office-checkbox:checked'))
						.map((cb) => cb.value);
				});

				label.appendChild(checkbox);
				label.appendChild(document.createTextNode(office.office_name));
				destinationOffice.appendChild(label);
			});
		};

		const fetchOffices = async () => {
			if (officeListNote) {
				officeListNote.textContent = 'Loading offices...';
			}

			try {
				const response = await fetch(`/guard/offices?register_type=${encodeURIComponent(registerType || 'normal')}`);
				const data = await response.json();

				if (!response.ok || !data.success) {
					throw new Error(data.message || 'Failed to load offices');
				}

				renderOfficeList(data.offices || []);
			} catch (error) {
				selectedOfficeIds = [];
				autoEnrolleeOfficeNames = [];

				if (destinationOffice) {
					destinationOffice.innerHTML = '';
					const errorState = document.createElement('p');
					errorState.className = 'office-list-note';
					errorState.textContent = 'Unable to load offices right now.';
					destinationOffice.appendChild(errorState);
				}
			}
		};

		const resetRegistrationFlowToStepOne = () => {
			releaseCamera();
			clearFrozenFrame();
			loadingOverlay.classList.add('is-hidden');

			currentStep = 1;
			capturedPictureData = '';
			faceIdCapturePublicPath = '';
			faceIdCapturePreviewUrl = '';
			hasSavedRegistration = false;
			existingVisitorMatch = null;
			existingVisitorConfirmed = false;
			selectedOfficeIds = [];
			autoEnrolleeOfficeNames = [];

			const resetPanel = registerType === 'enrollee' ? enrolleeStepPanel : visitorStepPanel;
			if (resetPanel) {
				const fields = resetPanel.querySelectorAll('input, textarea');
				fields.forEach((field) => {
					if (field.type === 'checkbox' || field.type === 'radio') {
						field.checked = false;
						return;
					}

					field.value = '';
				});
			}

			if (qrCodeContainer) {
				qrCodeContainer.innerHTML = '';
			}

			ticketControlNumber.textContent = '-';
			ticketVisitorName.textContent = '-';
			ticketPassNumber.textContent = '-';
			ticketPurpose.textContent = '-';
			ticketDestination.textContent = '-';

			if (ticketPhoto) {
				ticketPhoto.src = '';
				ticketPhoto.classList.add('is-hidden');
			}
			ticketPhotoFallback?.classList.remove('is-hidden');

			ticketSaveStatus.textContent = 'Generating QR and saving details...';
			ticketSaveStatus.classList.remove('error');

			ensureAutoControlNumber();
			updateStepUI();
			cameraStatus.textContent = 'Starting camera...';
			startCamera();
		};

		window.addEventListener('afterprint', () => {
			document.body.classList.remove('print-ticket-mode');

			if (!shouldResetAfterPrint) {
				return;
			}

			shouldResetAfterPrint = false;
			resetRegistrationFlowToStepOne();
		});

		window.addEventListener('beforeunload', () => {
			releaseCamera();
		});

		existingVisitorModalConfirm?.addEventListener('click', () => {
			closeExistingVisitorModal(true);
		});

		existingVisitorModalCancel?.addEventListener('click', () => {
			closeExistingVisitorModal(false);
		});

		existingVisitorModal?.addEventListener('click', (event) => {
			if (event.target === existingVisitorModal) {
				closeExistingVisitorModal(false);
			}
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && existingVisitorModal && !existingVisitorModal.classList.contains('is-hidden')) {
				closeExistingVisitorModal(false);
			}
		});

		// === DEBUG TEST FUNCTION ===
		window.testOcrEndpoint = function() {
			console.log('🧪 Testing OCR endpoint...');
			
			// Create a simple test image (tiny 1x1 pixel)
			const canvas = document.createElement('canvas');
			canvas.width = 1;
			canvas.height = 1;
			const ctx = canvas.getContext('2d');
			ctx.fillStyle = 'blue';
			ctx.fillRect(0, 0, 1, 1);
			const testImage = canvas.toDataURL('image/jpeg');
			
			console.log('🧪 Sending test image to /guard/parse-id...');
			
			const formData = new FormData();
			formData.append('image', testImage);
			formData.append('id_type', 'auto');
			
			fetch('/guard/parse-id', {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
				},
				body: formData
			})
			.then(response => {
				console.log('🧪 Response status:', response.status);
				return response.json();
			})
			.then(data => {
				console.log('🧪 Response data:', data);
				console.log('🧪 Success:', data.success);
				console.log('🧪 Message:', data.message);
				if (data.extracted_data) {
					console.log('🧪 Extracted data:', data.extracted_data);
				}
				if (data.form_data) {
					console.log('🧪 Form data:', data.form_data);
				}
			})
			.catch(error => {
				console.error('🧪 ERROR:', error);
				console.error(error.stack);
			});
		};
		console.log('💡 Tip: Open console and type: testOcrEndpoint()');

		if (hasRegisterFlow) {
			updateStepUI();
			ensureAutoControlNumber();
			if (registerType !== 'contractor') {
				fetchOffices();
			}
			startCamera();
		}
	</script>
</body>
</html>