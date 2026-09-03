<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Register Visitor</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<style nonce="{{ $cspNonce }}">
		:root {
			font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
			--sidebar-bg: #243c96;
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
			min-height: 100dvh;
		}

		@media (min-width: 992px) {
			.layout {
				height: 100vh;
				height: 100dvh;
				overflow: hidden;
			}
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
			word-break: break-word;
			overflow-wrap: anywhere;
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
			font-size: clamp(1.25rem, 2vw + 0.75rem, 1.75rem);
			font-weight: 700;
			color: #0f172a;
		}

		.register-flow {
			max-width: 720px;
			margin: 0 auto;
			width: 100%;
		}

		.register-flow:has(#registrationCompletePanel:not(.is-hidden)) {
			max-width: 1100px;
			margin-left: auto;
			margin-right: auto;
			width: 100%;
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
			font-size: clamp(1.35rem, 2.2vw + 0.85rem, 1.75rem);
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

		.camera-feed.is-mirrored {
			transform: scaleX(-1);
		}

		.flip-camera-btn {
			position: absolute;
			top: 12px;
			right: 12px;
			z-index: 7;
			width: 44px;
			height: 44px;
			padding: 0;
			border: 0;
			border-radius: 50%;
			background: rgba(15, 23, 42, 0.72);
			color: #fff;
			display: none;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			backdrop-filter: blur(4px);
			box-shadow: 0 2px 8px rgba(15, 23, 42, 0.28);
			-webkit-tap-highlight-color: transparent;
		}

		.scanner-zone.camera-on .flip-camera-btn {
			display: inline-flex;
		}

		.flip-camera-btn i {
			font-size: 1.15rem;
			line-height: 1;
		}

		.flip-camera-btn:disabled {
			opacity: 0.55;
			cursor: wait;
		}

		.flip-camera-btn:hover:not(:disabled),
		.flip-camera-btn:focus-visible:not(:disabled) {
			background: rgba(15, 23, 42, 0.88);
			outline: none;
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
			backdrop-filter: none;
			-webkit-backdrop-filter: none;
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
			font-size: clamp(1.35rem, 2.5vw + 0.85rem, 2.125rem);
			line-height: 1;
			font-weight: 600;
			color: #111827;
			margin: 0;
		}

		.visitor-meta-value.id {
			font-size: clamp(1.25rem, 2.2vw + 0.75rem, 2rem);
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
			accent-color: #3e4ba0;
			flex-shrink: 0;
		}

		.office-visit-heading {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			flex-wrap: wrap;
			margin-bottom: 8px;
		}

		.office-visit-heading .visitor-label {
			margin: 0;
		}

		.office-select-all-inline {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			margin: 0;
			font-size: 14px;
			font-weight: 700;
			color: #1f2937;
			cursor: pointer;
			user-select: none;
			white-space: nowrap;
		}

		.office-select-all-inline input {
			width: 16px;
			height: 16px;
			accent-color: #3e4ba0;
			flex-shrink: 0;
			cursor: pointer;
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
			margin: 0 auto;
			max-width: 1100px;
			width: 100%;
			display: grid;
			gap: 14px;
			justify-items: stretch;
			box-sizing: border-box;
		}

		.registration-complete.is-hidden {
			display: none !important;
		}

		.qr-success {
			display: grid;
			gap: 14px;
			color: #101828;
		}

		.qr-success-capture {
			display: flex;
			flex-direction: column;
			gap: 14px;
		}

		.qr-success-banner {
			display: flex;
			align-items: center;
			justify-content: space-between;
			min-height: 84px;
			padding: 14px 22px;
			border: 1px solid #a7ead0;
			border-radius: 18px;
			background: linear-gradient(110deg, #ecfff7, #f5fffb);
			box-shadow: 0 5px 20px rgba(16, 185, 129, 0.06);
		}

		.qr-success-banner-left {
			display: flex;
			align-items: center;
			gap: 16px;
			min-width: 0;
		}

		.qr-success-icon-ring {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 58px;
			height: 58px;
			flex-shrink: 0;
			border: 2px solid #abecd4;
			border-radius: 50%;
			box-shadow:
				0 0 0 6px rgba(18, 185, 129, 0.08),
				0 0 0 11px rgba(18, 185, 129, 0.04);
		}

		.qr-success-icon {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 42px;
			height: 42px;
			color: #fff;
			border-radius: 50%;
			background: #12b981;
		}

		.qr-success-icon svg {
			width: 24px;
			height: 24px;
			fill: none;
			stroke: currentColor;
			stroke-width: 2.7;
			stroke-linecap: round;
			stroke-linejoin: round;
		}

		.qr-success-banner-text {
			display: flex;
			align-items: center;
			gap: 12px;
			flex-wrap: wrap;
			min-width: 0;
		}

		.qr-success-banner-text h2 {
			margin: 0;
			font-size: clamp(18px, 1.6vw, 26px);
			line-height: 1.2;
			color: #0f172a;
		}

		.qr-success-type-badge {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 6px 14px;
			color: #fff;
			border-radius: 999px;
			background: #12b981;
			font-size: 13px;
			font-weight: 800;
			white-space: nowrap;
		}

		.qr-success-shield {
			width: 38px;
			height: 38px;
			flex-shrink: 0;
			fill: none;
			stroke: #8bdcbc;
			stroke-width: 1.8;
			stroke-linecap: round;
			stroke-linejoin: round;
		}

		.qr-success-primary-grid {
			display: grid;
			grid-template-columns: minmax(0, 1fr) minmax(280px, 0.95fr);
			gap: 14px;
		}

		.qr-success-visitor-card,
		.qr-success-qr-card,
		.qr-success-info-card,
		.qr-success-route-card {
			border: 1px solid #d7e0ec;
			border-radius: 16px;
			background: #fff;
			box-shadow: 0 5px 19px rgba(16, 24, 40, 0.05);
		}

		.qr-success-visitor-card {
			display: grid;
			grid-template-columns: minmax(140px, 38%) 1fr;
			gap: 18px;
			padding: 18px;
			min-height: 260px;
		}

		.qr-success-photo-wrap {
			width: 100%;
			min-height: 220px;
			overflow: hidden;
			border-radius: 12px;
			background: #eef2f7;
			position: relative;
		}

		.qr-success-photo {
			width: 100%;
			height: 100%;
			min-height: 220px;
			object-fit: cover;
			display: block;
		}

		.qr-success-photo-fallback {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 100%;
			height: 100%;
			min-height: 220px;
			color: #8ca3bd;
			background: linear-gradient(135deg, #eaf1f8, #f8fbff);
		}

		.qr-success-photo-fallback svg {
			width: 64px;
			height: 64px;
			fill: none;
			stroke: currentColor;
			stroke-width: 1.5;
			stroke-linecap: round;
			stroke-linejoin: round;
		}

		.qr-success-visitor-details {
			align-self: center;
			min-width: 0;
		}

		.qr-success-detail-row {
			display: grid;
			grid-template-columns: 30px 1fr;
			gap: 12px;
			align-items: center;
			padding: 8px 0;
		}

		.qr-success-detail-icon {
			color: #0753ad;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.qr-success-detail-icon svg {
			width: 24px;
			height: 24px;
			fill: none;
			stroke: currentColor;
			stroke-width: 1.8;
			stroke-linecap: round;
			stroke-linejoin: round;
		}

		.qr-success-detail-label {
			display: block;
			margin-bottom: 2px;
			color: #667085;
			font-size: 13px;
			font-weight: 600;
		}

		.qr-success-detail-value,
		.qr-success-info-value {
			display: block;
			color: #101828;
			font-size: 16px;
			line-height: 1.35;
			overflow-wrap: anywhere;
		}

		.qr-success-name {
			text-transform: uppercase;
		}

		.qr-success-control {
			color: #0753ad;
			letter-spacing: 0.03em;
		}

		.qr-success-detail-sep {
			height: 1px;
			margin-left: 42px;
			background: #e3e9f0;
		}

		.qr-success-qr-card {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			min-height: 260px;
			padding: 18px;
		}

		.qr-success-qr-title {
			margin: 0 0 6px;
			color: #073f8c;
			font-size: 17px;
			font-weight: 800;
		}

		.qr-success-qr-frame {
			position: relative;
			display: flex;
			align-items: center;
			justify-content: center;
			width: 220px;
			height: 190px;
			padding: 14px;
		}

		.qr-box {
			width: 170px;
			height: 170px;
			background: #fff;
			display: grid;
			place-items: center;
		}

		.qr-box canvas,
		.qr-box img {
			display: block;
			width: 170px !important;
			height: 170px !important;
			image-rendering: pixelated;
		}

		.qr-box table {
			width: 170px !important;
			height: 170px !important;
			border-collapse: collapse;
		}

		.qr-success-corner {
			position: absolute;
			width: 28px;
			height: 28px;
			border-color: #0753ad;
		}

		.qr-success-corner-tl {
			top: 1px;
			left: 1px;
			border-top: 4px solid;
			border-left: 4px solid;
			border-top-left-radius: 8px;
		}

		.qr-success-corner-tr {
			top: 1px;
			right: 1px;
			border-top: 4px solid;
			border-right: 4px solid;
			border-top-right-radius: 8px;
		}

		.qr-success-corner-bl {
			bottom: 1px;
			left: 1px;
			border-bottom: 4px solid;
			border-left: 4px solid;
			border-bottom-left-radius: 8px;
		}

		.qr-success-corner-br {
			right: 1px;
			bottom: 1px;
			border-right: 4px solid;
			border-bottom: 4px solid;
			border-bottom-right-radius: 8px;
		}

		.qr-success-qr-hint {
			margin: 8px 0 0;
			color: #515d6c;
			font-size: 15px;
			font-weight: 600;
		}

		.qr-success-info-card {
			display: grid;
			grid-template-columns: 0.75fr auto 1.25fr;
			align-items: center;
			min-height: 78px;
			padding: 16px 28px;
		}

		.qr-success-info-item {
			display: grid;
			grid-template-columns: 34px 1fr;
			align-items: center;
			gap: 10px;
			min-width: 0;
		}

		.qr-success-info-destination {
			padding-left: 22px;
		}

		.qr-success-info-divider {
			width: 1px;
			height: 44px;
			background: #d7e0ec;
		}

		.qr-success-route-card {
			padding: 18px 20px;
		}

		.qr-success-route-heading {
			display: flex;
			align-items: center;
			gap: 10px;
			margin-bottom: 12px;
		}

		.qr-success-route-heading svg {
			width: 22px;
			height: 22px;
			fill: #0753ad;
			stroke: #0753ad;
			stroke-width: 1.3;
		}

		.qr-success-route-heading h3 {
			margin: 0;
			font-size: 18px;
			color: #0f172a;
		}

		.qr-success-route-list {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
			gap: 12px;
		}

		.qr-success-route-item {
			display: flex;
			align-items: center;
			justify-content: space-between;
			min-height: 54px;
			padding: 8px 14px 8px 10px;
			border: 1px solid #d7e0ec;
			border-radius: 12px;
			background: #fff;
			gap: 10px;
		}

		.qr-success-route-item-left {
			display: flex;
			align-items: center;
			min-width: 0;
			gap: 12px;
		}

		.qr-success-route-number {
			display: inline-flex;
			flex: 0 0 38px;
			align-items: center;
			justify-content: center;
			width: 38px;
			height: 38px;
			color: #fff;
			border-radius: 50%;
			background: linear-gradient(145deg, #0962c6, #06499d);
			font-size: 16px;
			font-weight: 800;
		}

		.qr-success-route-item strong {
			overflow-wrap: anywhere;
			color: #073f8c;
			font-size: 15px;
		}

		.qr-success-route-arrow {
			flex: 0 0 20px;
			width: 20px;
			height: 20px;
			fill: none;
			stroke: #344054;
			stroke-width: 2;
			stroke-linecap: round;
			stroke-linejoin: round;
		}

		.qr-success-route-note {
			display: flex;
			align-items: center;
			gap: 10px;
			margin-top: 12px;
			padding: 10px 14px;
			border: 1px solid #b8d4ec;
			border-radius: 10px;
			background: #f4faff;
		}

		.qr-success-route-note svg {
			flex: 0 0 24px;
			width: 24px;
			height: 24px;
			fill: none;
			stroke: #073f8c;
			stroke-width: 1.7;
			stroke-linecap: round;
			stroke-linejoin: round;
		}

		.qr-success-route-note p {
			margin: 0;
			font-size: 13px;
			font-weight: 600;
			line-height: 1.45;
			color: #334155;
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

		.qr-success-actions {
			display: grid;
			grid-template-columns: 1fr 0.9fr 1.45fr;
			gap: 14px;
			padding: 14px 16px;
			border-radius: 14px;
			background: #fff;
			box-shadow: 0 6px 22px rgba(16, 24, 40, 0.06);
		}

		.qr-success-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			min-height: 52px;
			padding: 10px 18px;
			cursor: pointer;
			text-decoration: none;
			border: 1px solid transparent;
			border-radius: 12px;
			font: inherit;
			font-size: 16px;
			font-weight: 800;
			transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
		}

		.qr-success-btn:hover {
			transform: translateY(-1px);
		}

		.qr-success-btn:disabled {
			cursor: wait;
			opacity: 0.75;
			transform: none;
		}

		.qr-success-btn svg {
			width: 22px;
			height: 22px;
			fill: none;
			stroke: currentColor;
			stroke-width: 1.9;
			stroke-linecap: round;
			stroke-linejoin: round;
		}

		.qr-success-btn-primary {
			color: #fff;
			background: linear-gradient(120deg, #0757b8, #06499d);
			box-shadow: 0 6px 16px rgba(7, 83, 173, 0.18);
		}

		.qr-success-btn-primary:hover {
			color: #fff;
		}

		.qr-success-btn-secondary {
			color: #073f8c;
			border-color: #d7e3f2;
			background: #edf4ff;
		}

		.qr-success-complete-icon {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 28px;
			height: 28px;
			color: #0753ad;
			border-radius: 50%;
			background: #fff;
		}

		.qr-success-complete-icon svg {
			width: 16px;
			height: 16px;
			stroke-width: 2.5;
		}

		@media (max-width: 1050px) {
			.qr-success-primary-grid {
				grid-template-columns: 1fr;
			}

			.qr-success-actions {
				grid-template-columns: 1fr 1fr;
			}

			.qr-success-btn-complete {
				grid-column: 1 / -1;
			}
		}

		@media (max-width: 720px) {
			.qr-success-visitor-card {
				grid-template-columns: 1fr;
				min-height: 0;
			}

			.qr-success-photo-wrap,
			.qr-success-photo,
			.qr-success-photo-fallback {
				min-height: 220px;
				max-height: 280px;
			}

			.qr-success-info-card {
				grid-template-columns: 1fr;
				gap: 14px;
				padding: 16px;
			}

			.qr-success-info-divider {
				width: 100%;
				height: 1px;
			}

			.qr-success-info-destination {
				padding-left: 0;
			}

			.qr-success-shield {
				display: none;
			}

			.qr-success-actions {
				grid-template-columns: 1fr 1fr;
				padding: 0;
				background: transparent;
				box-shadow: none;
			}

			.qr-success-btn-complete {
				grid-column: 1 / -1;
			}
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

		@media (max-width: 991.98px) {
			.main > h1.page-title {
				display: none;
			}

			.confirmation-modal {
				padding: 12px;
				align-items: flex-end;
			}

			.confirmation-modal-card {
				width: 100%;
				max-height: min(92dvh, 640px);
				overflow-y: auto;
				border-radius: 16px 16px 0 0;
			}

			.confirmation-photo-panel {
				grid-template-columns: 1fr;
				justify-items: center;
				text-align: center;
			}

			.confirmation-photo-frame {
				width: min(132px, 40vw);
				height: min(132px, 40vw);
			}

			.confirmation-modal-footer {
				flex-direction: column-reverse;
				align-items: stretch;
			}

			.confirmation-modal-btn {
				width: 100%;
				min-width: 0;
			}

			.visitor-details-grid {
				grid-template-columns: 1fr;
			}

			.visitor-details-row {
				flex-wrap: wrap;
				align-items: flex-start;
			}

			.ticket-info-grid {
				grid-template-columns: 1fr;
				justify-items: center;
				text-align: center;
			}

			.ticket-photo-wrap {
				margin: 0 auto;
			}

			.id-guide-layout {
				grid-template-columns: 1fr;
				gap: 8px;
			}

			.scan-actions-row {
				width: 100%;
				flex-wrap: wrap;
			}

			.scan-action,
			.gallery-action {
				min-width: min(100%, 160px);
			}

			.ticket-actions {
				flex-wrap: wrap;
			}

			.ticket-btn {
				flex: 1 1 auto;
				min-width: min(100%, 140px);
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

			.brand-title span:first-child {
				font-size: 22px;
			}

			.brand-title span:last-child {
				font-size: 20px;
			}
		}

		@include('guard.partials.guard-responsive-styles')

		html:has(body.self-registration-mode),
		html:has(body.self-registration-mode) body {
			overflow-x: clip;
			overflow-y: auto;
			height: auto;
			max-height: none;
			min-height: 100%;
		}

		body.self-registration-mode {
			position: relative;
			inset: auto;
			width: 100%;
			min-height: 100%;
			height: auto;
			max-height: none;
			overflow-x: clip;
			overflow-y: auto;
			overscroll-behavior: auto;
			background: #f1f5fa;
		}

		body.self-registration-mode.kiosk-type-select {
			background: #1f348f;
		}

		body.self-registration-mode .layout {
			min-height: 100dvh;
			height: auto !important;
			max-height: none !important;
			overflow: visible !important;
		}

		body.self-registration-mode .self-registration-header {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			z-index: 300;
			flex-shrink: 0;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 20px;
			padding: 10px 22px;
			padding-top: max(10px, env(safe-area-inset-top));
			min-height: 72px;
			margin: 0;
			background: #2945a5;
			color: #fff;
			border-bottom: none;
			box-shadow: 0 4px 18px rgba(24, 49, 127, 0.18);
		}

		body.self-registration-mode .main {
			margin-left: 0 !important;
			padding: 0 !important;
			padding-top: calc(72px + env(safe-area-inset-top)) !important;
			display: flex;
			flex-direction: column;
			gap: 0;
			min-height: 100dvh;
			height: auto !important;
			max-height: none !important;
			overflow: visible !important;
			background: transparent;
		}

		body.self-registration-mode .self-registration-brand .brand-icon {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			background: rgba(255, 255, 255, 0.14);
			font-size: 22px;
			color: #fff;
		}

		body.self-registration-mode .self-registration-brand .brand-title {
			margin: 0;
			font-size: 0;
			line-height: 1.05;
			font-weight: 800;
			letter-spacing: -0.02em;
			display: flex;
			gap: 6px;
			align-items: baseline;
		}

		body.self-registration-mode .self-registration-brand .brand-title span {
			color: #ffd52b;
			font-size: 1.25rem;
		}

		body.self-registration-mode .self-registration-brand .brand-subtitle {
			display: block;
			font-size: 0.72rem;
			color: rgba(255, 255, 255, 0.78);
			line-height: 1.25;
			max-width: 11rem;
			margin-top: 2px;
		}

		body.self-registration-mode .self-reg-header-progress {
			flex: 1;
			max-width: 520px;
			min-width: 0;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		body.self-registration-mode .self-reg-stepper {
			display: flex;
			align-items: center;
			gap: 0;
			width: min(100%, 420px);
		}

		body.self-registration-mode .self-reg-step {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 6px;
			min-width: 72px;
			opacity: 0.55;
		}

		body.self-registration-mode .self-reg-step.is-active,
		body.self-registration-mode .self-reg-step.is-done {
			opacity: 1;
		}

		body.self-registration-mode .self-reg-step-num {
			width: 30px;
			height: 30px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 13px;
			font-weight: 700;
			background: rgba(255, 255, 255, 0.18);
			color: rgba(255, 255, 255, 0.85);
			border: 2px solid rgba(255, 255, 255, 0.28);
		}

		body.self-registration-mode .self-reg-step.is-active .self-reg-step-num {
			background: #3977e9;
			border-color: #4c8dff;
			color: #fff;
			box-shadow: 0 0 0 6px rgba(77, 142, 255, 0.15);
		}

		body.self-registration-mode .self-reg-step.is-done .self-reg-step-num {
			background: #18ad67;
			border-color: #18ad67;
			color: #fff;
			font-size: 0;
			line-height: 0;
		}

		body.self-registration-mode .self-reg-step.is-done .self-reg-step-num::before {
			content: "\F26E";
			font-family: "bootstrap-icons";
			font-size: 14px;
			line-height: 1;
			font-weight: 700;
		}

		body.self-registration-mode .self-reg-step-label {
			font-size: 0.68rem;
			font-weight: 600;
			color: rgba(255, 255, 255, 0.88);
			white-space: nowrap;
		}

		body.self-registration-mode .self-reg-step-line {
			flex: 1;
			height: 2px;
			min-width: 24px;
			margin: 0 6px 18px;
			background: rgba(255, 255, 255, 0.22);
			border-radius: 999px;
		}

		body.self-registration-mode .self-reg-step-line.is-done {
			background: #27d896;
		}

		body.self-registration-mode .self-reg-header-step {
			margin: 0 0 6px;
			text-align: center;
			font-size: 0.82rem;
			font-weight: 600;
			color: rgba(255, 255, 255, 0.92);
			letter-spacing: 0.02em;
		}

		body.self-registration-mode .self-reg-progress-track {
			height: 4px;
			background: rgba(255, 255, 255, 0.22);
			border-radius: 999px;
			overflow: hidden;
		}

		body.self-registration-mode .self-reg-progress-fill {
			height: 100%;
			width: 33.33%;
			background: #ffd84d;
			border-radius: 999px;
			transition: width 0.35s ease;
		}

		body.self-registration-mode .self-reg-logout-btn {
			padding: 7px 12px;
			font-size: 12px;
			border-color: rgba(255, 255, 255, 0.28);
			background: rgba(255, 255, 255, 0.1);
			color: #fff;
		}

		body.self-registration-mode .self-reg-logout-btn:hover {
			background: rgba(255, 255, 255, 0.18);
			color: #fff;
		}

		body.self-registration-mode .self-registration-body {
			flex: 1;
			display: flex;
			flex-direction: column;
			min-height: 0;
			overflow: visible !important;
			padding: 0;
			margin: 0;
			width: 100%;
			max-width: none;
		}

		body.self-registration-mode .page-title,
		body.self-registration-mode .self-reg-subtitle,
		body.self-registration-mode .flow-head,
		body.self-registration-mode .self-reg-kiosk-title {
			display: none !important;
		}

		body.self-registration-mode .register-flow {
			flex: 1;
			display: flex;
			flex-direction: column;
			min-height: 0;
			overflow: visible !important;
			width: 100%;
			max-width: none;
			margin: 0;
			background: #f1f5fa;
		}

		body.self-registration-mode .register-flow:has(#registrationCompletePanel:not(.is-hidden)) {
			align-items: center;
			justify-content: flex-start;
			background: #f3f6fc;
		}

		body.self-registration-mode .register-flow > .visitor-step:not(.is-hidden) {
			flex: 1;
			min-height: 0;
		}

		body.self-registration-mode .scanner-card:not(.is-hidden) {
			flex: 1;
			display: flex;
			flex-direction: column;
			align-items: stretch;
			min-height: 0;
			overflow: visible !important;
			padding: 0;
			margin: 0;
			border: none;
			border-radius: 0;
			box-shadow: none;
			background: #f1f5fa;
		}

		body.self-registration-mode .kiosk-scan-layout {
			flex: 1;
			display: flex;
			flex-direction: row;
			align-items: stretch;
			min-height: 0;
			overflow: hidden;
		}

		body.self-registration-mode .kiosk-scan-main {
			flex: 1;
			display: flex;
			flex-direction: column;
			min-width: 0;
			min-height: 0;
			padding: 20px 24px 16px;
			gap: 16px;
			overflow-y: auto;
			scrollbar-width: thin;
		}

		body.self-registration-mode .kiosk-scan-heading {
			text-align: center;
			flex-shrink: 0;
		}

		body.self-registration-mode .kiosk-scan-title {
			margin: 0 0 6px;
			font-size: clamp(1.2rem, 2vw, 1.5rem);
			font-weight: 700;
			color: #0f172a;
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		body.self-registration-mode .kiosk-scan-title .bi {
			color: #243c96;
			font-size: 1.15em;
		}

		body.self-registration-mode .kiosk-scan-desc {
			margin: 0;
			font-size: 0.9rem;
			color: #64748b;
			line-height: 1.45;
		}

		body.self-registration-mode .kiosk-scan-viewport {
			position: relative;
			flex: 1;
			min-height: 220px;
			display: flex;
			flex-direction: column;
		}

		body.self-registration-mode .scanner-zone {
			flex: 1;
			min-width: 0;
			min-height: 200px;
			height: auto;
			border-radius: 16px;
			padding: 0;
			border: 1px solid #dbe2ef;
			box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
			overflow: hidden;
		}

		body.self-registration-mode .scanner-zone.camera-frame {
			flex: none;
		}

		body.self-registration-mode .kiosk-camera-badge {
			position: absolute;
			left: 50%;
			bottom: 14px;
			transform: translateX(-50%);
			display: inline-flex;
			align-items: center;
			gap: 7px;
			padding: 6px 14px;
			border-radius: 999px;
			background: rgba(15, 23, 42, 0.72);
			color: #fff;
			font-size: 12px;
			font-weight: 600;
			z-index: 6;
			backdrop-filter: blur(4px);
		}

		body.self-registration-mode .kiosk-camera-badge .badge-dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: #94a3b8;
		}

		body.self-registration-mode .kiosk-camera-badge.is-ready .badge-dot {
			background: #22c55e;
			box-shadow: 0 0 8px rgba(34, 197, 94, 0.7);
		}

		body.self-registration-mode .kiosk-camera-badge.is-error .badge-dot {
			background: #ef4444;
		}

		body.self-registration-mode .kiosk-bottom-panels {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 14px;
			flex-shrink: 0;
		}

		body.self-registration-mode .kiosk-info-card {
			background: #fff;
			border: 1px solid #e2e8f0;
			border-radius: 14px;
			padding: 16px 18px;
			box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
		}

		body.self-registration-mode .kiosk-info-card-title {
			margin: 0 0 12px;
			font-size: 0.95rem;
			font-weight: 700;
			color: #0f172a;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		body.self-registration-mode .kiosk-info-card-title .bi {
			color: #243c96;
		}

		body.self-registration-mode .kiosk-tips-list {
			margin: 0;
			padding: 0;
			list-style: none;
			display: flex;
			flex-direction: column;
			gap: 8px;
		}

		body.self-registration-mode .kiosk-tips-list li {
			display: flex;
			align-items: flex-start;
			gap: 8px;
			font-size: 0.82rem;
			color: #475569;
			line-height: 1.4;
		}

		body.self-registration-mode .kiosk-tips-list .bi {
			color: #243c96;
			margin-top: 2px;
			flex-shrink: 0;
		}

		body.self-registration-mode .kiosk-id-chip-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 8px;
		}

		body.self-registration-mode .kiosk-id-chip {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 10px 12px;
			border-radius: 10px;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			font-size: 0.78rem;
			font-weight: 600;
			color: #334155;
		}

		body.self-registration-mode .kiosk-id-chip .bi {
			color: #243c96;
			font-size: 1rem;
		}

		body.self-registration-mode .kiosk-reg-footer {
			flex-shrink: 0;
			padding: 10px 24px 14px;
			text-align: center;
			font-size: 0.75rem;
			color: #94a3b8;
			background: #f1f5f9;
			border-top: 1px solid #e2e8f0;
		}

		body.self-registration-mode .kiosk-controls {
			width: clamp(300px, 34vw, 380px);
			flex-shrink: 0;
			display: flex;
			flex-direction: column;
			justify-content: flex-start;
			gap: 14px;
			padding: 24px 22px;
			background: #fff;
			border-left: 1px solid #e2e8f0;
			min-height: 0;
			overflow-y: auto;
			scrollbar-width: thin;
		}

		body.self-registration-mode .kiosk-visitor-badge {
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 12px 14px;
			border-radius: 12px;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
		}

		body.self-registration-mode .kiosk-visitor-avatar {
			width: 42px;
			height: 42px;
			border-radius: 50%;
			background: #243c96;
			color: #fff;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 18px;
			flex-shrink: 0;
		}

		body.self-registration-mode .kiosk-visitor-name {
			display: block;
			font-size: 0.92rem;
			font-weight: 700;
			color: #0f172a;
			line-height: 1.2;
		}

		body.self-registration-mode .kiosk-visitor-step {
			display: block;
			margin-top: 2px;
			font-size: 0.68rem;
			font-weight: 700;
			color: #243c96;
			letter-spacing: 0.04em;
		}

		body.self-registration-mode .kiosk-status-label {
			margin: 0 0 6px;
			font-size: 0.72rem;
			font-weight: 700;
			color: #64748b;
			text-transform: uppercase;
			letter-spacing: 0.04em;
		}

		body.self-registration-mode .kiosk-status-card {
			display: flex;
			align-items: flex-start;
			gap: 10px;
			padding: 12px 14px;
			border-radius: 12px;
			border: 1px solid #bbf7d0;
			background: #f0fdf4;
		}

		body.self-registration-mode .kiosk-status-card.is-error {
			border-color: #fecaca;
			background: #fef2f2;
		}

		body.self-registration-mode .kiosk-status-card .bi {
			font-size: 1.2rem;
			color: #16a34a;
			flex-shrink: 0;
			margin-top: 1px;
		}

		body.self-registration-mode .kiosk-status-card.is-error .bi {
			color: #dc2626;
		}

		body.self-registration-mode .kiosk-status-title {
			display: block;
			font-size: 0.88rem;
			font-weight: 700;
			color: #0f172a;
			line-height: 1.2;
		}

		body.self-registration-mode .kiosk-status-text {
			margin: 3px 0 0;
			font-size: 0.78rem;
			color: #475569;
			line-height: 1.4;
		}

		body.self-registration-mode .kiosk-or-divider {
			display: flex;
			align-items: center;
			gap: 10px;
			color: #94a3b8;
			font-size: 0.72rem;
			font-weight: 600;
			text-transform: uppercase;
		}

		body.self-registration-mode .kiosk-or-divider::before,
		body.self-registration-mode .kiosk-or-divider::after {
			content: "";
			flex: 1;
			height: 1px;
			background: #e2e8f0;
		}

		body.self-registration-mode .kiosk-supported-list {
			margin: 0;
			padding: 0;
			list-style: none;
			display: flex;
			flex-direction: column;
			gap: 6px;
		}

		body.self-registration-mode .kiosk-supported-list li {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 0.78rem;
			color: #475569;
		}

		body.self-registration-mode .kiosk-supported-list .bi {
			color: #16a34a;
			font-size: 0.85rem;
		}

		body.self-registration-mode .kiosk-controls-title {
			margin: 0;
			font-size: 1.1rem;
			font-weight: 700;
			color: #0f172a;
			line-height: 1.2;
		}

		body.self-registration-mode .camera-status {
			display: none;
		}

		body.self-registration-mode .scan-actions-row {
			width: 100%;
			margin: 0;
			flex-direction: column;
			gap: 10px;
		}

		body.self-registration-mode .scan-action,
		body.self-registration-mode .gallery-action {
			width: 100%;
			flex: none;
			height: 48px;
			border-radius: 12px;
			font-size: 14px;
			font-weight: 600;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
		}

		body.self-registration-mode .id-types:not(.is-hidden) {
			padding: 0;
			background: transparent;
			border: none;
		}

		body.self-registration-mode .id-types:not(.is-hidden) .id-types-title {
			margin: 0 0 8px;
			font-size: 0.72rem;
			font-weight: 700;
			color: #64748b;
			text-transform: uppercase;
			letter-spacing: 0.04em;
		}

		@keyframes kioskScanLine {
			0%, 100% { top: 18%; opacity: 0.35; }
			50% { top: 78%; opacity: 1; }
		}

		body.self-registration-mode .id-guide::after {
			content: "";
			position: absolute;
			left: 8%;
			right: 8%;
			height: 2px;
			background: linear-gradient(90deg, transparent, #3b82f6, transparent);
			box-shadow: 0 0 12px rgba(59, 130, 246, 0.65);
			animation: kioskScanLine 2.4s ease-in-out infinite;
			pointer-events: none;
		}

		body.self-registration-mode .scan-action {
			background: #243c96;
			border: none;
			color: #fff;
		}

		body.self-registration-mode .scan-action:hover:not(:disabled) {
			background: #1e3278;
			color: #fff;
		}

		body.self-registration-mode .gallery-action {
			background: #fff;
			border: 2px solid #243c96;
			color: #243c96;
		}

		body.self-registration-mode .gallery-action:hover:not(:disabled) {
			background: #eef2ff;
		}

		body.self-registration-mode .gallery-hint {
			width: 100%;
			margin: 0;
			text-align: left;
			font-size: 12px;
			color: #64748b;
			line-height: 1.4;
		}

		body.self-registration-mode .id-types {
			width: 100%;
			margin: 0;
			padding: 12px 14px;
			border-radius: 10px;
			background: #f1f5ff;
			border: 1px solid #dbeafe;
		}

		body.self-registration-mode .id-types-title {
			margin: 0 0 4px;
			font-size: 12px;
			font-weight: 700;
			color: #243c96;
		}

		body.self-registration-mode .id-types-list {
			margin: 0;
			padding: 0;
			list-style: none;
			font-size: 11px;
			line-height: 1.45;
			color: #475569;
		}

		body.self-registration-mode .id-types-list li {
			margin: 0;
		}

		body.self-registration-mode .visitor-step:not(.is-hidden) {
			flex: 1;
			display: flex;
			flex-direction: column;
			min-height: 0;
			overflow: hidden;
			margin: 0;
			padding: 0;
			background: #f1f5f9;
		}

		body.self-registration-mode .kiosk-verify-layout {
			flex: 1;
			display: grid;
			grid-template-columns: minmax(0, 1fr) 310px;
			align-items: start;
			gap: 20px;
			min-height: 0;
			width: min(1680px, calc(100% - 40px));
			margin: 0 auto;
			padding: 22px 20px max(24px, env(safe-area-inset-bottom));
			overflow-x: hidden;
			overflow-y: auto;
			-webkit-overflow-scrolling: touch;
		}

		body.self-registration-mode .kiosk-mobile-progress {
			display: none;
			width: min(100% - 28px, 760px);
			margin: 14px auto 0;
			padding: 14px 16px;
			border: 1px solid #dbe3ef;
			border-radius: 13px;
			background: #fff;
			box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
		}

		body.self-registration-mode .kiosk-mobile-progress-info {
			margin-bottom: 9px;
			display: flex;
			justify-content: space-between;
			gap: 10px;
			color: #64748b;
			font-size: 0.73rem;
		}

		body.self-registration-mode .kiosk-mobile-progress-info strong {
			color: #243f9f;
		}

		body.self-registration-mode .kiosk-mobile-progress .progress-track {
			height: 7px;
			overflow: hidden;
			border-radius: 999px;
			background: #e4e9f2;
		}

		body.self-registration-mode .kiosk-mobile-progress .progress-track span {
			display: block;
			height: 100%;
			border-radius: inherit;
			background: linear-gradient(90deg, #243f9f, #3977e9);
		}

		body.self-registration-mode .kiosk-verify-main {
			min-width: 0;
			min-height: 0;
		}

		body.self-registration-mode .kiosk-verify-card {
			background: #fff;
			border-radius: 22px;
			border: 1px solid #dbe3ef;
			box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
			padding: 0;
			overflow: hidden;
		}

		body.self-registration-mode .kiosk-verify-head {
			display: flex;
			align-items: center;
			gap: 15px;
			margin: 0;
			padding: 24px 26px;
			border-bottom: 1px solid #edf1f7;
		}

		body.self-registration-mode .kiosk-verify-head-icon {
			width: 48px;
			height: 48px;
			border-radius: 14px;
			background: #edf2ff;
			color: #2945a5;
			display: grid;
			place-items: center;
			font-size: 25px;
			flex-shrink: 0;
		}

		body.self-registration-mode .kiosk-verify-head h2 {
			margin: 0;
			font-size: clamp(1.25rem, 2vw, 1.65rem);
			font-weight: 800;
			color: #111827;
			line-height: 1.25;
			letter-spacing: -0.02em;
		}

		body.self-registration-mode .kiosk-verify-head p {
			margin: 5px 0 0;
			font-size: 0.88rem;
			color: #64748b;
			line-height: 1.45;
		}

		body.self-registration-mode .kiosk-verify-form-body {
			padding: 0 26px 28px;
		}

		body.self-registration-mode .kiosk-form-section {
			margin: 0;
			padding: 25px 0;
			border: 0;
			border-bottom: 1px solid #edf1f7;
		}

		body.self-registration-mode .kiosk-form-section:last-of-type {
			border-bottom: 0;
		}

		body.self-registration-mode .kiosk-form-section-title {
			display: flex;
			align-items: center;
			gap: 9px;
			margin: 0;
			font-size: 0.92rem;
			font-weight: 750;
			color: #111827;
		}

		body.self-registration-mode .kiosk-form-section-title .bi {
			width: 29px;
			height: 29px;
			border-radius: 9px;
			background: #edf2ff;
			color: #2945a5;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-size: 14px;
			flex-shrink: 0;
		}

		body.self-registration-mode .kiosk-form-grid {
			margin-top: 17px;
			display: grid;
			gap: 17px;
		}

		body.self-registration-mode .kiosk-form-grid.cols-2 {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}

		body.self-registration-mode .kiosk-form-grid.cols-3 {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}

		body.self-registration-mode .kiosk-field-label {
			display: block;
			margin: 0 0 7px;
			font-size: 0.78rem;
			font-weight: 650;
			color: #334155;
		}

		body.self-registration-mode .kiosk-input-wrap {
			position: relative;
		}

		body.self-registration-mode .kiosk-input-wrap .kiosk-input-icon,
		body.self-registration-mode .kiosk-input-suffix {
			display: none;
		}

		body.self-registration-mode .kiosk-input-wrap .visitor-input,
		body.self-registration-mode .kiosk-input-wrap .visitor-textarea {
			padding-left: 14px;
		}

		body.self-registration-mode .kiosk-input-wrap.has-suffix .visitor-input {
			padding-right: 14px;
		}

		body.self-registration-mode .kiosk-phone-prefix {
			display: flex;
			align-items: center;
			overflow: hidden;
			border: 1px solid #dbe3ef;
			border-radius: 10px;
			background: #fff;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}

		body.self-registration-mode .kiosk-phone-prefix:focus-within {
			border-color: #2945a5;
			box-shadow: 0 0 0 4px rgba(41, 69, 165, 0.1);
		}

		body.self-registration-mode .kiosk-phone-prefix .input-prefix {
			min-height: 48px;
			padding: 0 13px;
			display: grid;
			place-items: center;
			border-right: 1px solid #dbe3ef;
			background: #f7f9fc;
			color: #475569;
			font-size: 0.83rem;
			font-weight: 700;
			flex-shrink: 0;
		}

		body.self-registration-mode .kiosk-phone-prefix .visitor-input {
			border: 0 !important;
			border-radius: 0 !important;
			box-shadow: none !important;
			min-height: 48px;
		}

		body.self-registration-mode .kiosk-field-hint {
			display: block;
			margin-top: 6px;
			font-size: 0.72rem;
			line-height: 1.4;
			color: #64748b;
		}

		body.self-registration-mode .kiosk-form-section .visitor-input,
		body.self-registration-mode .kiosk-form-section .visitor-textarea {
			width: 100%;
			border: 1px solid #dbe3ef;
			border-radius: 10px;
			background: #fff;
			padding: 0 14px;
			font-size: 0.88rem;
			min-height: 48px;
			color: #111827;
			box-shadow: none;
			outline: none;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}

		body.self-registration-mode .kiosk-form-section .visitor-textarea {
			min-height: 96px;
			padding: 12px 14px;
			resize: vertical;
		}

		body.self-registration-mode .kiosk-form-section .visitor-input:hover,
		body.self-registration-mode .kiosk-form-section .visitor-textarea:hover {
			border-color: #bdc9da;
		}

		body.self-registration-mode .kiosk-form-section .visitor-input:focus,
		body.self-registration-mode .kiosk-form-section .visitor-textarea:focus {
			border-color: #2945a5;
			box-shadow: 0 0 0 4px rgba(41, 69, 165, 0.1);
		}

		body.self-registration-mode .kiosk-office-grid {
			margin-top: 17px;
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 11px;
			border: none;
			background: transparent;
			padding: 0;
		}

		body.self-registration-mode .kiosk-office-grid .office-list-note {
			grid-column: 1 / -1;
			margin: 0;
			padding: 12px;
			text-align: center;
			font-size: 13px;
			color: #64748b;
			background: #f8fafc;
			border: 1px dashed #e2e8f0;
			border-radius: 10px;
		}

		body.self-registration-mode .kiosk-office-choice {
			position: relative;
			min-width: 0;
		}

		body.self-registration-mode .kiosk-office-choice > input {
			position: absolute;
			width: 1px;
			height: 1px;
			opacity: 0;
			pointer-events: none;
		}

		body.self-registration-mode .kiosk-office-choice > label {
			min-height: 72px;
			padding: 13px;
			display: flex;
			align-items: center;
			gap: 10px;
			overflow: hidden;
			border: 1px solid #d8e1ee;
			border-radius: 12px;
			background: #fff;
			cursor: pointer;
			margin: 0;
			transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
		}

		body.self-registration-mode .kiosk-office-choice > label:hover {
			border-color: #91a6da;
			transform: translateY(-1px);
			box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
		}

		body.self-registration-mode .kiosk-office-heading {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			flex-wrap: wrap;
			margin-bottom: 0;
		}

		body.self-registration-mode .kiosk-office-heading .kiosk-form-section-title {
			margin: 0;
		}

		body.self-registration-mode .kiosk-select-all-inline {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			margin: 0;
			padding: 6px 10px;
			border: 1px solid #d8e1ee;
			border-radius: 999px;
			background: #f8fafc;
			font-size: 0.78rem;
			font-weight: 700;
			color: #1e293b;
			cursor: pointer;
			user-select: none;
			white-space: nowrap;
		}

		body.self-registration-mode .kiosk-select-all-inline input {
			position: absolute;
			width: 1px;
			height: 1px;
			opacity: 0;
			pointer-events: none;
		}

		body.self-registration-mode .kiosk-select-all-inline .checkbox-indicator {
			width: 16px;
			height: 16px;
			flex-shrink: 0;
			border: 2px solid #a4afbf;
			border-radius: 4px;
			background: #fff;
			display: grid;
			place-items: center;
		}

		body.self-registration-mode .kiosk-select-all-inline input:checked + .checkbox-indicator {
			border-color: #2945a5;
			background: #2945a5;
		}

		body.self-registration-mode .kiosk-select-all-inline input:checked + .checkbox-indicator::after {
			content: "";
			width: 4px;
			height: 8px;
			margin-bottom: 1px;
			border: solid #fff;
			border-width: 0 2px 2px 0;
			transform: rotate(45deg);
		}

		body.self-registration-mode .kiosk-select-all-inline input:indeterminate + .checkbox-indicator {
			border-color: #2945a5;
			background: #2945a5;
		}

		body.self-registration-mode .kiosk-select-all-inline input:indeterminate + .checkbox-indicator::after {
			content: "";
			width: 8px;
			height: 2px;
			margin: 0;
			background: #fff;
			border: 0;
			transform: none;
		}

		body.self-registration-mode .kiosk-office-choice .checkbox-indicator,
		body.self-registration-mode .kiosk-office-choice .radio-indicator {
			width: 19px;
			height: 19px;
			flex-shrink: 0;
			border: 2px solid #a4afbf;
			border-radius: 5px;
			background: #fff;
			display: grid;
			place-items: center;
		}

		body.self-registration-mode .kiosk-office-choice .office-details {
			min-width: 0;
			flex: 1;
		}

		body.self-registration-mode .kiosk-office-choice .office-details strong {
			display: block;
			color: #1e293b;
			font-size: 0.76rem;
			line-height: 1.35;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}

		body.self-registration-mode .kiosk-office-choice .office-details small {
			display: block;
			margin-top: 3px;
			color: #64748b;
			font-size: 0.65rem;
		}

		body.self-registration-mode .kiosk-office-choice .office-check {
			width: 22px;
			height: 22px;
			display: none;
			place-items: center;
			flex-shrink: 0;
			border-radius: 50%;
			background: #2945a5;
			color: #fff;
			font-size: 12px;
		}

		body.self-registration-mode .kiosk-office-choice > input:focus-visible + label {
			border-color: #2945a5;
			box-shadow: 0 0 0 4px rgba(41, 69, 165, 0.1);
		}

		body.self-registration-mode .kiosk-office-choice > input:checked + label {
			border-color: #2945a5;
			background: #edf2ff;
			box-shadow: 0 0 0 3px rgba(41, 69, 165, 0.08);
		}

		body.self-registration-mode .kiosk-office-choice > input:checked + label .checkbox-indicator,
		body.self-registration-mode .kiosk-office-choice > input:checked + label .radio-indicator {
			border-color: #2945a5;
			background: #2945a5;
			box-shadow: none;
		}

		body.self-registration-mode .kiosk-office-choice > input:checked + label .checkbox-indicator::after {
			content: "";
			width: 5px;
			height: 9px;
			margin-bottom: 2px;
			border: solid #fff;
			border-width: 0 2px 2px 0;
			transform: rotate(45deg);
		}

		body.self-registration-mode .kiosk-office-choice > input:indeterminate + label {
			border-color: #2945a5;
			background: #edf2ff;
		}

		body.self-registration-mode .kiosk-office-choice > input:indeterminate + label .checkbox-indicator {
			border-color: #2945a5;
			background: #2945a5;
		}

		body.self-registration-mode .kiosk-office-choice > input:indeterminate + label .checkbox-indicator::after {
			content: "";
			width: 9px;
			height: 2px;
			margin: 0;
			background: #fff;
			border: 0;
			transform: none;
		}

		body.self-registration-mode .kiosk-office-choice > input:checked + label .office-check {
			display: grid;
		}

		.other-destination-wrap {
			overflow: hidden;
			max-height: 0;
			opacity: 0;
			margin-top: 0;
			transition: max-height 0.25s ease, opacity 0.2s ease, margin-top 0.25s ease;
		}

		.other-destination-wrap.is-visible {
			max-height: 120px;
			opacity: 1;
			margin-top: 17px;
		}

		body.self-registration-mode .other-destination-wrap.is-visible {
			grid-column: 1 / -1;
		}

		.office-option input[type="checkbox"] {
			width: 16px;
			height: 16px;
		}

		/* legacy tile class kept as alias during transition */
		body.self-registration-mode .kiosk-office-tile {
			display: none;
		}

		body.self-registration-mode .kiosk-verify-actions {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-top: 0;
			padding-top: 25px;
			border-top: 0;
		}

		body.self-registration-mode .kiosk-verify-back {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 7px;
			min-height: 48px;
			min-width: 0;
			padding: 0 20px;
			border: 1px solid #dbe3ef;
			border-radius: 11px;
			background: #fff;
			color: #334155;
			font-size: 0.83rem;
			font-weight: 750;
			cursor: pointer;
			transition: transform 0.2s ease, background 0.2s ease;
		}

		body.self-registration-mode .kiosk-verify-back:hover {
			background: #f8fafc;
			transform: translateY(-1px);
		}

		body.self-registration-mode .kiosk-verify-proceed {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 7px;
			min-height: 48px;
			min-width: 0;
			padding: 0 20px;
			border: none;
			border-radius: 11px;
			background: #2945a5;
			color: #fff;
			font-size: 0.83rem;
			font-weight: 750;
			cursor: pointer;
			box-shadow: 0 6px 16px rgba(41, 69, 165, 0.22);
			transition: transform 0.2s ease, background 0.2s ease;
		}

		body.self-registration-mode .kiosk-verify-proceed:hover {
			background: #1d3485;
			transform: translateY(-1px);
		}

		body.self-registration-mode .kiosk-verify-sidebar {
			position: sticky;
			top: calc(86px + env(safe-area-inset-top));
			display: grid;
			gap: 16px;
			min-height: 0;
			overflow: visible;
		}

		body.self-registration-mode .kiosk-summary-card,
		body.self-registration-mode .kiosk-verify-tips-card {
			background: #fff;
			border-radius: 16px;
			border: 1px solid #dbe3ef;
			box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
			padding: 20px;
		}

		body.self-registration-mode .kiosk-summary-card-title,
		body.self-registration-mode .kiosk-verify-tips-title {
			display: flex;
			align-items: center;
			gap: 9px;
			margin: 0;
			font-size: 0.94rem;
			font-weight: 700;
			color: #111827;
		}

		body.self-registration-mode .kiosk-summary-card-title .bi,
		body.self-registration-mode .kiosk-verify-tips-title .bi {
			color: #2945a5;
			font-size: 20px;
		}

		body.self-registration-mode .kiosk-summary-badge {
			width: fit-content;
			margin-top: 15px;
			padding: 6px 11px;
			display: inline-flex;
			border-radius: 999px;
			background: #eafaf2;
			color: #087847;
			font-size: 0.7rem;
			font-weight: 750;
		}

		body.self-registration-mode .kiosk-summary-progress-label {
			margin-top: 18px;
			margin-bottom: 8px;
			display: flex;
			justify-content: space-between;
			gap: 10px;
			color: #64748b;
			font-size: 0.72rem;
		}

		body.self-registration-mode .kiosk-summary-progress-value {
			color: #2945a5;
			font-weight: 700;
		}

		body.self-registration-mode .kiosk-summary-progress-track {
			height: 7px;
			overflow: hidden;
			border-radius: 999px;
			background: #e2e8f0;
		}

		body.self-registration-mode .kiosk-summary-progress-fill {
			display: block;
			height: 100%;
			width: 66%;
			border-radius: inherit;
			background: linear-gradient(90deg, #2945a5, #4285ef);
		}

		body.self-registration-mode .kiosk-id-scan-status {
			margin-top: 18px;
			padding: 15px;
			display: flex;
			align-items: flex-start;
			gap: 11px;
			border-radius: 12px;
			border: 1px solid #a9e8c8;
			background: #eafaf2;
		}

		body.self-registration-mode .kiosk-id-scan-status > .bi {
			width: 24px;
			height: 24px;
			display: grid;
			place-items: center;
			flex-shrink: 0;
			border-radius: 50%;
			background: #18ad67;
			color: #fff;
			font-size: 14px;
		}

		body.self-registration-mode .kiosk-id-scan-status strong {
			display: block;
			color: #11663f;
			font-size: 0.78rem;
		}

		body.self-registration-mode .kiosk-id-scan-status span {
			display: none;
		}

		body.self-registration-mode .kiosk-id-scan-status small {
			display: block;
			margin-top: 5px;
			color: #398062;
			font-size: 0.68rem;
			line-height: 1.5;
		}

		body.self-registration-mode .kiosk-verify-tips-list {
			margin: 17px 0 0;
			padding: 0;
			display: grid;
			gap: 13px;
			list-style: none;
		}

		body.self-registration-mode .kiosk-verify-tips-list li {
			display: flex;
			align-items: flex-start;
			gap: 9px;
			color: #56657a;
			font-size: 0.74rem;
			line-height: 1.45;
		}

		body.self-registration-mode .kiosk-verify-tips-list .bi {
			width: 18px;
			height: 18px;
			display: grid;
			place-items: center;
			flex-shrink: 0;
			border-radius: 50%;
			background: #18ad67;
			color: #fff;
			font-size: 10px;
			margin-top: 0;
		}

		body.self-registration-mode .kiosk-mobile-action-bar {
			display: none;
		}

		body.self-registration-mode .kiosk-mobile-action-bar .mobile-back-button,
		body.self-registration-mode .kiosk-mobile-action-bar .mobile-continue-button {
			min-height: 52px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 7px;
			border-radius: 12px;
			font-size: 0.85rem;
			font-weight: 750;
			text-decoration: none;
			cursor: pointer;
			transition: transform 0.15s ease, background 0.2s ease;
		}

		body.self-registration-mode .kiosk-mobile-action-bar .mobile-back-button {
			min-width: 88px;
			padding: 0 13px;
			border: 1px solid #d8e1ee;
			background: #fff;
			color: #334155;
		}

		body.self-registration-mode .kiosk-mobile-action-bar .mobile-continue-button {
			width: 100%;
			padding: 0 18px;
			border: 0;
			background: #2945a5;
			color: #fff;
			box-shadow: 0 6px 15px rgba(41, 69, 165, 0.23);
		}

		body.self-registration-mode .kiosk-mobile-action-bar .mobile-back-button:active,
		body.self-registration-mode .kiosk-mobile-action-bar .mobile-continue-button:active {
			transform: scale(0.98);
		}

		body.self-registration-mode .registration-complete:not(.is-hidden) {
			flex: 1 1 auto;
			display: flex !important;
			flex-direction: column;
			align-items: stretch;
			justify-content: flex-start;
			align-self: center;
			width: min(100%, 1100px);
			max-width: 1100px;
			min-height: 0;
			overflow-x: hidden;
			overflow-y: auto;
			margin: 0 auto;
			padding: 24px 28px 32px;
			background: transparent;
			gap: 14px;
			scrollbar-width: thin;
			box-sizing: border-box;
		}

		body.self-registration-mode .registration-complete:not(.is-hidden) > * {
			width: 100%;
			max-width: 1100px;
			flex-shrink: 0;
			margin-left: auto;
			margin-right: auto;
		}

		body.self-registration-mode .registration-complete:not(.is-hidden) .qr-success {
			width: 100%;
		}

		body.self-registration-mode .visitor-card,
		body.self-registration-mode .visitor-field,
		body.self-registration-mode .visitor-textarea-wrap {
			padding: 14px 16px;
			margin-bottom: 0;
			border-radius: 12px;
			box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
		}

		body.self-registration-mode .visitor-card-title {
			font-size: 1rem;
			font-weight: 700;
			margin-bottom: 12px;
			color: #0f172a;
		}

		body.self-registration-mode .visitor-details-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 10px 14px;
		}

		body.self-registration-mode .visitor-details-grid .visitor-input-group:nth-child(3) {
			grid-column: 1 / -1;
		}

		body.self-registration-mode .visitor-label {
			font-size: 13px;
			font-weight: 600;
			margin-bottom: 6px;
			color: #334155;
		}

		body.self-registration-mode .visitor-input,
		body.self-registration-mode .visitor-select,
		body.self-registration-mode .visitor-textarea {
			padding: 9px 12px;
			font-size: 14px;
			min-height: 40px;
			max-width: 100%;
		}

		body.self-registration-mode .visitor-textarea {
			min-height: 72px;
			max-height: 100px;
			resize: none;
		}

		body.self-registration-mode .visitor-submit {
			width: 100%;
			max-width: 360px;
			align-self: center;
			height: 50px;
			margin-top: 6px;
			font-size: 15px;
			font-weight: 600;
			border-radius: 12px;
		}

		body.self-registration-mode .office-list {
			max-height: none;
			overflow: visible;
		}

		body.self-registration-mode .qr-success-qr-frame {
			width: 200px;
			height: 180px;
		}

		body.self-registration-mode .qr-box,
		body.self-registration-mode .qr-box canvas,
		body.self-registration-mode .qr-box img {
			width: 150px !important;
			height: 150px !important;
		}

		body.self-registration-mode.kiosk-type-select .self-reg-header-progress {
			display: none;
		}

		body.self-registration-mode .self-reg-header-step--center {
			flex: none;
			margin: 0;
			text-align: center;
			font-size: 0.95rem;
			font-weight: 600;
			color: rgba(255, 255, 255, 0.95);
			line-height: 1.2;
		}

		body.self-registration-mode .kiosk-type-picker {
			flex: 1;
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 0;
			padding: 24px;
			background: linear-gradient(rgba(31, 52, 143, 0.72), rgba(31, 52, 143, 0.72)),
				url('{{ asset('picture/lipa.png') }}') no-repeat center center / cover;
		}

		/* Type picker: extend background to the top so no body-color stripe shows under the fixed header. */
		body.self-registration-mode.kiosk-type-select {
			--self-reg-header-height: calc(72px + env(safe-area-inset-top, 0px));
			background: #1f348f;
		}

		body.self-registration-mode.kiosk-type-select .main {
			padding-top: 0 !important;
			min-height: 100dvh;
		}

		body.self-registration-mode.kiosk-type-select .self-registration-body {
			min-height: 100dvh;
		}

		body.self-registration-mode.kiosk-type-select .kiosk-type-picker {
			min-height: 100dvh;
			padding-top: calc(var(--self-reg-header-height) + 24px);
			padding-bottom: max(24px, env(safe-area-inset-bottom, 0px));
			box-sizing: border-box;
		}

		body.self-registration-mode.kiosk-type-select .self-registration-header {
			border-bottom: none;
			box-shadow: none;
		}

		body.self-registration-mode .kiosk-type-picker-inner {
			width: min(100%, 960px);
			text-align: center;
		}

		body.self-registration-mode .kiosk-type-picker-title {
			margin: 0 0 8px;
			font-size: clamp(1.75rem, 3vw, 2.25rem);
			font-weight: 800;
			color: #ffffff;
			letter-spacing: -0.03em;
			text-shadow: 0 2px 12px rgba(15, 23, 42, 0.35);
		}

		body.self-registration-mode .kiosk-type-picker-subtitle {
			margin: 0 0 18px;
			font-size: clamp(0.95rem, 1.5vw, 1.1rem);
			color: rgba(255, 255, 255, 0.88);
			line-height: 1.5;
			text-shadow: 0 1px 8px rgba(15, 23, 42, 0.3);
		}

		body.self-registration-mode .kiosk-guard-duty {
			width: min(100%, 720px);
			margin: 0 auto 20px;
			text-align: left;
		}

		body.self-registration-mode .kiosk-guard-duty-card {
			display: flex;
			align-items: center;
			gap: 14px;
			padding: 14px 16px;
			border-radius: 16px;
			background: rgba(255, 255, 255, 0.92);
			border: 1px solid rgba(255, 255, 255, 0.55);
			box-shadow: 0 10px 28px rgba(15, 23, 42, 0.16);
			backdrop-filter: blur(10px);
		}

		body.self-registration-mode .kiosk-guard-duty-card.is-hidden {
			display: none;
		}

		body.self-registration-mode .kiosk-guard-duty-icon {
			width: 42px;
			height: 42px;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
			background: #eef2ff;
			color: #243c96;
			font-size: 20px;
		}

		body.self-registration-mode .kiosk-guard-duty-icon.warn {
			background: #fff7ed;
			color: #c2410c;
		}

		body.self-registration-mode .kiosk-guard-duty-copy {
			flex: 1;
			min-width: 0;
		}

		body.self-registration-mode .kiosk-guard-duty-label {
			margin: 0 0 2px;
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 0.06em;
			text-transform: uppercase;
			color: #243c96;
		}

		body.self-registration-mode .kiosk-guard-duty-name {
			margin: 0;
			font-size: 1rem;
			font-weight: 800;
			color: #0f172a;
			line-height: 1.25;
		}

		body.self-registration-mode .kiosk-guard-duty-meta,
		body.self-registration-mode .kiosk-guard-duty-empty p {
			margin: 2px 0 0;
			font-size: 12px;
			color: #64748b;
			line-height: 1.4;
		}

		body.self-registration-mode .kiosk-guard-duty-empty .kiosk-guard-duty-name {
			font-size: 0.95rem;
		}

		body.self-registration-mode .kiosk-guard-duty-side {
			display: flex;
			flex-direction: column;
			align-items: flex-end;
			gap: 8px;
			flex-shrink: 0;
		}

		body.self-registration-mode .kiosk-guard-duty-status {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 4px 10px;
			border-radius: 999px;
			background: #ecfdf5;
			color: #047857;
			font-size: 11px;
			font-weight: 800;
			letter-spacing: 0.04em;
		}

		body.self-registration-mode .kiosk-guard-duty-status::before {
			content: '';
			width: 7px;
			height: 7px;
			border-radius: 50%;
			background: #10b981;
			box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.18);
		}

		body.self-registration-mode .kiosk-guard-duty-btn {
			appearance: none;
			border: 0;
			border-radius: 10px;
			padding: 8px 12px;
			background: #243c96;
			color: #fff;
			font-size: 12px;
			font-weight: 700;
			white-space: nowrap;
			cursor: pointer;
			transition: background 0.15s ease, transform 0.15s ease;
		}

		body.self-registration-mode .kiosk-guard-duty-btn:hover {
			background: #1d327d;
			transform: translateY(-1px);
		}

		body.self-registration-mode .kiosk-guard-duty-btn.secondary {
			background: #fff;
			color: #243c96;
			border: 1px solid #c7d2fe;
		}

		body.self-registration-mode .kiosk-guard-duty-btn.secondary:hover {
			background: #eef2ff;
		}

		body.self-registration-mode .kiosk-guard-duty-btn.danger {
			background: #fff;
			color: #b91c1c;
			border: 1px solid #fecaca;
		}

		body.self-registration-mode .kiosk-guard-duty-btn.danger:hover {
			background: #fef2f2;
		}

		body.self-registration-mode .kiosk-guard-duty-since {
			margin: 0;
			font-size: 11px;
			font-weight: 700;
			color: #047857;
		}

		body.self-registration-mode .kiosk-guard-duty-actions {
			display: flex;
			flex-wrap: wrap;
			justify-content: flex-end;
			gap: 8px;
		}

		body.self-registration-mode .end-duty-profile {
			margin-bottom: 14px;
			padding: 12px 14px;
			border-radius: 12px;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			text-align: left;
		}

		body.self-registration-mode .end-duty-profile strong {
			display: block;
			color: #0f172a;
			font-size: 16px;
		}

		body.self-registration-mode .end-duty-profile span,
		body.self-registration-mode .end-duty-profile small {
			display: block;
			margin-top: 3px;
			color: #475569;
			font-size: 13px;
		}

		body.self-registration-mode .end-duty-started {
			margin: 12px 0 0;
			padding-top: 10px;
			border-top: 1px solid #e2e8f0;
		}

		body.self-registration-mode .end-duty-started span {
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 0.04em;
			text-transform: uppercase;
			color: #64748b;
		}

		body.self-registration-mode .end-duty-started strong {
			margin-top: 2px;
			font-size: 14px;
		}

		body.self-registration-mode .end-duty-warning {
			margin: 0 0 14px;
			font-size: 14px;
			line-height: 1.5;
			color: #334155;
			text-align: left;
		}

		.confirmation-modal-btn.danger {
			background: #dc2626;
			color: #ffffff;
		}

		.confirmation-modal-btn.danger:hover {
			background: #b91c1c;
			filter: none;
		}

		.confirmation-modal-footer.is-choice {
			flex-wrap: wrap;
			justify-content: flex-end;
		}

		.confirmation-modal-footer.is-choice .confirmation-modal-btn {
			min-width: 0;
		}

		body.self-registration-mode .guard-duty-current {
			margin-bottom: 14px;
			padding: 12px 14px;
			border-radius: 12px;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			text-align: left;
		}

		body.self-registration-mode .guard-duty-current.is-hidden {
			display: none;
		}

		body.self-registration-mode .guard-duty-current span {
			display: block;
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 0.04em;
			text-transform: uppercase;
			color: #64748b;
		}

		body.self-registration-mode .guard-duty-current strong {
			display: block;
			margin-top: 4px;
			color: #0f172a;
			font-size: 15px;
		}

		body.self-registration-mode .guard-duty-current small {
			display: block;
			margin-top: 2px;
			color: #475569;
			font-size: 12px;
		}

		body.self-registration-mode .guard-duty-field {
			display: flex;
			flex-direction: column;
			gap: 6px;
			margin-bottom: 12px;
			text-align: left;
		}

		body.self-registration-mode .guard-duty-field label {
			font-size: 13px;
			font-weight: 600;
			color: #334155;
		}

		body.self-registration-mode .guard-duty-field input {
			height: 44px;
			border: 1px solid #dbe2ef;
			border-radius: 12px;
			padding: 0 14px;
			font-size: 14px;
			color: #0f172a;
		}

		body.self-registration-mode .guard-duty-field input:focus {
			outline: none;
			border-color: #243c96;
			box-shadow: 0 0 0 3px rgba(36, 60, 150, 0.12);
		}

		body.self-registration-mode .guard-duty-error {
			min-height: 18px;
			margin: 0 0 4px;
			font-size: 13px;
			color: #b91c1c;
			text-align: left;
		}

		.confirmation-modal-btn:disabled {
			opacity: 0.65;
			cursor: wait;
		}

		body.self-registration-mode .kiosk-type-grid {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 18px;
		}

		body.self-registration-mode .kiosk-type-card {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			gap: 12px;
			min-height: 220px;
			padding: 28px 20px;
			border: 2px solid #dbe2ef;
			border-radius: 20px;
			background: #fff;
			color: inherit;
			text-decoration: none;
			box-shadow: 0 8px 28px rgba(36, 60, 150, 0.08);
			transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
		}

		body.self-registration-mode .kiosk-type-card:hover {
			transform: translateY(-4px);
			border-color: #243c96;
			box-shadow: 0 14px 36px rgba(36, 60, 150, 0.14);
		}

		body.self-registration-mode .kiosk-type-card.is-disabled,
		body.self-registration-mode .kiosk-type-grid.is-locked .kiosk-type-card {
			opacity: 0.46;
			filter: grayscale(0.28);
			pointer-events: none;
			cursor: not-allowed;
			box-shadow: none;
		}

		body.self-registration-mode .kiosk-type-card.is-disabled:hover,
		body.self-registration-mode .kiosk-type-grid.is-locked .kiosk-type-card:hover {
			transform: none;
			border-color: #dbe2ef;
			box-shadow: none;
		}

		body.self-registration-mode .kiosk-type-card.is-active {
			border-color: #243c96;
			background: linear-gradient(180deg, #f5f7ff 0%, #ffffff 100%);
			box-shadow: 0 12px 32px rgba(36, 60, 150, 0.16);
		}

		body.self-registration-mode .kiosk-type-icon {
			width: 64px;
			height: 64px;
			border-radius: 18px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 28px;
			background: #eef2ff;
			color: #243c96;
		}

		body.self-registration-mode .kiosk-type-card[data-type="enrollee"] .kiosk-type-icon {
			background: #ecfdf5;
			color: #059669;
		}

		body.self-registration-mode .kiosk-type-card[data-type="contractor"] .kiosk-type-icon {
			background: #fff7ed;
			color: #ea580c;
		}

		body.self-registration-mode .kiosk-type-name {
			font-size: 1.15rem;
			font-weight: 700;
			color: #0f172a;
		}

		body.self-registration-mode .kiosk-type-desc {
			font-size: 0.88rem;
			color: #64748b;
			line-height: 1.45;
			max-width: 220px;
		}

		body.self-registration-mode .kiosk-type-switcher {
			display: flex;
			flex-wrap: wrap;
			gap: 6px;
			margin-bottom: 4px;
		}

		body.self-registration-mode .kiosk-type-pill {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			padding: 5px 10px;
			border-radius: 999px;
			border: 1px solid #dbe2ef;
			background: #f8fafc;
			color: #475569;
			font-size: 11px;
			font-weight: 600;
			text-decoration: none;
			transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
		}

		body.self-registration-mode .kiosk-type-pill:hover {
			border-color: #243c96;
			color: #243c96;
			background: #eef2ff;
		}

		body.self-registration-mode .kiosk-type-pill.active {
			border-color: #243c96;
			background: #243c96;
			color: #fff;
		}

		body.self-registration-mode .kiosk-type-pill .bi {
			font-size: 12px;
		}

		.kiosk-privacy-modal {
			position: fixed;
			inset: 0;
			z-index: 1400;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 20px;
			background: rgba(15, 23, 42, 0.62);
			backdrop-filter: blur(4px);
		}

		.kiosk-privacy-modal.is-hidden {
			display: none !important;
		}

		.kiosk-privacy-card {
			width: min(100%, 640px);
			max-height: min(88svh, 720px);
			display: flex;
			flex-direction: column;
			background: #fff;
			border-radius: 18px;
			box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
			overflow: hidden;
		}

		.kiosk-privacy-header {
			flex-shrink: 0;
			padding: 22px 24px 14px;
			border-bottom: 1px solid #e2e8f0;
		}

		.kiosk-privacy-badge {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			margin-bottom: 10px;
			padding: 5px 10px;
			border-radius: 999px;
			background: #eef2ff;
			color: #243c96;
			font-size: 12px;
			font-weight: 700;
		}

		.kiosk-privacy-title {
			margin: 0;
			font-size: 1.35rem;
			font-weight: 800;
			color: #0f172a;
			letter-spacing: -0.02em;
		}

		.kiosk-privacy-subtitle {
			margin: 6px 0 0;
			font-size: 0.92rem;
			color: #64748b;
			line-height: 1.45;
		}

		.kiosk-privacy-body {
			flex: 1;
			min-height: 0;
			overflow-y: auto;
			padding: 18px 24px;
			scrollbar-width: thin;
		}

		.kiosk-privacy-body h3 {
			margin: 0 0 8px;
			font-size: 0.95rem;
			font-weight: 700;
			color: #243c96;
		}

		.kiosk-privacy-body p,
		.kiosk-privacy-body li {
			font-size: 0.9rem;
			line-height: 1.55;
			color: #334155;
		}

		.kiosk-privacy-body p {
			margin: 0 0 12px;
		}

		.kiosk-privacy-body ul {
			margin: 0 0 14px;
			padding-left: 1.15rem;
		}

		.kiosk-privacy-body li {
			margin-bottom: 6px;
		}

		.kiosk-privacy-footer {
			flex-shrink: 0;
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			justify-content: flex-end;
			padding: 16px 24px 20px;
			border-top: 1px solid #e2e8f0;
			background: #f8fafc;
		}

		.kiosk-privacy-btn {
			min-width: 140px;
			height: 48px;
			padding: 0 18px;
			border-radius: 12px;
			font-size: 15px;
			font-weight: 700;
			cursor: pointer;
			border: 1px solid transparent;
		}

		.kiosk-privacy-btn.secondary {
			background: #fff;
			border-color: #cbd5e1;
			color: #475569;
		}

		.kiosk-privacy-btn.secondary:hover {
			background: #f1f5f9;
		}

		.kiosk-privacy-btn.primary {
			background: #243c96;
			color: #fff;
		}

		.kiosk-privacy-btn.primary:hover {
			filter: brightness(1.05);
		}

		@media (max-width: 640px) {
			.kiosk-privacy-footer {
				flex-direction: column-reverse;
			}

			.kiosk-privacy-btn {
				width: 100%;
			}
		}

		body.self-registration-mode .kiosk-controls-divider {
			height: 1px;
			background: #e2e8f0;
			margin: 4px 0;
		}

		body.self-registration-mode .kiosk-change-type-label {
			margin: 0;
			font-size: 11px;
			font-weight: 600;
			color: #94a3b8;
			text-transform: uppercase;
			letter-spacing: 0.04em;
		}

		body.self-registration-mode .kiosk-back-type-link {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			margin-top: 2px;
			font-size: 12px;
			font-weight: 600;
			color: #64748b;
			text-decoration: none;
		}

		body.self-registration-mode .kiosk-back-type-link:hover {
			color: #243c96;
		}

		body.self-registration-mode .kiosk-controls-type-note {
			margin: -6px 0 0;
			font-size: 12px;
			color: #64748b;
		}

		@media (max-width: 1180px) {
			body.self-registration-mode .kiosk-verify-layout {
				grid-template-columns: minmax(0, 1fr) 280px;
				overflow-y: auto;
			}

			body.self-registration-mode .kiosk-office-grid {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
		}

		@media (max-width: 1100px) {
			body.self-registration-mode .kiosk-scan-layout,
			body.self-registration-mode .scan-layout.kiosk-scan-layout {
				display: flex !important;
				flex-direction: column;
				grid-template-columns: none;
			}

			body.self-registration-mode .kiosk-scan-main,
			body.self-registration-mode .scan-main.kiosk-scan-main {
				order: 1;
				padding: 16px;
				overflow: visible;
			}

			body.self-registration-mode .kiosk-bottom-panels,
			body.self-registration-mode .desktop-info-cards.kiosk-bottom-panels {
				grid-template-columns: 1fr;
			}

			body.self-registration-mode .scanner-zone,
			body.self-registration-mode .scanner-zone.camera-frame {
				flex: none;
				min-height: 280px;
				max-height: none;
				aspect-ratio: 4 / 3;
			}

			body.self-registration-mode .camera-section,
			body.self-registration-mode .scan-heading.kiosk-scan-heading,
			body.self-registration-mode .desktop-info-cards.kiosk-bottom-panels {
				max-width: none;
			}

			body.self-registration-mode .kiosk-controls,
			body.self-registration-mode .scan-sidebar.kiosk-controls {
				order: 2;
				width: 100%;
				flex-shrink: 0;
				padding: 16px 18px;
				gap: 10px;
				border-left: none;
				border-top: 1px solid #e2e8f0;
				position: static;
			}

			body.self-registration-mode .kiosk-controls-title {
				font-size: 1.1rem;
			}

			body.self-registration-mode .scan-action,
			body.self-registration-mode .gallery-action {
				height: 46px;
			}

			body.self-registration-mode .self-reg-step-label {
				font-size: 0.6rem;
			}

			body.self-registration-mode .kiosk-verify-layout {
				width: min(100% - 28px, 760px);
				margin: 14px auto 0;
				padding: 0 0 max(20px, env(safe-area-inset-bottom));
				grid-template-columns: 1fr;
				gap: 14px;
			}

			body.self-registration-mode .kiosk-mobile-progress {
				display: none;
			}

			body.self-registration-mode .kiosk-verify-sidebar {
				position: static;
				grid-row: 1;
				grid-template-columns: repeat(2, minmax(0, 1fr));
				display: grid;
			}

			body.self-registration-mode .kiosk-verify-main {
				grid-row: 2;
			}

			body.self-registration-mode .id-guide {
				width: min(420px, 72%);
			}
		}

		@media (max-width: 640px) {
			body.self-registration-mode .self-registration-header {
				position: fixed;
				top: 0;
				left: 0;
				right: 0;
				z-index: 300;
				min-height: auto;
				padding:
					max(9px, env(safe-area-inset-top))
					max(12px, env(safe-area-inset-right))
					8px
					max(12px, env(safe-area-inset-left));
				display: grid;
				grid-template-columns: minmax(0, 1fr) auto;
				grid-template-rows: auto auto;
				gap: 8px 10px;
				align-items: center;
			}

			body.self-registration-mode .main {
				padding-top: calc(108px + env(safe-area-inset-top)) !important;
			}

			body.self-registration-mode.kiosk-type-select .main {
				padding-top: 0 !important;
			}

			body.self-registration-mode:not(.kiosk-type-select) .self-registration-brand {
				grid-column: 1;
				grid-row: 1;
				min-width: 0;
			}

			body.self-registration-mode:not(.kiosk-type-select) .self-reg-logout-btn {
				grid-column: 2;
				grid-row: 1;
				justify-self: end;
				width: 40px;
				height: 40px;
				min-width: 40px;
				min-height: 40px;
				padding: 0;
				border-radius: 10px;
			}

			body.self-registration-mode:not(.kiosk-type-select) .self-reg-header-progress {
				grid-column: 1 / -1;
				grid-row: 2;
				max-width: none;
				width: 100%;
				margin-top: 1px;
			}

			body.self-registration-mode:not(.kiosk-type-select) .self-reg-stepper {
				width: 100%;
				display: grid;
				grid-template-columns:
					minmax(54px, auto)
					minmax(20px, 1fr)
					minmax(70px, auto)
					minmax(20px, 1fr)
					minmax(54px, auto);
				align-items: start;
				gap: 0;
			}

			body.self-registration-mode:not(.kiosk-type-select) .self-reg-step {
				min-width: 0;
				gap: 4px;
			}

			body.self-registration-mode:not(.kiosk-type-select) .self-reg-step-num {
				width: 27px;
				height: 27px;
				font-size: 0.7rem;
			}

			body.self-registration-mode:not(.kiosk-type-select) .self-reg-step-label {
				max-width: 72px;
				font-size: 0.58rem;
				line-height: 1.2;
				text-align: center;
				white-space: normal;
			}

			body.self-registration-mode:not(.kiosk-type-select) .self-reg-step-line {
				width: 100%;
				min-width: 0;
				margin: 13px 0 0;
			}

			body.self-registration-mode .self-registration-brand .brand-icon {
				width: 38px;
				height: 38px;
				font-size: 18px;
				border-radius: 10px;
			}

			body.self-registration-mode .self-registration-brand .brand-title span {
				font-size: 1.05rem;
			}

			body.self-registration-mode .self-registration-brand .brand-subtitle {
				font-size: 0.58rem;
			}

			body.self-registration-mode .self-reg-logout-btn span {
				display: none;
			}

			body.self-registration-mode .self-reg-header-step--center {
				display: none;
			}

			body.self-registration-mode.kiosk-type-select .self-registration-header {
				grid-template-columns: minmax(0, 1fr) auto;
				grid-template-rows: auto;
				align-items: center;
			}

			body.self-registration-mode.kiosk-type-select .self-registration-brand {
				grid-column: 1;
				grid-row: 1;
			}

			body.self-registration-mode.kiosk-type-select .self-reg-logout-btn {
				grid-column: 2;
				grid-row: 1;
				justify-self: end;
				width: 40px;
				height: 40px;
				min-width: 40px;
				min-height: 40px;
				padding: 0;
				border-radius: 10px;
			}

			body.self-registration-mode.kiosk-type-select .self-reg-logout-btn span {
				display: none;
			}

			body.self-registration-mode .scan-actions-row {
				flex-direction: column;
			}

			body.self-registration-mode .scan-action,
			body.self-registration-mode .gallery-action {
				flex: 1;
				width: 100%;
				height: 46px;
				font-size: 13px;
			}

			body.self-registration-mode .id-types-list {
				display: none;
			}

			body.self-registration-mode .id-types-title {
				margin: 0;
			}

			body.self-registration-mode .kiosk-type-grid {
				grid-template-columns: 1fr;
				gap: 12px;
			}

			body.self-registration-mode .kiosk-guard-duty-card {
				flex-wrap: wrap;
			}

			body.self-registration-mode .kiosk-guard-duty-side {
				width: 100%;
				flex-direction: row;
				flex-wrap: wrap;
				justify-content: space-between;
				align-items: center;
			}

			body.self-registration-mode .kiosk-guard-duty-actions {
				width: 100%;
			}

			body.self-registration-mode .kiosk-guard-duty-actions .kiosk-guard-duty-btn {
				flex: 1;
			}

			body.self-registration-mode .kiosk-type-card {
				min-height: auto;
				text-align: center;
			}

			body.self-registration-mode .kiosk-type-icon {
				width: 56px;
				height: 56px;
				font-size: 24px;
			}

			body.self-registration-mode .kiosk-type-desc {
				max-width: none;
			}

			body.self-registration-mode .kiosk-mobile-progress {
				display: none !important;
			}

			body.self-registration-mode .kiosk-verify-layout {
				width: calc(100% - 20px);
				margin-top: 12px;
				gap: 14px;
			}

			body.self-registration-mode .kiosk-verify-sidebar {
				display: block;
			}

			body.self-registration-mode .kiosk-verify-tips-card {
				display: none;
			}

			body.self-registration-mode .kiosk-summary-card {
				padding: 15px;
				border-radius: 14px;
			}

			body.self-registration-mode .kiosk-verify-card {
				border-radius: 16px;
			}

			body.self-registration-mode .kiosk-verify-head {
				padding: 18px 16px;
				align-items: flex-start;
				gap: 12px;
			}

			body.self-registration-mode .kiosk-verify-head-icon {
				width: 42px;
				height: 42px;
				border-radius: 12px;
			}

			body.self-registration-mode .kiosk-verify-head h2 {
				font-size: 1.15rem;
			}

			body.self-registration-mode .kiosk-verify-head p {
				font-size: 0.77rem;
			}

			body.self-registration-mode .kiosk-verify-form-body {
				padding: 0 16px 30px;
			}

			body.self-registration-mode .kiosk-form-section {
				padding: 21px 0;
			}

			body.self-registration-mode .kiosk-form-section:last-of-type {
				padding-bottom: 36px;
				border-bottom: 0;
			}

			body.self-registration-mode .kiosk-form-grid.cols-2,
			body.self-registration-mode .kiosk-form-grid.cols-3 {
				grid-template-columns: 1fr;
			}

			body.self-registration-mode .kiosk-office-grid {
				grid-template-columns: 1fr;
				gap: 9px;
			}

			body.self-registration-mode .kiosk-office-choice > label {
				min-height: 66px;
			}

			body.self-registration-mode .kiosk-office-choice .office-details strong {
				font-size: 0.8rem;
			}

			body.self-registration-mode .kiosk-form-section .visitor-input,
			body.self-registration-mode .kiosk-form-section .visitor-textarea,
			body.self-registration-mode .kiosk-phone-prefix .visitor-input {
				min-height: 50px;
				font-size: 16px;
			}

			body.self-registration-mode .kiosk-phone-prefix .input-prefix {
				min-height: 50px;
			}

			body.self-registration-mode .kiosk-verify-actions.desktop-actions {
				display: none;
			}

			body.self-registration-mode .visitor-step:not(.is-hidden):has(.kiosk-verify-layout) {
				padding-bottom: calc(100px + env(safe-area-inset-bottom));
			}

			body.self-registration-mode .kiosk-mobile-action-bar {
				position: fixed;
				right: 0;
				bottom: 0;
				left: 0;
				z-index: 200;
				padding:
					10px
					max(12px, env(safe-area-inset-right))
					calc(10px + env(safe-area-inset-bottom))
					max(12px, env(safe-area-inset-left));
				display: grid;
				grid-template-columns: auto minmax(0, 1fr);
				align-items: center;
				gap: 10px;
				border-top: 1px solid #e2e8f0;
				background: rgba(255, 255, 255, 0.97);
				box-shadow: 0 -8px 25px rgba(15, 23, 42, 0.09);
				backdrop-filter: blur(12px);
				-webkit-backdrop-filter: blur(12px);
			}

			body.self-registration-mode .ticket-info-grid {
				grid-template-columns: 1fr;
				justify-items: center;
				text-align: center;
				gap: 12px;
			}

			body.self-registration-mode .ticket-photo-wrap {
				width: 88px;
				height: 88px;
			}

			body.self-registration-mode .qr-box,
			body.self-registration-mode .qr-box canvas,
			body.self-registration-mode .qr-box img {
				width: min(140px, 52vw) !important;
				height: min(140px, 52vw) !important;
			}

			body.self-registration-mode .kiosk-privacy-modal {
				padding: 0;
				align-items: flex-end;
			}

			body.self-registration-mode .kiosk-privacy-card {
				width: 100%;
				max-height: min(92dvh, 720px);
				border-radius: 18px 18px 0 0;
			}

			body.self-registration-mode .kiosk-privacy-footer {
				flex-direction: column-reverse;
			}

			body.self-registration-mode .kiosk-privacy-btn {
				width: 100%;
			}
		}

		@media (max-width: 991.98px) {
			body.self-registration-mode .layout {
				height: auto !important;
				max-height: none !important;
				overflow: visible !important;
			}

			body.self-registration-mode .main {
				margin-left: 0 !important;
				overflow: visible !important;
				height: auto !important;
				max-height: none !important;
				padding-top: calc(72px + env(safe-area-inset-top)) !important;
			}

			body.self-registration-mode .visitor-step:not(.is-hidden) {
				padding: 0;
			}

			body.self-registration-mode .visitor-step:not(.is-hidden) > *:not(.kiosk-verify-layout):not(.kiosk-mobile-progress):not(.kiosk-mobile-action-bar) {
				width: min(100%, 560px);
			}

			body.self-registration-mode .registration-complete:not(.is-hidden) {
				width: min(100%, 100%);
				max-width: 100%;
				padding: 16px 16px 24px;
			}

			body.self-registration-mode .registration-complete:not(.is-hidden) > * {
				width: 100%;
				max-width: 100%;
			}

			body.self-registration-mode .visitor-details-grid {
				grid-template-columns: 1fr;
			}

			body.self-registration-mode .visitor-details-grid .visitor-input-group:nth-child(3) {
				grid-column: auto;
			}
		}

		.self-registration-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 16px;
			padding: 14px 0 18px;
			margin-bottom: 8px;
			border-bottom: none;
		}

		.self-registration-brand {
			display: flex;
			align-items: center;
			gap: 12px;
			min-width: 0;
		}

		.self-registration-brand .brand-icon {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			background: #243c96;
			color: #fff;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 22px;
			flex-shrink: 0;
		}

		.self-registration-brand .brand-title {
			margin: 0;
			font-size: 1.15rem;
			font-weight: 700;
			color: #0f172a;
			line-height: 1.2;
		}

		.self-registration-brand .brand-subtitle {
			display: block;
			color: #64748b;
			font-size: 0.78rem;
			line-height: 1.3;
		}

		.self-reg-logout-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			padding: 10px 16px;
			border: 1px solid #fecaca;
			border-radius: 12px;
			background: #fff;
			color: #ff3b30;
			font-weight: 700;
			text-decoration: none;
			flex-shrink: 0;
			transition: background 0.2s ease, border-color 0.2s ease;
		}

		.self-reg-logout-btn:hover {
			background: #ffe9e9;
			color: #ff3b30;
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

			body.print-ticket-mode .guard-nav-backdrop,
			body.print-ticket-mode .guard-mobile-topbar,
			body.print-ticket-mode .guard-topbar,
			body.print-ticket-mode .self-registration-header,
			body.print-ticket-mode .self-reg-header-progress,
			body.print-ticket-mode .kiosk-type-picker,
			body.print-ticket-mode .kiosk-privacy-modal,
			body.print-ticket-mode .kiosk-type-switcher,
			body.print-ticket-mode .kiosk-controls,
			body.print-ticket-mode .self-reg-subtitle,
			body.print-ticket-mode .sidebar,
			body.print-ticket-mode .page-title,
			body.print-ticket-mode .flow-head,
			body.print-ticket-mode .scanner-card,
			body.print-ticket-mode .visitor-step,
			body.print-ticket-mode .ticket-status,
			body.print-ticket-mode .qr-success-actions,
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
				display: block !important;
				visibility: visible !important;
				max-width: none !important;
				margin: 0 !important;
				padding: 0 !important;
			}

			body.print-ticket-mode #registrationCompletePanel .ticket-status,
			body.print-ticket-mode #registrationCompletePanel .qr-success-actions {
				display: none !important;
			}

			body.print-ticket-mode #ticketCapture,
			body.print-ticket-mode #registrationTicketCard {
				display: block !important;
				position: static !important;
				transform: none !important;
				width: 100%;
				max-width: 100% !important;
				margin: 0 auto !important;
				box-shadow: none;
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

		/* =========================================================
		   SELF-REG ID SCAN — new VMS scan layout
		========================================================= */

		body.self-registration-mode {
			--vms-primary: #2c48aa;
			--vms-primary-dark: #20378c;
			--vms-primary-light: #edf2ff;
			--vms-success: #12aa5b;
			--vms-success-dark: #087443;
			--vms-success-light: #edfff4;
			--vms-success-border: #a4ebc1;
			--vms-page-bg: #f1f5fa;
			--vms-surface: #ffffff;
			--vms-camera-bg: #101827;
			--vms-text: #111827;
			--vms-text-secondary: #52637c;
			--vms-muted: #64748b;
			--vms-border: #d8e1ed;
			--vms-shadow-small: 0 2px 8px rgba(15, 23, 42, 0.05);
		}

		body.self-registration-mode .self-registration-header {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			z-index: 300;
			width: 100%;
			min-height: 70px;
			padding: 9px 18px;
			padding-top: max(9px, env(safe-area-inset-top));
			display: grid;
			grid-template-columns: minmax(230px, 1fr) auto minmax(230px, 1fr);
			align-items: center;
			gap: 20px;
			background: var(--vms-primary);
			color: #fff;
			box-shadow: 0 3px 15px rgba(31, 55, 140, 0.18);
			border-bottom: none;
			margin: 0;
		}

		body.self-registration-mode .self-registration-brand {
			grid-column: 1;
			justify-self: start;
		}

		body.self-registration-mode .self-reg-header-progress {
			grid-column: 2;
			justify-self: center;
			max-width: none;
			flex: none;
		}

		body.self-registration-mode .self-reg-logout-btn {
			grid-column: 3;
			justify-self: end;
			align-self: center;
			min-height: 40px;
			padding: 0 14px;
			border: 1px solid rgba(255, 255, 255, 0.55);
			border-radius: 12px;
			background: rgba(255, 255, 255, 0.08);
			color: #fff;
			font-size: 12px;
			font-weight: 750;
		}

		body.self-registration-mode.kiosk-type-select .self-registration-header {
			grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
			align-items: center;
		}

		body.self-registration-mode.kiosk-type-select .self-registration-brand {
			grid-column: 1;
			justify-self: start;
		}

		body.self-registration-mode.kiosk-type-select .self-reg-header-step--center {
			grid-column: 2;
			justify-self: center;
			align-self: center;
			margin: 0;
			flex: none;
			white-space: nowrap;
		}

		body.self-registration-mode.kiosk-type-select .self-reg-logout-btn {
			grid-column: 3;
			justify-self: end;
			align-self: center;
		}

		body.self-registration-mode .self-reg-stepper {
			width: 430px;
			min-width: 0;
			display: grid;
			grid-template-columns:
				minmax(70px, auto)
				minmax(55px, 1fr)
				minmax(85px, auto)
				minmax(55px, 1fr)
				minmax(70px, auto);
			align-items: start;
			gap: 0;
		}

		body.self-registration-mode .self-reg-step-line {
			width: 100%;
			height: 2px;
			min-width: 0;
			margin: 14px 0 0;
			background: rgba(255, 255, 255, 0.27);
		}

		body.self-registration-mode .self-reg-logout-btn {
			grid-column: 3;
			justify-self: end;
			align-self: center;
			min-height: 40px;
			padding: 0 14px;
			border: 1px solid rgba(255, 255, 255, 0.55);
			border-radius: 12px;
			background: rgba(255, 255, 255, 0.08);
			color: #fff;
			font-size: 12px;
			font-weight: 750;
		}

		body.self-registration-mode .scan-layout.kiosk-scan-layout {
			width: 100%;
			flex: 1;
			display: grid;
			grid-template-columns: minmax(0, 1fr) 350px;
			align-items: stretch;
			overflow: visible;
			min-height: 0;
		}

		body.self-registration-mode .scan-main.kiosk-scan-main {
			min-width: 0;
			width: 100%;
			padding: 20px 22px 16px;
			overflow: visible;
			display: block;
		}

		body.self-registration-mode .scan-heading.kiosk-scan-heading {
			width: 100%;
			max-width: 720px;
			margin: 0 auto 14px;
			text-align: center;
			flex-shrink: 0;
		}

		body.self-registration-mode .scan-heading .kiosk-scan-title {
			margin: 0;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 7px;
			color: var(--vms-text);
			font-size: clamp(1.1rem, 1.6vw, 1.35rem);
			line-height: 1.25;
			font-weight: 700;
		}

		body.self-registration-mode .scan-heading .kiosk-scan-title .bi {
			color: var(--vms-primary);
			font-size: 1.15em;
		}

		body.self-registration-mode .scan-heading .kiosk-scan-desc {
			margin: 8px auto 0;
			max-width: 34rem;
			color: var(--vms-muted);
			font-size: 13px;
			line-height: 1.5;
		}

		body.self-registration-mode .camera-section {
			position: relative;
			width: 100%;
			max-width: 720px;
			margin: 0 auto;
			height: auto;
			overflow: visible;
		}

		body.self-registration-mode .scanner-zone.camera-frame {
			position: relative;
			width: 100%;
			min-height: 280px;
			max-height: 400px;
			aspect-ratio: 16 / 10;
			flex: none;
			height: auto;
			overflow: hidden;
			border-radius: 14px;
			background: var(--vms-camera-bg);
			box-shadow: var(--vms-shadow-small);
			padding: 0;
			border: 0;
		}

		body.self-registration-mode .scanner-zone .camera-feed,
		body.self-registration-mode .scanner-zone .frozen-frame {
			position: absolute;
			inset: 0;
			width: 100%;
			height: 100%;
			max-width: none;
			max-height: none;
			object-fit: cover;
			object-position: center;
			background: #050505;
			transform: none;
			top: 0;
			left: 0;
		}

		body.self-registration-mode .scanner-zone .frozen-frame {
			display: none;
			z-index: 3;
		}

		body.self-registration-mode .scanner-zone .frozen-frame.visible {
			display: block;
		}

		body.self-registration-mode .scanner-zone .camera-feed.is-mirrored {
			transform: scaleX(-1);
		}

		body.self-registration-mode .flip-camera-btn.switch-camera-button {
			position: absolute;
			top: 14px;
			right: 14px;
			z-index: 5;
			width: 38px;
			height: 38px;
			padding: 0;
			display: none;
			place-items: center;
			border: 0;
			border-radius: 10px;
			background: rgba(15, 23, 42, 0.72);
			color: #fff;
		}

		body.self-registration-mode .scanner-zone.camera-on .flip-camera-btn.switch-camera-button {
			display: grid;
		}

		body.self-registration-mode .scanner-overlay.camera-overlay {
			position: absolute;
			inset: 0;
			z-index: 4;
			pointer-events: none;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		body.self-registration-mode .id-guide {
			position: absolute;
			top: 50%;
			left: 50%;
			width: min(430px, 72%);
			aspect-ratio: 1.58 / 1;
			border: 2px solid #6676ff;
			border-radius: 15px;
			transform: translate(-50%, -50%);
			box-shadow: 0 0 0 4px rgba(83, 99, 255, 0.15);
			background: transparent;
			backdrop-filter: none;
			-webkit-backdrop-filter: none;
		}

		body.self-registration-mode .id-guide::after {
			display: none;
		}

		body.self-registration-mode .id-guide .corner,
		body.self-registration-mode .id-guide-layout,
		body.self-registration-mode .id-guide-photo,
		body.self-registration-mode .id-guide-info,
		body.self-registration-mode .id-guide-line,
		body.self-registration-mode .id-guide-chip {
			display: none;
		}

		body.self-registration-mode .id-guide-content {
			position: absolute;
			inset: 15px;
			display: grid;
			grid-template-columns: 30% minmax(0, 1fr);
			gap: 12px;
		}

		body.self-registration-mode .id-photo-placeholder {
			border: 2px solid #a8bfdc;
			border-radius: 10px;
			background: rgba(255, 255, 255, 0.05);
		}

		body.self-registration-mode .id-lines {
			display: flex;
			flex-direction: column;
			justify-content: space-around;
			gap: 8px;
		}

		body.self-registration-mode .id-lines span {
			height: 8px;
			display: block;
			border-radius: 999px;
			background: #a8bfdc;
		}

		body.self-registration-mode .id-lines span:nth-child(2) { width: 80%; }
		body.self-registration-mode .id-lines span:nth-child(3) { width: 60%; }

		body.self-registration-mode .kiosk-camera-badge.camera-ready-badge {
			position: absolute;
			bottom: 13px;
			left: 50%;
			transform: translateX(-50%);
			padding: 7px 12px;
			display: inline-flex;
			align-items: center;
			gap: 7px;
			border-radius: 999px;
			background: rgba(10, 18, 32, 0.85);
			color: #fff;
			font-size: 11px;
			font-weight: 650;
			z-index: 6;
			backdrop-filter: none;
		}

		body.self-registration-mode .camera-ready-dot,
		body.self-registration-mode .kiosk-camera-badge .badge-dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: #94a3b8;
			box-shadow: none;
		}

		body.self-registration-mode .kiosk-camera-badge.is-ready .badge-dot,
		body.self-registration-mode .kiosk-camera-badge.is-ready .camera-ready-dot {
			background: #17c66d;
			box-shadow: 0 0 0 4px rgba(23, 198, 109, 0.12);
		}

		body.self-registration-mode .kiosk-camera-badge.is-error .badge-dot,
		body.self-registration-mode .kiosk-camera-badge.is-error .camera-ready-dot {
			background: #ef4444;
			box-shadow: none;
		}

		body.self-registration-mode .desktop-info-cards.kiosk-bottom-panels {
			width: 100%;
			max-width: 720px;
			margin: 16px auto 0;
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 14px;
			flex-shrink: 0;
		}

		body.self-registration-mode .info-card.kiosk-info-card {
			min-width: 0;
			padding: 16px;
			border: 1px solid var(--vms-border);
			border-radius: 14px;
			background: var(--vms-surface);
			box-shadow: var(--vms-shadow-small);
		}

		body.self-registration-mode .info-card-title {
			margin-bottom: 12px;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		body.self-registration-mode .info-card-title .bi {
			color: var(--vms-primary);
			font-size: 18px;
		}

		body.self-registration-mode .info-card-title h2 {
			margin: 0;
			color: var(--vms-text);
			font-size: 14px;
			font-weight: 700;
		}

		body.self-registration-mode .tips-list {
			margin: 0;
			padding: 0;
			display: grid;
			gap: 10px;
			list-style: none;
		}

		body.self-registration-mode .tips-list li {
			display: flex;
			align-items: flex-start;
			gap: 8px;
			color: var(--vms-text-secondary);
			font-size: 12px;
			line-height: 1.4;
		}

		body.self-registration-mode .tip-check,
		body.self-registration-mode .supported-check {
			width: 16px;
			height: 16px;
			margin-top: 1px;
			display: grid;
			place-items: center;
			flex-shrink: 0;
			border-radius: 50%;
			background: var(--vms-primary);
			color: #fff;
			font-size: 10px;
			font-weight: 800;
		}

		body.self-registration-mode .supported-check {
			background: var(--vms-success);
		}

		body.self-registration-mode .desktop-id-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 9px;
		}

		body.self-registration-mode .desktop-id-item {
			min-height: 45px;
			padding: 10px 12px;
			display: flex;
			align-items: center;
			gap: 9px;
			border: 1px solid var(--vms-border);
			border-radius: 10px;
			background: #f8fafc;
			color: #334155;
			font-size: 12px;
		}

		body.self-registration-mode .desktop-id-item .bi {
			color: var(--vms-primary);
			font-size: 17px;
			flex-shrink: 0;
		}

		body.self-registration-mode .scan-sidebar.kiosk-controls {
			position: relative;
			min-width: 0;
			width: 100%;
			max-width: none;
			padding: 20px;
			border-left: 1px solid var(--vms-border);
			border-top: 0;
			background: var(--vms-surface);
			overflow: visible;
			display: block;
			gap: 0;
		}

		body.self-registration-mode .visitor-type-card {
			width: 100%;
			padding: 14px;
			display: flex;
			align-items: center;
			gap: 12px;
			border: 1px solid var(--vms-border);
			border-radius: 13px;
			background: #f8fafc;
			margin: 0;
		}

		body.self-registration-mode .visitor-icon {
			width: 43px;
			height: 43px;
			display: grid;
			place-items: center;
			flex-shrink: 0;
			border-radius: 50%;
			background: var(--vms-primary);
			color: #fff;
			font-size: 18px;
		}

		body.self-registration-mode .visitor-type-card strong {
			display: block;
			color: var(--vms-text);
			font-size: 14px;
			line-height: 1.2;
		}

		body.self-registration-mode .visitor-type-card small,
		body.self-registration-mode .kiosk-visitor-step {
			display: block;
			margin-top: 4px;
			color: var(--vms-primary);
			font-size: 10px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.04em;
		}

		body.self-registration-mode .sidebar-section {
			width: 100%;
			margin-top: 16px;
		}

		body.self-registration-mode .sidebar-section-title,
		body.self-registration-mode .kiosk-status-label {
			margin: 0 0 10px;
			color: #60718a;
			font-size: 11px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.04em;
		}

		body.self-registration-mode .camera-status-card.kiosk-status-card {
			width: 100%;
			min-height: 62px;
			padding: 13px;
			display: flex;
			align-items: flex-start;
			gap: 10px;
			border: 1px solid var(--vms-success-border);
			border-radius: 13px;
			background: var(--vms-success-light);
		}

		body.self-registration-mode .camera-status-card.kiosk-status-card.is-error {
			border-color: #fecaca;
			background: #fef2f2;
		}

		body.self-registration-mode .status-icon {
			width: 21px;
			height: 21px;
			display: grid;
			place-items: center;
			flex-shrink: 0;
			border-radius: 50%;
			background: var(--vms-success);
			color: #fff;
			font-size: 12px;
		}

		body.self-registration-mode .camera-status-card.is-error .status-icon {
			background: #dc2626;
		}

		body.self-registration-mode .camera-status-card .kiosk-status-title {
			display: block;
			color: var(--vms-text);
			font-size: 13px;
			line-height: 1.3;
			font-weight: 700;
		}

		body.self-registration-mode .camera-status-card .kiosk-status-text {
			margin: 4px 0 0;
			color: var(--vms-success-dark);
			font-size: 10px;
			line-height: 1.45;
		}

		body.self-registration-mode .camera-status-card.is-error .kiosk-status-text {
			color: #991b1b;
		}

		body.self-registration-mode .scan-actions {
			display: grid;
			gap: 11px;
		}

		body.self-registration-mode .scan-button.scan-action,
		body.self-registration-mode .upload-button.gallery-action {
			width: 100%;
			min-height: 46px;
			height: auto;
			padding: 10px 14px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			border-radius: 11px;
			font-size: 13px;
			font-weight: 750;
			flex: none;
		}

		body.self-registration-mode .scan-button.scan-action {
			border: 0;
			background: var(--vms-primary);
			color: #fff;
			box-shadow: 0 6px 14px rgba(44, 72, 170, 0.2);
		}

		body.self-registration-mode .scan-button.scan-action:hover:not(:disabled) {
			background: var(--vms-primary-dark);
			filter: none;
		}

		body.self-registration-mode .upload-button.gallery-action {
			border: 2px solid var(--vms-primary);
			background: #fff;
			color: var(--vms-primary);
		}

		body.self-registration-mode .upload-button.gallery-action:hover:not(:disabled) {
			background: var(--vms-primary-light);
		}

		body.self-registration-mode .divider.kiosk-or-divider {
			width: 100%;
			display: grid;
			grid-template-columns: 1fr auto 1fr;
			align-items: center;
			gap: 10px;
			text-align: center;
			color: #94a3b8;
			font-size: 10px;
		}

		body.self-registration-mode .divider.kiosk-or-divider::before,
		body.self-registration-mode .divider.kiosk-or-divider::after {
			display: none;
		}

		body.self-registration-mode .divider.kiosk-or-divider > span {
			height: 1px;
			background: var(--vms-border);
		}

		body.self-registration-mode .upload-hint {
			margin: 11px 0 0;
			color: var(--vms-muted);
			font-size: 11px;
			line-height: 1.5;
		}

		body.self-registration-mode .supported-list {
			margin: 0;
			padding: 0;
			display: grid;
			gap: 9px;
			list-style: none;
		}

		body.self-registration-mode .supported-list li {
			display: flex;
			align-items: flex-start;
			gap: 8px;
			color: var(--vms-text-secondary);
			font-size: 11px;
			line-height: 1.4;
		}

		body.self-registration-mode .bottom-navigation {
			margin-top: 18px;
			padding-top: 15px;
			border-top: 1px solid var(--vms-border);
		}

		body.self-registration-mode .kiosk-controls-divider {
			display: none;
		}

		body.self-registration-mode .back-link.kiosk-back-type-link {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			color: var(--vms-text-secondary);
			font-size: 11px;
			text-decoration: none;
			margin-top: 0;
		}

		body.self-registration-mode .back-link.kiosk-back-type-link:hover {
			color: var(--vms-primary);
		}

		body.self-registration-mode .vms-footer.kiosk-reg-footer {
			position: relative;
			width: 100%;
			padding: 15px;
			border-top: 1px solid var(--vms-border);
			background: #edf2f8;
			color: #879bb8;
			font-size: 11px;
			text-align: center;
			margin: 0;
		}

		/* Large laptop / Windows mid screens — keep side-by-side, tighten frame */
		@media screen and (min-width: 1101px) and (max-width: 1600px) {
			body.self-registration-mode .self-registration-header {
				grid-template-columns: minmax(180px, 1fr) auto minmax(120px, 1fr);
				gap: 12px;
			}

			body.self-registration-mode .self-reg-stepper {
				width: min(100%, 410px);
			}

			body.self-registration-mode .scan-layout.kiosk-scan-layout {
				grid-template-columns: minmax(0, 1fr) minmax(280px, 320px);
			}

			body.self-registration-mode .scanner-zone.camera-frame {
				min-height: 260px;
				max-height: 380px;
				aspect-ratio: 16 / 10;
			}

			body.self-registration-mode .id-guide {
				width: min(400px, 70%);
			}
		}

		/*
		 * Tablet + iPad (incl. iPad Pro 1024×1366 portrait).
		 * Stack scan layout so camera fills width (no tiny centered feed).
		 */
		@media screen and (min-width: 768px) and (max-width: 1100px) {
			body.self-registration-mode .self-registration-header {
				position: fixed;
				top: 0;
				left: 0;
				right: 0;
				z-index: 300;
				grid-template-columns: minmax(0, 1fr) auto;
				grid-template-rows: auto auto;
				gap: 10px 12px;
				padding: 10px 16px;
				padding-top: max(10px, env(safe-area-inset-top));
			}

			body.self-registration-mode .main {
				padding-top: calc(118px + env(safe-area-inset-top)) !important;
			}

			body.self-registration-mode.kiosk-type-select .main {
				padding-top: 0 !important;
			}

			body.self-registration-mode.kiosk-type-select .self-registration-header {
				grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
				grid-template-rows: auto;
				align-items: center;
			}

			body.self-registration-mode.kiosk-type-select .self-registration-brand {
				grid-column: 1;
				grid-row: 1;
			}

			body.self-registration-mode.kiosk-type-select .self-reg-header-step--center {
				display: block;
				grid-column: 2;
				grid-row: 1;
				justify-self: center;
				font-size: 0.9rem;
			}

			body.self-registration-mode.kiosk-type-select .self-reg-logout-btn {
				grid-column: 3;
				grid-row: 1;
				justify-self: end;
				width: auto;
				height: auto;
				min-width: 0;
				padding: 0 14px;
			}

			body.self-registration-mode.kiosk-type-select .self-reg-logout-btn span {
				display: inline;
			}

			body.self-registration-mode:not(.kiosk-type-select) .self-registration-brand {
				grid-column: 1;
				grid-row: 1;
			}

			body.self-registration-mode:not(.kiosk-type-select) .self-reg-logout-btn {
				grid-column: 2;
				grid-row: 1;
				justify-self: end;
				width: 42px;
				height: 42px;
				min-width: 42px;
				padding: 0;
			}

			body.self-registration-mode:not(.kiosk-type-select) .self-reg-logout-btn span {
				display: none;
			}

			body.self-registration-mode .self-reg-header-progress {
				grid-column: 1 / -1;
				grid-row: 2;
				width: 100%;
				max-width: none;
				justify-self: stretch;
			}

			body.self-registration-mode .self-reg-stepper {
				width: 100%;
				max-width: 520px;
				margin: 0 auto;
				grid-template-columns:
					minmax(55px, auto)
					minmax(25px, 1fr)
					minmax(70px, auto)
					minmax(25px, 1fr)
					minmax(55px, auto);
			}

			body.self-registration-mode .scan-layout.kiosk-scan-layout,
			body.self-registration-mode .kiosk-scan-layout {
				display: flex !important;
				flex-direction: column;
				grid-template-columns: none;
			}

			body.self-registration-mode .scan-main.kiosk-scan-main {
				order: 1;
				padding: 18px 20px;
				width: 100%;
			}

			body.self-registration-mode .scan-sidebar.kiosk-controls {
				order: 2;
				position: static;
				width: 100%;
				padding: 20px;
				display: grid;
				grid-template-columns: repeat(2, minmax(0, 1fr));
				align-items: start;
				gap: 16px;
				border-top: 1px solid var(--vms-border);
				border-left: 0;
			}

			body.self-registration-mode .scanner-zone.camera-frame {
				width: 100%;
				min-height: 340px;
				max-height: none;
				aspect-ratio: 4 / 3;
			}

			body.self-registration-mode .camera-section,
			body.self-registration-mode .scan-heading.kiosk-scan-heading,
			body.self-registration-mode .desktop-info-cards.kiosk-bottom-panels {
				max-width: none;
			}

			body.self-registration-mode .id-guide {
				width: min(440px, 70%);
			}

			body.self-registration-mode .desktop-info-cards.kiosk-bottom-panels {
				grid-template-columns: 1fr 1fr;
			}

			body.self-registration-mode .visitor-type-card {
				grid-column: 1 / -1;
			}

			body.self-registration-mode .camera-status-section {
				grid-column: 1;
				grid-row: 2;
				margin-top: 0;
			}

			body.self-registration-mode .action-section {
				grid-column: 2;
				grid-row: 2 / span 2;
				margin-top: 0;
			}

			body.self-registration-mode .supported-section {
				grid-column: 1;
				grid-row: 3;
				margin-top: 0;
			}

			body.self-registration-mode .bottom-navigation {
				grid-column: 1 / -1;
				margin-top: 0;
			}
		}

		/* Mobile phone */
		@media screen and (max-width: 767px) {
			body.self-registration-mode .self-registration-header {
				position: fixed;
				top: 0;
				left: 0;
				right: 0;
				z-index: 300;
				min-height: auto;
				padding:
					max(9px, env(safe-area-inset-top))
					max(12px, env(safe-area-inset-right))
					9px
					max(12px, env(safe-area-inset-left));
				grid-template-columns: minmax(0, 1fr) auto;
				grid-template-rows: auto auto;
				gap: 8px 10px;
			}

			body.self-registration-mode .main {
				padding-top: calc(108px + env(safe-area-inset-top)) !important;
			}

			body.self-registration-mode.kiosk-type-select .main {
				padding-top: 0 !important;
			}

			body.self-registration-mode.kiosk-type-select .self-registration-header {
				grid-template-rows: auto;
				align-items: center;
			}

			body.self-registration-mode .self-reg-header-step--center {
				display: none;
			}

			body.self-registration-mode .self-registration-brand {
				grid-column: 1;
				grid-row: 1;
				min-width: 0;
			}

			body.self-registration-mode .self-reg-logout-btn {
				grid-column: 2;
				grid-row: 1;
				justify-self: end;
				width: 40px;
				height: 40px;
				min-width: 40px;
				min-height: 40px;
				padding: 0;
				display: grid;
				place-items: center;
			}

			body.self-registration-mode .self-reg-logout-btn span {
				display: none;
			}

			body.self-registration-mode .self-reg-header-progress {
				grid-column: 1 / -1;
				grid-row: 2;
				width: 100%;
				max-width: none;
			}

			body.self-registration-mode .self-reg-stepper {
				width: 100%;
				grid-template-columns:
					minmax(50px, auto)
					minmax(15px, 1fr)
					minmax(70px, auto)
					minmax(15px, 1fr)
					minmax(50px, auto);
			}

			body.self-registration-mode .self-reg-step-num {
				width: 27px;
				height: 27px;
				font-size: 11px;
			}

			body.self-registration-mode .self-reg-step-label {
				max-width: 72px;
				font-size: 8px;
				white-space: normal;
			}

			body.self-registration-mode .self-reg-step-line {
				margin-top: 13px;
			}

			body.self-registration-mode .scan-layout.kiosk-scan-layout,
			body.self-registration-mode .kiosk-scan-layout {
				display: flex !important;
				flex-direction: column;
				grid-template-columns: none;
			}

			body.self-registration-mode .scan-main.kiosk-scan-main {
				order: 1;
				width: 100%;
				padding: 0;
				overflow: visible;
			}

			body.self-registration-mode .scan-heading.kiosk-scan-heading {
				margin: 0;
				padding: 18px 16px 14px;
			}

			body.self-registration-mode .scan-heading .kiosk-scan-title {
				font-size: clamp(1rem, 4.5vw, 1.15rem);
			}

			body.self-registration-mode .scan-heading .kiosk-scan-desc {
				max-width: 350px;
				margin: 7px auto 0;
				font-size: 13px;
			}

			body.self-registration-mode .camera-section {
				width: 100%;
				max-width: none;
				padding: 0 16px 16px;
				overflow: visible;
			}

			body.self-registration-mode .scanner-zone.camera-frame {
				width: 100%;
				min-height: 220px;
				max-height: none;
				aspect-ratio: 4 / 3;
				border-radius: 16px;
			}

			body.self-registration-mode .id-guide {
				width: min(74%, 290px);
			}

			body.self-registration-mode .id-guide-content {
				inset: 10px;
				gap: 8px;
			}

			body.self-registration-mode .id-lines span {
				height: 6px;
			}

			body.self-registration-mode .kiosk-camera-badge.camera-ready-badge {
				bottom: 9px;
				padding: 6px 10px;
				font-size: 9px;
			}

			body.self-registration-mode .flip-camera-btn.switch-camera-button {
				top: 9px;
				right: 9px;
				width: 34px;
				height: 34px;
			}

			body.self-registration-mode .desktop-info-cards.kiosk-bottom-panels {
				display: none !important;
			}

			body.self-registration-mode .scan-sidebar.kiosk-controls {
				order: 2;
				position: static;
				width: 100%;
				padding: 16px;
				display: block;
				border-top: 1px solid var(--vms-border);
				border-left: 0;
				overflow: visible;
			}

			body.self-registration-mode .scan-actions-row,
			body.self-registration-mode .scan-actions {
				flex-direction: column;
			}

			body.self-registration-mode .scan-action,
			body.self-registration-mode .gallery-action,
			body.self-registration-mode .scan-button {
				width: 100%;
				min-width: 0;
			}

			body.self-registration-mode .visitor-type-card {
				margin: 0 0 16px;
			}

			body.self-registration-mode .sidebar-section {
				margin-top: 16px;
			}

			body.self-registration-mode .desktop-id-grid {
				grid-template-columns: 1fr;
			}

			body.self-registration-mode .kiosk-type-grid {
				grid-template-columns: 1fr;
			}

			body.self-registration-mode .vms-footer.kiosk-reg-footer {
				padding:
					15px
					max(14px, env(safe-area-inset-right))
					calc(15px + env(safe-area-inset-bottom))
					max(14px, env(safe-area-inset-left));
			}
		}

		@media screen and (max-width: 390px) {
			body.self-registration-mode .self-registration-header {
				padding-right: max(10px, env(safe-area-inset-right));
				padding-left: max(10px, env(safe-area-inset-left));
			}

			body.self-registration-mode .self-reg-stepper {
				grid-template-columns:
					minmax(45px, auto)
					minmax(10px, 1fr)
					minmax(62px, auto)
					minmax(10px, 1fr)
					minmax(45px, auto);
			}

			body.self-registration-mode .scan-heading.kiosk-scan-heading {
				padding-right: 14px;
				padding-left: 14px;
			}

			body.self-registration-mode .scan-heading .kiosk-scan-title {
				font-size: 16px;
			}

			body.self-registration-mode .camera-section {
				padding-right: 14px;
				padding-left: 14px;
			}

			body.self-registration-mode .scanner-zone.camera-frame {
				min-height: 190px;
			}

			body.self-registration-mode .scan-sidebar.kiosk-controls {
				padding: 14px;
			}
		}

		@media screen and (max-height: 500px) and (orientation: landscape) {
			body.self-registration-mode .scanner-zone.camera-frame {
				min-height: 200px;
				aspect-ratio: 16 / 9;
			}

			body.self-registration-mode .self-registration-header {
				min-height: auto;
				padding-top: 6px;
				padding-bottom: 6px;
			}
		}

		/* Guard (non-kiosk) register flow on small screens */
		@media (max-width: 991.98px) {
			body:not(.self-registration-mode) .main {
				padding: 16px max(14px, env(safe-area-inset-right)) 20px max(14px, env(safe-area-inset-left));
			}

			.register-flow {
				max-width: 100%;
			}

			.scanner-card {
				padding: 12px;
			}

			.scanner-zone {
				padding: 28px 12px;
				min-height: 240px;
			}
		}

		.leave-register-toast.swal2-popup {
			width: auto !important;
			max-width: min(560px, calc(100vw - 24px));
			margin-top: 16px !important;
			padding: 14px 16px 12px !important;
			border-radius: 12px !important;
			box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15) !important;
		}

		.leave-register-toast .swal2-icon {
			width: 2em;
			height: 2em;
			margin: 0 10px 0 0 !important;
			border-width: 2px;
		}

		.leave-register-toast-title {
			font-size: 15px !important;
			font-weight: 700 !important;
			color: #111827 !important;
			margin: 0 !important;
			text-align: left !important;
		}

		.leave-register-toast-text {
			font-size: 13px !important;
			color: #4b5563 !important;
			margin: 4px 0 0 !important;
			text-align: left !important;
		}

		.leave-register-toast-actions {
			margin: 10px 0 0 !important;
			width: 100%;
			justify-content: flex-end !important;
			gap: 8px !important;
		}

		.leave-register-toast-btn {
			border: 0;
			border-radius: 8px;
			padding: 7px 14px;
			font-size: 13px;
			font-weight: 600;
			cursor: pointer;
		}

		.leave-register-toast-btn-stay {
			background: #f3f4f6;
			color: #374151;
		}

		.leave-register-toast-btn-leave {
			background: #3e4ba0;
			color: #fff;
		}

		.qr-print-holder {
			position: fixed;
			left: -4000px;
			top: 0;
			width: 1px;
			height: 1px;
			overflow: hidden;
		}

		.progress-track span.is-complete-66 {
			width: 66%;
		}

		.visitor-input-group.span-all {
			grid-column: 1 / -1;
		}

		.visitor-input-group.spaced-top {
			margin-top: 17px;
		}
	</style>
</head>
@php
	$guardSidebarUser = auth()->user();
	$guardSidebarName = trim(((string) ($guardSidebarUser->first_name ?? '')).' '.((string) ($guardSidebarUser->last_name ?? '')));
	$guardSidebarName = $guardSidebarName !== '' ? $guardSidebarName : ((string) ($guardSidebarUser->name ?? $guardSidebarUser->email ?? 'Guard Officer'));
	$isSelfRegisteredRole = (int) optional($guardSidebarUser)->role_id === 4;
	$registerTypeInput = request('type');
	$registerType = in_array($registerTypeInput, ['normal', 'contractor', 'enrollee'], true)
		? $registerTypeInput
		: ($isSelfRegisteredRole ? null : 'normal');
	$showKioskTypePicker = $isSelfRegisteredRole && $registerType === null;
	$registerTypeLabels = [
		'normal' => 'Normal Visitor',
		'enrollee' => 'Enrollee',
		'contractor' => 'Contractor',
	];
	$guardOnDutyState = ['has_active_guard' => false, 'shift' => null];
	if ($isSelfRegisteredRole) {
		$guardOnDutyState = app(\App\Services\GuardDutyService::class)
			->payloadForKiosk((int) $guardSidebarUser->user_id);
	}
	$hasActiveGuard = (bool) ($guardOnDutyState['has_active_guard'] ?? false);
	$activeGuardShift = $guardOnDutyState['shift'] ?? null;
	$activeGuardName = trim((string) data_get($activeGuardShift, 'guard.name', ''));
	$activeGuardBadge = trim((string) data_get($activeGuardShift, 'guard.badge_number', ''));
	$activeGuardStation = trim((string) data_get($activeGuardShift, 'guard.station', ''));
	$activeGuardSince = null;
	if (! empty($activeGuardShift['clock_in_at'])) {
		try {
			$activeGuardSince = \Carbon\Carbon::parse($activeGuardShift['clock_in_at'], 'Asia/Manila')->format('g:i A');
		} catch (\Throwable $e) {
			$activeGuardSince = null;
		}
	}
@endphp
<body @class([
	'self-registration-mode' => $isSelfRegisteredRole,
	'kiosk-type-select' => $showKioskTypePicker,
])>
	<div class="layout">
		@if (! $isSelfRegisteredRole)
			<div class="guard-nav-backdrop" id="guardNavBackdrop" aria-hidden="true"></div>
		@endif
		@if (! $isSelfRegisteredRole)
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
		@endif

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
					<div class="confirmation-summary-row is-hidden" id="existingVisitorModalProgressRow">
						<span class="confirmation-summary-label">Enrollment Progress</span>
						<span class="confirmation-summary-value" id="existingVisitorModalProgress">-</span>
					</div>
				</div>
			</div>
			<div class="confirmation-modal-footer">
				<button type="button" class="confirmation-modal-btn secondary" id="existingVisitorModalCancel">Cancel (New Visitor)</button>
				<button type="button" class="confirmation-modal-btn primary" id="existingVisitorModalConfirm">Yes, Continue</button>
			</div>
		</div>
	</div>

	@if ($showKioskTypePicker)
	<div class="confirmation-modal is-hidden" id="guardDutyModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="guardDutyModalTitle">
		<div class="confirmation-modal-card">
			<form id="guardDutyForm" autocomplete="off">
				<div class="confirmation-modal-header">
					<h2 class="confirmation-modal-title" id="guardDutyModalTitle">Set Guard on Duty</h2>
					<p class="confirmation-modal-subtitle" id="guardDutyModalSubtitle">Sign in with an active guard account to assign the officer on duty.</p>
				</div>
				<div class="confirmation-modal-body">
					<div class="guard-duty-current is-hidden" id="guardDutyCurrentWrap">
						<span>Currently On Duty:</span>
						<strong id="guardDutyCurrentName">—</strong>
						<small id="guardDutyCurrentSince">Since —</small>
					</div>
					<div class="guard-duty-field">
						<label for="guardDutyEmail" id="guardDutyEmailLabel">Email / Guard Account</label>
						<input id="guardDutyEmail" name="email" type="email" required maxlength="255" autocomplete="username">
					</div>
					<div class="guard-duty-field">
						<label for="guardDutyPassword">Password</label>
						<input id="guardDutyPassword" name="password" type="password" required maxlength="255" autocomplete="current-password">
					</div>
					<p class="guard-duty-error" id="guardDutyError" role="alert"></p>
				</div>
				<div class="confirmation-modal-footer">
					<button type="button" class="confirmation-modal-btn secondary" id="guardDutyCancelBtn">Cancel</button>
					<button type="submit" class="confirmation-modal-btn primary" id="guardDutySubmitBtn">Confirm</button>
				</div>
			</form>
		</div>
	</div>
	@endif

	@if ($isSelfRegisteredRole)
	<div class="confirmation-modal is-hidden" id="endDutyModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="endDutyModalTitle">
		<div class="confirmation-modal-card">
			<form id="endDutyForm" autocomplete="off">
				<div class="confirmation-modal-header">
					<h2 class="confirmation-modal-title" id="endDutyModalTitle">End Guard Duty</h2>
					<p class="confirmation-modal-subtitle">The current guard must confirm their password before this shift can be closed.</p>
				</div>
				<div class="confirmation-modal-body">
					<div class="end-duty-profile" id="endDutyProfile">
						<strong id="endDutyGuardName">—</strong>
						<span id="endDutyGuardBadge">Badge No. —</span>
						<span id="endDutyGuardStation">—</span>
						<div class="end-duty-started">
							<span>Duty Started:</span>
							<strong id="endDutyStartedAt">—</strong>
						</div>
					</div>
					<p class="end-duty-warning">
						Are you sure you want to end this duty?<br>
						Visitor self-registration will be temporarily disabled until another guard is assigned.
					</p>
					<div class="guard-duty-field">
						<label for="endDutyPassword">Password</label>
						<input id="endDutyPassword" name="password" type="password" required maxlength="255" autocomplete="current-password">
					</div>
					<p class="guard-duty-error" id="endDutyError" role="alert"></p>
				</div>
				<div class="confirmation-modal-footer">
					<button type="button" class="confirmation-modal-btn secondary" id="endDutyCancelBtn">Cancel</button>
					<button type="submit" class="confirmation-modal-btn danger" id="endDutySubmitBtn">Confirm End Duty</button>
				</div>
			</form>
		</div>
	</div>

	<div class="confirmation-modal is-hidden" id="logoutDutyModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="logoutDutyModalTitle">
		<div class="confirmation-modal-card">
			<div class="confirmation-modal-header">
				<h2 class="confirmation-modal-title" id="logoutDutyModalTitle">You are currently marked as the Guard on Duty.</h2>
				<p class="confirmation-modal-subtitle">What would you like to do?</p>
			</div>
			<div class="confirmation-modal-body">
				<p class="end-duty-warning">
					Logout Only keeps the current shift active. End Duty &amp; Logout closes the shift and then signs this kiosk out.
				</p>
			</div>
			<div class="confirmation-modal-footer is-choice">
				<button type="button" class="confirmation-modal-btn secondary" id="logoutDutyCancelBtn">Cancel</button>
				<button type="button" class="confirmation-modal-btn primary" id="logoutDutyOnlyBtn">Logout Only</button>
				<button type="button" class="confirmation-modal-btn danger" id="logoutDutyEndBtn">End Duty &amp; Logout</button>
			</div>
		</div>
	</div>
	@endif



		<main class="main">
			@if ($isSelfRegisteredRole)
				<header class="self-registration-header">
					<div class="self-registration-brand">
						<div class="brand-icon">
							<i class="bi bi-shield-lock-fill"></i>
						</div>
						<div>
							<h4 class="brand-title mb-0"><span>VMS</span></h4>
							<small class="brand-subtitle">Visitor Monitoring System</small>
						</div>
					</div>
					@if ($showKioskTypePicker)
						<p class="self-reg-header-step self-reg-header-step--center">Select Visitor Type</p>
					@else
					<div class="self-reg-header-progress" id="selfRegProgress" aria-label="Registration progress">
						<div class="self-reg-stepper">
							<div class="self-reg-step is-active" data-step="1">
								<span class="self-reg-step-num">1</span>
								<span class="self-reg-step-label">ID Scan</span>
							</div>
							<span class="self-reg-step-line" data-after="1"></span>
							<div class="self-reg-step" data-step="2">
								<span class="self-reg-step-num">2</span>
								<span class="self-reg-step-label">Verify Details</span>
							</div>
							<span class="self-reg-step-line" data-after="2"></span>
							<div class="self-reg-step" data-step="3">
								<span class="self-reg-step-num">3</span>
								<span class="self-reg-step-label">Complete</span>
							</div>
						</div>
					</div>
					@endif
					<a href="{{ route('logout') }}"
					   class="self-reg-logout-btn"
					   id="selfRegLogoutBtn">
						<i class="bi bi-box-arrow-right"></i>
						<span>Logout</span>
					</a>
					<form id="self-reg-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
						@csrf
					</form>
				</header>
			@else
				@include('guard.partials.guard-topbar', ['title' => 'Register Visitor'])
			@endif
			@if ($isSelfRegisteredRole)
				<div class="self-registration-body">
			@endif
			@if ($showKioskTypePicker)
				<section class="kiosk-type-picker" aria-label="Choose visitor type">
					<div class="kiosk-type-picker-inner">
						<h2 class="kiosk-type-picker-title">Welcome to NU Secure</h2>
						<p class="kiosk-type-picker-subtitle">Tap the option that best describes your visit to begin registration.</p>
						<div class="kiosk-guard-duty" id="kioskGuardDuty" data-has-active="{{ $hasActiveGuard ? '1' : '0' }}">
							<div class="kiosk-guard-duty-card kiosk-guard-duty-empty {{ $hasActiveGuard ? 'is-hidden' : '' }}" id="kioskGuardDutyEmpty">
								<div class="kiosk-guard-duty-icon warn" aria-hidden="true">
									<i class="bi bi-shield-exclamation"></i>
								</div>
								<div class="kiosk-guard-duty-copy">
									<p class="kiosk-guard-duty-label">Guard on Duty</p>
									<h3 class="kiosk-guard-duty-name">No Guard Currently Assigned</h3>
									<p>Visitor self-registration is temporarily unavailable until a security guard is assigned.</p>
								</div>
								<div class="kiosk-guard-duty-side">
									<button type="button" class="kiosk-guard-duty-btn" id="kioskSetGuardBtn">Set Guard on Duty</button>
								</div>
							</div>
							<div class="kiosk-guard-duty-card kiosk-guard-duty-active {{ $hasActiveGuard ? '' : 'is-hidden' }}" id="kioskGuardDutyActive">
								<div class="kiosk-guard-duty-icon" aria-hidden="true">
									<i class="bi bi-shield-check"></i>
								</div>
								<div class="kiosk-guard-duty-copy">
									<p class="kiosk-guard-duty-label">Guard on Duty</p>
									<h3 class="kiosk-guard-duty-name" id="kioskGuardDutyName">{{ $activeGuardName !== '' ? $activeGuardName : 'Security Guard' }}</h3>
									<p class="kiosk-guard-duty-meta" id="kioskGuardDutyMeta">
										@if ($hasActiveGuard)
											Security Guard{{ $activeGuardBadge !== '' ? ' • Badge '.$activeGuardBadge : '' }}
										@endif
									</p>
									<p class="kiosk-guard-duty-meta" id="kioskGuardDutyStation">
										@if ($hasActiveGuard)
											{{ $activeGuardStation !== '' ? $activeGuardStation : 'Self-Registration' }}
										@endif
									</p>
								</div>
								<div class="kiosk-guard-duty-side">
									<span class="kiosk-guard-duty-status">ON DUTY</span>
									<p class="kiosk-guard-duty-since" id="kioskGuardDutySince">{{ $hasActiveGuard && $activeGuardSince ? 'Since '.$activeGuardSince : '' }}</p>
									<div class="kiosk-guard-duty-actions">
										<button type="button" class="kiosk-guard-duty-btn secondary" id="kioskChangeGuardBtn">Change Guard</button>
										<button type="button" class="kiosk-guard-duty-btn danger" id="kioskEndDutyBtn">End Duty</button>
									</div>
								</div>
							</div>
						</div>
						<div class="kiosk-type-grid {{ $hasActiveGuard ? '' : 'is-locked' }}" id="kioskTypeGrid">
							<a href="{{ $hasActiveGuard ? '/guard/register?type=normal' : '#' }}" class="kiosk-type-card js-privacy-consent-link {{ $hasActiveGuard ? '' : 'is-disabled' }}" data-type="normal" data-href="/guard/register?type=normal" @if (! $hasActiveGuard) aria-disabled="true" tabindex="-1" @endif>
								<span class="kiosk-type-icon"><i class="bi bi-person-fill"></i></span>
								<span class="kiosk-type-name">Normal Visitor</span>
								<span class="kiosk-type-desc">Guests visiting offices, staff, or departments on campus.</span>
							</a>
							<a href="{{ $hasActiveGuard ? '/guard/register?type=enrollee' : '#' }}" class="kiosk-type-card js-privacy-consent-link {{ $hasActiveGuard ? '' : 'is-disabled' }}" data-type="enrollee" data-href="/guard/register?type=enrollee" @if (! $hasActiveGuard) aria-disabled="true" tabindex="-1" @endif>
								<span class="kiosk-type-icon"><i class="bi bi-mortarboard-fill"></i></span>
								<span class="kiosk-type-name">Enrollee</span>
								<span class="kiosk-type-desc">Prospective or incoming students for enrollment-related visits.</span>
							</a>
							<a href="{{ $hasActiveGuard ? '/guard/register?type=contractor' : '#' }}" class="kiosk-type-card js-privacy-consent-link {{ $hasActiveGuard ? '' : 'is-disabled' }}" data-type="contractor" data-href="/guard/register?type=contractor" @if (! $hasActiveGuard) aria-disabled="true" tabindex="-1" @endif>
								<span class="kiosk-type-icon"><i class="bi bi-briefcase-fill"></i></span>
								<span class="kiosk-type-name">Contractor</span>
								<span class="kiosk-type-desc">Vendors and service providers with scheduled work on site.</span>
							</a>
						</div>
					</div>
				</section>
			@elseif ($registerType !== null)
				@if ($isSelfRegisteredRole)
					<h1 class="page-title">Register Visitor</h1>
					<p class="self-reg-subtitle">Follow the steps below to complete your visitor check-in.</p>
				@endif
				<section class="register-flow">
					<div class="flow-head">
						<div class="flow-step-meta">
							<p class="flow-step-name" id="flowStepName">ID Scan</p>
							<p class="flow-step-count" id="flowStepCount">Step 1 of 3</p>
						</div>
					</div>

					<div class="scanner-card">
						@if ($isSelfRegisteredRole)
						<div class="kiosk-scan-layout scan-layout">
							<section class="kiosk-scan-main scan-main">
								<div class="kiosk-scan-heading scan-heading">
									<h2 class="kiosk-scan-title" id="kioskScanTitle">
										<i class="bi bi-camera-video-fill"></i>
										<span id="kioskScanTitleText">Scan Your Identification Card</span>
									</h2>
									<p class="kiosk-scan-desc" id="kioskScanDesc">Position your ID card within the frame. Ensure all details are clear and visible.</p>
								</div>

								<div class="camera-section">
									<div class="scanner-zone camera-frame">
										<video id="cameraFeed" class="camera-feed camera-video" autoplay playsinline muted></video>
										<canvas id="frozenFrame" class="frozen-frame camera-canvas"></canvas>
										<button type="button" class="flip-camera-btn switch-camera-button" id="flipCameraBtn" aria-label="Flip camera" title="Flip camera">
											<i class="bi bi-phone-flip" aria-hidden="true"></i>
										</button>
										<div class="scanner-overlay camera-overlay" aria-hidden="true">
											<div class="picture-guide is-hidden" id="pictureGuide">
												<span class="corner tl"></span>
												<span class="corner tr"></span>
												<span class="corner bl"></span>
												<span class="corner br"></span>
												<span class="face-oval"></span>
												<span class="shoulder-line"></span>
												<span class="id-holder"></span>
											</div>
											<div class="id-guide" id="idGuide">
												<div class="id-guide-content">
													<div class="id-photo-placeholder"></div>
													<div class="id-lines">
														<span></span>
														<span></span>
														<span></span>
														<span></span>
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
										<div class="kiosk-camera-badge camera-ready-badge" id="kioskCameraBadge">
											<span class="badge-dot camera-ready-dot"></span>
											<span id="kioskCameraBadgeText">Starting camera...</span>
										</div>
									</div>
								</div>

								<div class="desktop-info-cards kiosk-bottom-panels" id="kioskBottomPanels">
									<section class="info-card kiosk-info-card">
										<div class="info-card-title kiosk-info-card-title">
											<i class="bi bi-lightbulb-fill"></i>
											<h2>Scanning Tips</h2>
										</div>
										<ul class="tips-list kiosk-tips-list">
											<li><span class="tip-check">✓</span> Place the ID on a flat and well-lit surface.</li>
											<li><span class="tip-check">✓</span> Avoid glare and shadows on the identification card.</li>
											<li><span class="tip-check">✓</span> Keep all four corners visible inside the frame.</li>
											<li><span class="tip-check">✓</span> Hold the camera steady before capturing.</li>
										</ul>
									</section>
									<section class="info-card kiosk-info-card">
										<div class="info-card-title kiosk-info-card-title">
											<i class="bi bi-credit-card-2-front-fill"></i>
											<h2>Supported ID Types</h2>
										</div>
										<div class="desktop-id-grid kiosk-id-chip-grid">
											<div class="desktop-id-item kiosk-id-chip"><i class="bi bi-person-vcard-fill"></i> National ID</div>
											<div class="desktop-id-item kiosk-id-chip"><i class="bi bi-credit-card-2-front-fill"></i> UMID</div>
											<div class="desktop-id-item kiosk-id-chip"><i class="bi bi-check2-square"></i> Voters ID</div>
											<div class="desktop-id-item kiosk-id-chip"><i class="bi bi-car-front-fill"></i> Drivers License</div>
											<div class="desktop-id-item kiosk-id-chip"><i class="bi bi-person-hearts"></i> Senior ID</div>
										</div>
									</section>
								</div>
							</section>

							<aside class="kiosk-controls scan-sidebar">
								<section class="visitor-type-card kiosk-visitor-badge">
									<span class="visitor-icon kiosk-visitor-avatar">
										<i class="bi bi-{{ $registerType === 'enrollee' ? 'mortarboard-fill' : ($registerType === 'contractor' ? 'briefcase-fill' : 'person-fill') }}"></i>
									</span>
									<div>
										<strong class="kiosk-visitor-name">{{ $registerTypeLabels[$registerType] ?? 'Visitor' }}</strong>
										<small class="kiosk-visitor-step" id="kioskVisitorStep">Step 1 of 3</small>
									</div>
								</section>

								<section class="sidebar-section camera-status-section">
									<h2 class="sidebar-section-title kiosk-status-label">Camera Status</h2>
									<div class="camera-status-card kiosk-status-card" id="kioskCameraStatusCard">
										<span class="status-icon"><i class="bi bi-check-circle-fill"></i></span>
										<div>
											<strong class="kiosk-status-title" id="kioskCameraStatusTitle">Starting Camera</strong>
											<p class="kiosk-status-text" id="kioskCameraStatusText">Please allow camera access when requested.</p>
										</div>
									</div>
									<p class="camera-status is-hidden" id="cameraStatus">Starting camera...</p>
									<canvas id="captureCanvas" class="is-hidden"></canvas>
								</section>

								<section class="sidebar-section action-section">
									<div class="scan-actions scan-actions-row">
										<button type="button" class="scan-button scan-action" id="scanAction">
											<i class="bi bi-arrow-clockwise"></i>
											<span id="scanActionText">Scan ID Card</span>
										</button>
										<div class="divider kiosk-or-divider">
											<span></span>
											<small>OR</small>
											<span></span>
										</div>
										<button type="button" class="upload-button gallery-action is-hidden" id="galleryAction">
											<i class="bi bi-cloud-upload"></i>
											Upload ID Image
										</button>
									</div>
									<input type="file" id="idGalleryInput" class="is-hidden" accept="image/jpeg,image/png,image/webp,image/*">
									<p class="upload-hint gallery-hint is-hidden" id="galleryHint">If the camera is unavailable, upload a clear photo of your ID.</p>
								</section>

								<section class="sidebar-section supported-section id-types is-hidden" id="idTypesPanel">
									<h2 class="sidebar-section-title id-types-title">Supported ID Types</h2>
									<ul class="supported-list kiosk-supported-list">
										<li><span class="supported-check">✓</span> National ID</li>
										<li><span class="supported-check">✓</span> UMID</li>
										<li><span class="supported-check">✓</span> Voters ID</li>
										<li><span class="supported-check">✓</span> Drivers License</li>
										<li><span class="supported-check">✓</span> Senior ID</li>
									</ul>
								</section>

								<div class="bottom-navigation">
									<div class="kiosk-controls-divider"></div>
									<a href="/guard/register" class="back-link kiosk-back-type-link js-kiosk-type-link">
										<span>←</span>
										<span>All visitor types</span>
									</a>
								</div>
							</aside>
						</div>
						@else
						<div class="scanner-zone">
							<video id="cameraFeed" class="camera-feed" autoplay playsinline muted></video>
							<canvas id="frozenFrame" class="frozen-frame"></canvas>
							<button type="button" class="flip-camera-btn" id="flipCameraBtn" aria-label="Flip camera" title="Flip camera">
								<i class="bi bi-phone-flip" aria-hidden="true"></i>
							</button>
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
						<canvas id="captureCanvas" class="is-hidden"></canvas>
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
								<li>- National ID</li>
								<li>- UMID</li>
								<li>- Voters ID</li>
								<li>- Drivers License</li>
								<li>- Senior ID</li>
							</ul>
						</div>
						@endif
					</div>
					@if ($isSelfRegisteredRole)
					<footer class="kiosk-reg-footer vms-footer">© {{ date('Y') }} VMS. All rights reserved.</footer>
					@endif

					@if ($registerType === 'enrollee')
						<div class="visitor-step is-hidden" id="enrolleeStepPanel">
							@if ($isSelfRegisteredRole)
							<div class="kiosk-mobile-progress" aria-label="Registration progress">
								<div class="kiosk-mobile-progress-info">
									<span>Step 2 of 3</span>
									<strong>66% Complete</strong>
								</div>
								<div class="progress-track"><span class="is-complete-66"></span></div>
							</div>
							<div class="kiosk-verify-layout">
								<div class="kiosk-verify-main">
									<div class="kiosk-verify-card">
										<header class="kiosk-verify-head">
											<div class="kiosk-verify-head-icon" aria-hidden="true">
												<i class="bi bi-people-fill"></i>
											</div>
											<div>
												<h2>Verify Your Information</h2>
												<p>Please review and confirm that all information is correct.</p>
											</div>
										</header>

										<div class="kiosk-verify-form-body">
										<section class="kiosk-form-section">
											<h3 class="kiosk-form-section-title"><i class="bi bi-person-fill"></i> Personal Information</h3>
											<div class="kiosk-form-grid cols-2">
												<div class="visitor-input-group">
													<label class="kiosk-field-label" for="visitorFirstName">First Name <span class="required-mark">*</span></label>
													<div class="kiosk-input-wrap">
														<input class="visitor-input" id="visitorFirstName" name="first_name" type="text" placeholder="Enter first name" autocomplete="given-name" required>
													</div>
												</div>
												<div class="visitor-input-group">
													<label class="kiosk-field-label" for="visitorLastName">Last Name <span class="required-mark">*</span></label>
													<div class="kiosk-input-wrap">
														<input class="visitor-input" id="visitorLastName" name="last_name" type="text" placeholder="Enter last name" autocomplete="family-name" required>
													</div>
												</div>
												<div class="visitor-input-group span-all">
													<label class="kiosk-field-label" for="visitorBirthday">Birthday <span class="required-mark">*</span></label>
													<div class="kiosk-input-wrap">
														<input class="visitor-input" id="visitorBirthday" name="birthday" type="date" autocomplete="bday" max="{{ now()->subDay()->format('Y-m-d') }}" required>
													</div>
												</div>
											</div>
										</section>

										<section class="kiosk-form-section">
											<h3 class="kiosk-form-section-title"><i class="bi bi-telephone-fill"></i> Contact Information</h3>
											<div class="visitor-input-group">
												<label class="kiosk-field-label" for="visitorPhoneNumber">Phone Number <span class="required-mark">*</span></label>
												<div class="kiosk-phone-prefix">
													<span class="input-prefix">+63</span>
													<input class="visitor-input" id="visitorPhoneNumber" name="phone_number" type="tel" placeholder="9XXXXXXXXX" inputmode="numeric" maxlength="10" pattern="9[0-9]{9}" autocomplete="tel" required>
												</div>
												<span class="kiosk-field-hint">Enter the 10-digit number without the leading zero.</span>
											</div>
										</section>

										<section class="kiosk-form-section">
											<h3 class="kiosk-form-section-title"><i class="bi bi-geo-alt-fill"></i> Address</h3>
											<div class="kiosk-form-grid cols-2">
												<div class="visitor-input-group">
													<label class="kiosk-field-label" for="visitorHouseNo">House No.</label>
													<div class="kiosk-input-wrap">
														<input class="visitor-input" id="visitorHouseNo" name="house_no" type="text" placeholder="Enter house number" autocomplete="address-line1">
													</div>
												</div>
												<div class="visitor-input-group">
													<label class="kiosk-field-label" for="visitorStreet">Street</label>
													<div class="kiosk-input-wrap">
														<input class="visitor-input" id="visitorStreet" name="street" type="text" placeholder="Enter street" autocomplete="address-line2">
													</div>
												</div>
												<div class="visitor-input-group">
													<label class="kiosk-field-label" for="visitorBarangay">Barangay <span class="required-mark">*</span></label>
													<div class="kiosk-input-wrap">
														<input class="visitor-input" id="visitorBarangay" name="barangay" type="text" placeholder="Enter barangay" required>
													</div>
												</div>
												<div class="visitor-input-group">
													<label class="kiosk-field-label" for="visitorCity">City / Municipality <span class="required-mark">*</span></label>
													<div class="kiosk-input-wrap">
														<input class="visitor-input" id="visitorCity" name="city_municipality" type="text" placeholder="Enter city or municipality" autocomplete="address-level2" required>
													</div>
												</div>
												<div class="visitor-input-group">
													<label class="kiosk-field-label" for="visitorProvince">Province <span class="required-mark">*</span></label>
													<div class="kiosk-input-wrap">
														<input class="visitor-input" id="visitorProvince" name="province" type="text" placeholder="Enter province" autocomplete="address-level1" required>
													</div>
												</div>
												<div class="visitor-input-group">
													<label class="kiosk-field-label" for="visitorRegion">Region</label>
													<div class="kiosk-input-wrap">
														<input class="visitor-input" id="visitorRegion" name="region" type="text" placeholder="Enter region" readonly>
													</div>
												</div>
											</div>
										</section>

										<section class="kiosk-form-section">
											<h3 class="kiosk-form-section-title"><i class="bi bi-person-vcard-fill"></i> ID Pass Number <span class="required-mark">*</span></h3>
											<div class="visitor-input-group">
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="visitorIdPassNumber" name="id_pass_number" type="text" placeholder="Enter ID pass number" required>
												</div>
											</div>
										</section>

										<input id="visitorControlNumber" name="control_number" type="hidden">

										<div class="kiosk-verify-actions desktop-actions">
											<button type="button" class="kiosk-verify-back" id="kioskVerifyBackBtn">
												<i class="bi bi-chevron-left"></i>
												<span>Back</span>
											</button>
											<button type="button" class="kiosk-verify-proceed" id="generateQrBtn">
												<span class="submit-text">Confirm and Continue</span>
												<i class="bi bi-chevron-right submit-arrow"></i>
											</button>
										</div>
										</div>
									</div>
								</div>

								<aside class="kiosk-verify-sidebar">
									<div class="kiosk-summary-card">
										<h3 class="kiosk-summary-card-title"><i class="bi bi-person-fill"></i> Visitor Summary</h3>
										<span class="kiosk-summary-badge">{{ $registerTypeLabels[$registerType] ?? 'Visitor' }}</span>
										<div class="kiosk-summary-progress-label">
											<span>Registration Progress</span>
											<strong class="kiosk-summary-progress-value">66%</strong>
										</div>
										<div class="kiosk-summary-progress-track">
											<div class="kiosk-summary-progress-fill"></div>
										</div>
										<div class="kiosk-id-scan-status">
											<i class="bi bi-check-lg" aria-hidden="true"></i>
											<div>
												<strong>ID Scan Completed</strong>
												<span>Completed</span>
												<small>ID information was captured successfully.</small>
											</div>
										</div>
									</div>
									<div class="kiosk-verify-tips-card">
										<h3 class="kiosk-verify-tips-title"><i class="bi bi-lightbulb-fill"></i> Before Continuing</h3>
										<ul class="kiosk-verify-tips-list">
											<li><i class="bi bi-check-lg" aria-hidden="true"></i> Review all entered information.</li>
											<li><i class="bi bi-check-lg" aria-hidden="true"></i> Make sure required fields are complete.</li>
											<li><i class="bi bi-check-lg" aria-hidden="true"></i> You can go back if you need to edit.</li>
										</ul>
									</div>
								</aside>
							</div>
							<div class="kiosk-mobile-action-bar">
								<button type="button" class="mobile-back-button" id="kioskVerifyBackBtnMobile">
									<i class="bi bi-chevron-left" aria-hidden="true"></i>
									<span>Back</span>
								</button>
								<button type="button" class="mobile-continue-button" id="generateQrBtnMobile">
									<span>Continue</span>
									<i class="bi bi-chevron-right" aria-hidden="true"></i>
								</button>
							</div>
							@else
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
										<label class="visitor-label" for="visitorHouseNo">House No.</label>
										<input class="visitor-input" id="visitorHouseNo" name="house_no" type="text" placeholder="House no.">
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorStreet">Street</label>
										<input class="visitor-input" id="visitorStreet" name="street" type="text" placeholder="Street">
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorBarangay">Barangay</label>
										<input class="visitor-input" id="visitorBarangay" name="barangay" type="text" placeholder="Barangay">
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorCity">City / Municipality</label>
										<input class="visitor-input" id="visitorCity" name="city_municipality" type="text" placeholder="City / municipality">
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorProvince">Province</label>
										<input class="visitor-input" id="visitorProvince" name="province" type="text" placeholder="Province">
									</div>
									<div class="visitor-input-group">
										<label class="visitor-label" for="visitorRegion">Region</label>
										<input class="visitor-input" id="visitorRegion" name="region" type="text" placeholder="Region" readonly>
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

							<input id="visitorControlNumber" name="control_number" type="hidden">

							<button type="button" class="visitor-submit" id="generateQrBtn">Proceed to Face + ID Capture</button>
							@endif
						</div>
					@else
					<div class="visitor-step is-hidden" id="visitorStepPanel">
						@if ($isSelfRegisteredRole)
						<div class="kiosk-mobile-progress" aria-label="Registration progress">
							<div class="kiosk-mobile-progress-info">
								<span>Step 2 of 3</span>
								<strong>66% Complete</strong>
							</div>
							<div class="progress-track"><span class="is-complete-66"></span></div>
						</div>
						<div class="kiosk-verify-layout">
							<div class="kiosk-verify-main">
								<div class="kiosk-verify-card">
									<header class="kiosk-verify-head">
										<div class="kiosk-verify-head-icon" aria-hidden="true">
											<i class="bi bi-people-fill"></i>
										</div>
										<div>
											<h2>Verify Your Information</h2>
											<p>Please review and confirm that all information is correct.</p>
										</div>
									</header>

									<div class="kiosk-verify-form-body">
									<section class="kiosk-form-section">
										<h3 class="kiosk-form-section-title"><i class="bi bi-person-fill"></i> Personal Information</h3>
										<div class="kiosk-form-grid cols-2">
											<div class="visitor-input-group">
												<label class="kiosk-field-label" for="visitorFirstName">First Name <span class="required-mark">*</span></label>
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="visitorFirstName" name="first_name" type="text" placeholder="Enter first name" autocomplete="given-name" required>
												</div>
											</div>
											<div class="visitor-input-group">
												<label class="kiosk-field-label" for="visitorLastName">Last Name <span class="required-mark">*</span></label>
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="visitorLastName" name="last_name" type="text" placeholder="Enter last name" autocomplete="family-name" required>
												</div>
											</div>
											<div class="visitor-input-group span-all">
												<label class="kiosk-field-label" for="visitorBirthday">Birthday <span class="required-mark">*</span></label>
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="visitorBirthday" name="birthday" type="date" autocomplete="bday" max="{{ now()->subDay()->format('Y-m-d') }}" required>
												</div>
											</div>
										</div>
									</section>

									<section class="kiosk-form-section">
										<h3 class="kiosk-form-section-title"><i class="bi bi-telephone-fill"></i> Contact Information</h3>
										<div class="visitor-input-group">
											<label class="kiosk-field-label" for="visitorPhoneNumber">Phone Number <span class="required-mark">*</span></label>
											<div class="kiosk-phone-prefix">
												<span class="input-prefix">+63</span>
												<input class="visitor-input" id="visitorPhoneNumber" name="phone_number" type="tel" placeholder="9XXXXXXXXX" inputmode="numeric" maxlength="10" pattern="9[0-9]{9}" autocomplete="tel" required>
											</div>
											<span class="kiosk-field-hint">Enter the 10-digit number without the leading zero.</span>
										</div>
									</section>

									<section class="kiosk-form-section">
										<h3 class="kiosk-form-section-title"><i class="bi bi-geo-alt-fill"></i> Address</h3>
										<div class="kiosk-form-grid cols-2">
											<div class="visitor-input-group">
												<label class="kiosk-field-label" for="visitorHouseNo">House No.</label>
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="visitorHouseNo" name="house_no" type="text" placeholder="Enter house number" autocomplete="address-line1">
												</div>
											</div>
											<div class="visitor-input-group">
												<label class="kiosk-field-label" for="visitorStreet">Street</label>
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="visitorStreet" name="street" type="text" placeholder="Enter street" autocomplete="address-line2">
												</div>
											</div>
											<div class="visitor-input-group">
												<label class="kiosk-field-label" for="visitorBarangay">Barangay <span class="required-mark">*</span></label>
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="visitorBarangay" name="barangay" type="text" placeholder="Enter barangay" required>
												</div>
											</div>
											<div class="visitor-input-group">
												<label class="kiosk-field-label" for="visitorCity">City / Municipality <span class="required-mark">*</span></label>
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="visitorCity" name="city_municipality" type="text" placeholder="Enter city or municipality" autocomplete="address-level2" required>
												</div>
											</div>
											<div class="visitor-input-group">
												<label class="kiosk-field-label" for="visitorProvince">Province <span class="required-mark">*</span></label>
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="visitorProvince" name="province" type="text" placeholder="Enter province" autocomplete="address-level1" required>
												</div>
											</div>
											<div class="visitor-input-group">
												<label class="kiosk-field-label" for="visitorRegion">Region</label>
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="visitorRegion" name="region" type="text" placeholder="Enter region" readonly>
												</div>
											</div>
										</div>
									</section>

									<section class="kiosk-form-section">
										<div class="kiosk-office-heading">
											<h3 class="kiosk-form-section-title"><i class="bi bi-building-fill"></i> Office to Visit <span class="required-mark">*</span></h3>
											@if ($registerType !== 'contractor')
												<label class="kiosk-select-all-inline" for="officeSelectAll">
													<input type="checkbox" id="officeSelectAll" class="office-checkbox" value="__select_all__" name="destination_office_select_all">
													<span class="checkbox-indicator" aria-hidden="true"></span>
													<span>Select All</span>
												</label>
											@endif
										</div>
										@if ($registerType === 'contractor')
											<div class="visitor-input-group spaced-top">
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="destinationOfficeText" name="destination_office_text" type="text" placeholder="Enter destination office" required>
												</div>
											</div>
										@else
											<div class="kiosk-office-grid" id="destinationOffice">
												<p class="office-list-note" id="officeListNote">Loading offices...</p>
											</div>
											<div class="visitor-input-group other-destination-wrap is-hidden" id="otherDestinationWrap">
												<label class="kiosk-field-label" for="otherDestinationText">Specify Destination <span class="required-mark">*</span></label>
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="otherDestinationText" name="other_destination_text" type="text" maxlength="255" placeholder="Enter office or destination" autocomplete="off">
												</div>
											</div>
										@endif
									</section>

									@if ($registerType === 'contractor')
										<section class="kiosk-form-section">
											<h3 class="kiosk-form-section-title"><i class="bi bi-person-lines-fill"></i> Contact Person <span class="required-mark">*</span></h3>
											<div class="visitor-input-group spaced-top">
												<div class="kiosk-input-wrap">
													<input class="visitor-input" id="contactPerson" name="contact_person" type="text" placeholder="Enter contact person" required>
												</div>
											</div>
										</section>
									@endif

									<section class="kiosk-form-section">
										<h3 class="kiosk-form-section-title"><i class="bi bi-pencil-fill"></i> Purpose <span class="required-mark">*</span></h3>
										<div class="visitor-input-group spaced-top">
											<textarea class="visitor-textarea" id="visitReason" placeholder="Please state the purpose of your visit..." required></textarea>
										</div>
									</section>

									<section class="kiosk-form-section">
										<h3 class="kiosk-form-section-title"><i class="bi bi-person-vcard-fill"></i> ID Pass Number <span class="required-mark">*</span></h3>
										<div class="visitor-input-group spaced-top">
											<div class="kiosk-input-wrap">
												<input class="visitor-input" id="visitorIdPassNumber" name="id_pass_number" type="text" placeholder="Enter ID pass number" required>
											</div>
										</div>
									</section>

									<input id="visitorControlNumber" name="control_number" type="hidden">

									<div class="kiosk-verify-actions desktop-actions">
										<button type="button" class="kiosk-verify-back" id="kioskVerifyBackBtn">
											<i class="bi bi-chevron-left"></i>
											<span>Back</span>
										</button>
										<button type="button" class="kiosk-verify-proceed" id="generateQrBtn">
											<span class="submit-text">Confirm and Continue</span>
											<i class="bi bi-chevron-right submit-arrow"></i>
										</button>
									</div>
									</div>
								</div>
							</div>

							<aside class="kiosk-verify-sidebar">
								<div class="kiosk-summary-card">
									<h3 class="kiosk-summary-card-title"><i class="bi bi-person-fill"></i> Visitor Summary</h3>
									<span class="kiosk-summary-badge">{{ $registerTypeLabels[$registerType] ?? 'Visitor' }}</span>
									<div class="kiosk-summary-progress-label">
										<span>Registration Progress</span>
										<strong class="kiosk-summary-progress-value">66%</strong>
									</div>
									<div class="kiosk-summary-progress-track">
										<div class="kiosk-summary-progress-fill"></div>
									</div>
									<div class="kiosk-id-scan-status">
										<i class="bi bi-check-lg" aria-hidden="true"></i>
										<div>
											<strong>ID Scan Completed</strong>
											<span>Completed</span>
											<small>ID information was captured successfully.</small>
										</div>
									</div>
								</div>
								<div class="kiosk-verify-tips-card">
									<h3 class="kiosk-verify-tips-title"><i class="bi bi-lightbulb-fill"></i> Before Continuing</h3>
									<ul class="kiosk-verify-tips-list">
										<li><i class="bi bi-check-lg" aria-hidden="true"></i> Review all entered information.</li>
										<li><i class="bi bi-check-lg" aria-hidden="true"></i> Make sure required fields are complete.</li>
										<li><i class="bi bi-check-lg" aria-hidden="true"></i> Select one or more offices to visit.</li>
									</ul>
								</div>
							</aside>
						</div>
						<div class="kiosk-mobile-action-bar">
							<button type="button" class="mobile-back-button" id="kioskVerifyBackBtnMobile">
								<i class="bi bi-chevron-left" aria-hidden="true"></i>
								<span>Back</span>
							</button>
							<button type="button" class="mobile-continue-button" id="generateQrBtnMobile">
								<span>Continue</span>
								<i class="bi bi-chevron-right" aria-hidden="true"></i>
							</button>
						</div>
						@else
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
									<label class="visitor-label" for="visitorHouseNo">House No.</label>
									<input class="visitor-input" id="visitorHouseNo" name="house_no" type="text" placeholder="House no.">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorStreet">Street</label>
									<input class="visitor-input" id="visitorStreet" name="street" type="text" placeholder="Street">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorBarangay">Barangay</label>
									<input class="visitor-input" id="visitorBarangay" name="barangay" type="text" placeholder="Barangay">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorCity">City / Municipality</label>
									<input class="visitor-input" id="visitorCity" name="city_municipality" type="text" placeholder="City / municipality">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorProvince">Province</label>
									<input class="visitor-input" id="visitorProvince" name="province" type="text" placeholder="Province">
								</div>
								<div class="visitor-input-group">
									<label class="visitor-label" for="visitorRegion">Region</label>
									<input class="visitor-input" id="visitorRegion" name="region" type="text" placeholder="Region" readonly>
								</div>
							</div>
						</div>

						<div class="visitor-field">
							<label class="visitor-label" for="visitorPhoneNumber">Phone Number <span class="required-mark">*</span></label>
							<input class="visitor-input" id="visitorPhoneNumber" name="phone_number" type="tel" placeholder="09XXXXXXXXX" inputmode="numeric" minlength="11" maxlength="11" pattern="[0-9]{11}" required>
						</div>

						<div class="visitor-field">
							<div class="office-visit-heading">
								<label class="visitor-label" for="destinationOffice">Office to Visit <span class="required-mark">*</span></label>
								@if ($registerType !== 'contractor')
									<label class="office-select-all-inline" for="officeSelectAll">
										<input type="checkbox" id="officeSelectAll" class="office-checkbox" value="__select_all__" name="destination_office_select_all">
										<span>Select All</span>
									</label>
								@endif
							</div>
							@if ($registerType === 'contractor')
								<input class="visitor-input" id="destinationOfficeText" name="destination_office_text" type="text" placeholder="Enter destination office" required>
							@else
								<div class="office-list" id="destinationOffice">
									<p class="office-list-note" id="officeListNote">Loading offices...</p>
								</div>
								<div class="visitor-field other-destination-wrap is-hidden" id="otherDestinationWrap">
									<label class="visitor-label" for="otherDestinationText">Specify Destination <span class="required-mark">*</span></label>
									<input class="visitor-input" id="otherDestinationText" name="other_destination_text" type="text" maxlength="255" placeholder="Enter office or destination" autocomplete="off">
								</div>
							@endif
						</div>

						@if ($registerType === 'contractor')
							<div class="visitor-field">
								<label class="visitor-label" for="contactPerson">Contact Person <span class="required-mark">*</span></label>
								<input class="visitor-input" id="contactPerson" name="contact_person" type="text" placeholder="Enter contact person" required>
							</div>
						@endif

						<div class="visitor-textarea-wrap">
							<label class="visitor-label" for="visitReason">Purpose <span class="required-mark">*</span></label>
							<textarea class="visitor-textarea" id="visitReason" required></textarea>
						</div>

						<div class="visitor-field">
							<label class="visitor-label" for="visitorIdPassNumber">ID Pass Number <span class="required-mark">*</span></label>
							<input class="visitor-input" id="visitorIdPassNumber" name="id_pass_number" type="text" placeholder="" required>
						</div>

						<input id="visitorControlNumber" name="control_number" type="hidden">

						<button type="button" class="visitor-submit" id="generateQrBtn">Proceed to Face + ID Capture</button>
						@endif
					</div>
					@endif

					<div class="registration-complete is-hidden" id="registrationCompletePanel">
						<div class="qr-success" id="registrationTicketCard">
							<section class="qr-success-capture" id="ticketCapture">
								<div class="qr-success-banner">
									<div class="qr-success-banner-left">
										<div class="qr-success-icon-ring">
											<div class="qr-success-icon" aria-hidden="true">
												<svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>
											</div>
										</div>
										<div class="qr-success-banner-text">
											<h2>Visitor Registered Successfully</h2>
											<span class="qr-success-type-badge" id="ticketVisitorTypeBadge">
												@if ($registerType === 'enrollee')
													Enrollee
												@elseif ($registerType === 'contractor')
													Contractor
												@else
													Normal Visitor
												@endif
											</span>
										</div>
									</div>
									<svg class="qr-success-shield" viewBox="0 0 24 24" aria-hidden="true">
										<path d="M12 3 5 6v5c0 5 3 8 7 10 4-2 7-5 7-10V6l-7-3Z"/>
									</svg>
								</div>

								<div class="qr-success-primary-grid">
									<section class="qr-success-visitor-card">
										<div class="qr-success-photo-wrap">
											<img id="ticketPhoto" class="qr-success-photo is-hidden" alt="Visitor face and ID">
											<div id="ticketPhotoFallback" class="qr-success-photo-fallback" aria-hidden="true">
												<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3-7 8-7s8 3 8 7"/></svg>
											</div>
										</div>
										<div class="qr-success-visitor-details">
											<div class="qr-success-detail-row">
												<div class="qr-success-detail-icon" aria-hidden="true">
													<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3-7 8-7s8 3 8 7"/></svg>
												</div>
												<div>
													<span class="qr-success-detail-label">Full Name</span>
													<strong class="qr-success-detail-value qr-success-name" id="ticketVisitorName">-</strong>
												</div>
											</div>
											<div class="qr-success-detail-sep"></div>
											<div class="qr-success-detail-row">
												<div class="qr-success-detail-icon" aria-hidden="true">
													<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8" cy="11" r="2"/><path d="M6 16c.5-1.5 1.5-2 2-2s1.5.5 2 2M13 9h5M13 13h5"/></svg>
												</div>
												<div>
													<span class="qr-success-detail-label">ID Pass Number</span>
													<strong class="qr-success-detail-value" id="ticketPassNumber">-</strong>
												</div>
											</div>
											<div class="qr-success-detail-sep"></div>
											<div class="qr-success-detail-row">
												<div class="qr-success-detail-icon" aria-hidden="true">
													<svg viewBox="0 0 24 24"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 2h6v4H9zM9 10h6M9 14h6M9 18h4"/></svg>
												</div>
												<div>
													<span class="qr-success-detail-label">Control Number</span>
													<strong class="qr-success-detail-value qr-success-control" id="ticketControlNumber">-</strong>
												</div>
											</div>
										</div>
									</section>

									<section class="qr-success-qr-card">
										<p class="qr-success-qr-title">Present this QR code</p>
										<div class="qr-success-qr-frame">
											<span class="qr-success-corner qr-success-corner-tl"></span>
											<span class="qr-success-corner qr-success-corner-tr"></span>
											<span class="qr-success-corner qr-success-corner-bl"></span>
											<span class="qr-success-corner qr-success-corner-br"></span>
											<div class="qr-box" id="qrCodeContainer"></div>
										</div>
										<p class="qr-success-qr-hint">at each stop on your route.</p>
									</section>
								</div>

								<section class="qr-success-info-card">
									<div class="qr-success-info-item">
										<div class="qr-success-detail-icon" aria-hidden="true">
											<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="2"/></svg>
										</div>
										<div>
											<span class="qr-success-detail-label">Purpose</span>
											<strong class="qr-success-info-value" id="ticketPurpose">-</strong>
										</div>
									</div>
									<div class="qr-success-info-divider"></div>
									<div class="qr-success-info-item qr-success-info-destination">
										<div class="qr-success-detail-icon" aria-hidden="true">
											<svg viewBox="0 0 24 24"><path d="M3 8h18v12H3zM8 8V5h8v3"/></svg>
										</div>
										<div>
											<span class="qr-success-detail-label">Destination</span>
											<strong class="qr-success-info-value" id="ticketDestination">-</strong>
										</div>
									</div>
								</section>

								<section class="qr-success-route-card">
									<div class="qr-success-route-heading">
										<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s7-6 7-13A7 7 0 0 0 5 9c0 7 7 13 7 13Z"/><circle cx="12" cy="9" r="2"/></svg>
										<h3>Visit route (in order)</h3>
									</div>
									<div class="qr-success-route-list" id="ticketRouteList"></div>
									<div class="qr-success-route-note">
										<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg>
										<p>Keep this pass ready. Staff will scan the code at each office to record your visit.</p>
									</div>
								</section>
							</section>

							<p class="ticket-status" id="ticketSaveStatus">Generating QR and saving details...</p>

							<div class="qr-success-actions ticket-actions">
								<button type="button" class="qr-success-btn qr-success-btn-primary" id="downloadQrBtn">
									<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>
									<span>Download</span>
								</button>
								<button type="button" class="qr-success-btn qr-success-btn-secondary" id="printTicketBtn">
									<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
									<span>Print</span>
								</button>
								<a href="{{ url('/guard/dashboard') }}" class="qr-success-btn qr-success-btn-primary qr-success-btn-complete" id="newVisitorAfterTicketBtn">
									<span class="qr-success-complete-icon" aria-hidden="true">
										<svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>
									</span>
									<span>Complete &amp; return</span>
								</a>
							</div>
						</div>
					</div>
				</section>
			@endif
			@if ($isSelfRegisteredRole)
				</div>
			@endif
		</main>
	</div>

	@include('guard.partials.guard-responsive-script')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	@include('guard.partials.guard-privacy-consent')
	@if ($isSelfRegisteredRole)
	<script nonce="{{ $cspNonce }}">
		(() => {
			const urls = {
				current: @json(route('self-registration.guard-on-duty')),
				assign: @json(route('self-registration.guard-on-duty.assign')),
				change: @json(route('self-registration.guard-on-duty.change')),
				end: @json(route('self-registration.guard-on-duty.end')),
			};
			const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
			const panel = document.getElementById('kioskGuardDuty');
			const emptyCard = document.getElementById('kioskGuardDutyEmpty');
			const activeCard = document.getElementById('kioskGuardDutyActive');
			const nameEl = document.getElementById('kioskGuardDutyName');
			const metaEl = document.getElementById('kioskGuardDutyMeta');
			const stationEl = document.getElementById('kioskGuardDutyStation');
			const sinceEl = document.getElementById('kioskGuardDutySince');
			const grid = document.getElementById('kioskTypeGrid');
			const cards = Array.from(document.querySelectorAll('.kiosk-type-card'));
			const modal = document.getElementById('guardDutyModal');
			const form = document.getElementById('guardDutyForm');
			const titleEl = document.getElementById('guardDutyModalTitle');
			const subtitleEl = document.getElementById('guardDutyModalSubtitle');
			const emailLabel = document.getElementById('guardDutyEmailLabel');
			const emailInput = document.getElementById('guardDutyEmail');
			const passwordInput = document.getElementById('guardDutyPassword');
			const errorEl = document.getElementById('guardDutyError');
			const submitBtn = document.getElementById('guardDutySubmitBtn');
			const currentWrap = document.getElementById('guardDutyCurrentWrap');
			const currentNameEl = document.getElementById('guardDutyCurrentName');
			const currentSinceEl = document.getElementById('guardDutyCurrentSince');
			const endDutyModal = document.getElementById('endDutyModal');
			const endDutyForm = document.getElementById('endDutyForm');
			const endDutyPassword = document.getElementById('endDutyPassword');
			const endDutyError = document.getElementById('endDutyError');
			const endDutySubmitBtn = document.getElementById('endDutySubmitBtn');
			const endDutyGuardName = document.getElementById('endDutyGuardName');
			const endDutyGuardBadge = document.getElementById('endDutyGuardBadge');
			const endDutyGuardStation = document.getElementById('endDutyGuardStation');
			const endDutyStartedAt = document.getElementById('endDutyStartedAt');
			const logoutDutyModal = document.getElementById('logoutDutyModal');
			const logoutForm = document.getElementById('self-reg-logout-form');
			const logoutBtn = document.getElementById('selfRegLogoutBtn');
			let modalMode = 'assign';
			let currentShift = null;
			let logoutAfterEnd = false;

			const syncKioskTypeHeaderOffset = () => {
				if (!document.body.classList.contains('kiosk-type-select')) {
					return;
				}

				const header = document.querySelector('.self-registration-header');
				const picker = document.querySelector('.kiosk-type-picker');
				if (!header || !picker) {
					return;
				}

				const headerHeight = Math.ceil(header.getBoundingClientRect().height);
				document.documentElement.style.setProperty('--self-reg-header-height', `${headerHeight}px`);
				picker.style.paddingTop = `${headerHeight + 24}px`;
			};

			syncKioskTypeHeaderOffset();
			window.addEventListener('resize', syncKioskTypeHeaderOffset);
			window.addEventListener('orientationchange', syncKioskTypeHeaderOffset);

			const csrfHeaders = () => ({
				'Content-Type': 'application/json',
				'Accept': 'application/json',
				'X-CSRF-TOKEN': csrfToken,
			});

			const formatDutySince = (value) => {
				if (!value) {
					return '';
				}

				const date = new Date(value);
				if (Number.isNaN(date.getTime())) {
					return '';
				}

				return date.toLocaleTimeString('en-US', {
					hour: 'numeric',
					minute: '2-digit',
				});
			};

			const clearPasswordField = (input) => {
				if (input) {
					input.value = '';
				}
			};

			const setVisitorCardsEnabled = (enabled) => {
				grid?.classList.toggle('is-locked', !enabled);
				cards.forEach((card) => {
					card.classList.toggle('is-disabled', !enabled);
					card.setAttribute('aria-disabled', enabled ? 'false' : 'true');
					const destination = card.getAttribute('data-href') || card.getAttribute('href') || '#';
					if (enabled) {
						card.removeAttribute('tabindex');
						card.setAttribute('href', destination);
					} else {
						card.setAttribute('tabindex', '-1');
						card.setAttribute('href', '#');
					}
				});
			};

			const renderGuardState = (payload) => {
				const hasActive = Boolean(payload?.has_active_guard && payload?.shift);
				currentShift = hasActive ? payload.shift : null;
				if (panel) {
					panel.dataset.hasActive = hasActive ? '1' : '0';
				}
				emptyCard?.classList.toggle('is-hidden', hasActive);
				activeCard?.classList.toggle('is-hidden', !hasActive);
				setVisitorCardsEnabled(hasActive);

				if (!hasActive || !currentShift) {
					return;
				}

				const guard = currentShift.guard || {};
				const badge = String(guard.badge_number || '').trim();
				const station = String(guard.station || '').trim();
				const since = formatDutySince(currentShift.clock_in_at);

				if (nameEl) {
					nameEl.textContent = guard.name || 'Security Guard';
				}
				if (metaEl) {
					metaEl.textContent = badge !== ''
						? `Security Guard • Badge ${badge}`
						: 'Security Guard';
				}
				if (stationEl) {
					stationEl.textContent = station !== '' ? station : 'Self-Registration';
				}
				if (sinceEl) {
					sinceEl.textContent = since ? `Since ${since}` : '';
				}
			};

			const setModalError = (message) => {
				if (errorEl) {
					errorEl.textContent = message || '';
				}
			};

			const setModalBusy = (busy) => {
				if (submitBtn) {
					submitBtn.disabled = busy;
				}
				if (emailInput) {
					emailInput.disabled = busy;
				}
				if (passwordInput) {
					passwordInput.disabled = busy;
				}
			};

			const setEndDutyError = (message) => {
				if (endDutyError) {
					endDutyError.textContent = message || '';
				}
			};

			const setEndDutyBusy = (busy) => {
				if (endDutySubmitBtn) {
					endDutySubmitBtn.disabled = busy;
				}
				if (endDutyPassword) {
					endDutyPassword.disabled = busy;
				}
			};

			const openModalEl = (el) => {
				el?.classList.remove('is-hidden');
				el?.setAttribute('aria-hidden', 'false');
			};

			const closeModalEl = (el) => {
				el?.classList.add('is-hidden');
				el?.setAttribute('aria-hidden', 'true');
			};

			const openModal = (mode) => {
				modalMode = mode;
				setModalError('');
				if (form) {
					form.reset();
				}

				const isChange = mode === 'change';
				if (titleEl) {
					titleEl.textContent = isChange ? 'Change Guard' : 'Set Guard on Duty';
				}
				if (subtitleEl) {
					subtitleEl.textContent = isChange
						? 'The incoming guard must authenticate before taking over this kiosk.'
						: 'Sign in with an active guard account to assign the officer on duty.';
				}
				if (emailLabel) {
					emailLabel.textContent = isChange ? 'New Guard Email' : 'Email / Guard Account';
				}
				if (submitBtn) {
					submitBtn.textContent = isChange ? 'Confirm Guard Change' : 'Confirm';
				}

				currentWrap?.classList.toggle('is-hidden', !isChange);
				if (isChange && currentShift) {
					if (currentNameEl) {
						currentNameEl.textContent = currentShift.guard?.name || 'Security Guard';
					}
					if (currentSinceEl) {
						const since = formatDutySince(currentShift.clock_in_at);
						currentSinceEl.textContent = since ? `Since ${since}` : 'Currently assigned';
					}
				}

				openModalEl(modal);
				window.setTimeout(() => emailInput?.focus(), 50);
			};

			const closeModal = () => {
				closeModalEl(modal);
				setModalBusy(false);
				setModalError('');
				form?.reset();
			};

			const populateEndDutyModal = (shift) => {
				const guard = shift?.guard || {};
				const badge = String(guard.badge_number || '').trim();
				const station = String(guard.station || '').trim();
				const since = formatDutySince(shift?.clock_in_at);

				if (endDutyGuardName) {
					endDutyGuardName.textContent = guard.name || 'Security Guard';
				}
				if (endDutyGuardBadge) {
					endDutyGuardBadge.textContent = badge !== '' ? `Badge No. ${badge}` : 'Badge No. —';
				}
				if (endDutyGuardStation) {
					endDutyGuardStation.textContent = station !== '' ? station : 'Self-Registration';
				}
				if (endDutyStartedAt) {
					endDutyStartedAt.textContent = since || '—';
				}
			};

			const openEndDutyModal = (afterLogout = false) => {
				logoutAfterEnd = afterLogout;
				setEndDutyError('');
				endDutyForm?.reset();
				clearPasswordField(endDutyPassword);
				populateEndDutyModal(currentShift);
				closeModalEl(logoutDutyModal);
				openModalEl(endDutyModal);
				window.setTimeout(() => endDutyPassword?.focus(), 50);
			};

			const closeEndDutyModal = () => {
				closeModalEl(endDutyModal);
				setEndDutyBusy(false);
				setEndDutyError('');
				endDutyForm?.reset();
				clearPasswordField(endDutyPassword);
				logoutAfterEnd = false;
			};

			const openLogoutDutyModal = () => {
				openModalEl(logoutDutyModal);
			};

			const closeLogoutDutyModal = () => {
				closeModalEl(logoutDutyModal);
			};

			const submitLogoutForm = () => {
				logoutForm?.submit();
			};

			const fetchCurrentGuard = async () => {
				const response = await fetch(urls.current, {
					headers: { 'Accept': 'application/json' },
					credentials: 'same-origin',
				});
				const payload = await response.json().catch(() => ({ has_active_guard: false, shift: null }));
				renderGuardState(payload);
				return payload;
			};

			const submitGuardDuty = async (event) => {
				event.preventDefault();
				setModalError('');

				const email = String(emailInput?.value || '').trim();
				const password = String(passwordInput?.value || '');

				if (!email || !password) {
					setModalError('Email and password are required.');
					return;
				}

				setModalBusy(true);

				try {
					const response = await fetch(modalMode === 'change' ? urls.change : urls.assign, {
						method: 'POST',
						headers: csrfHeaders(),
						credentials: 'same-origin',
						body: JSON.stringify({ email, password }),
					});
					const payload = await response.json().catch(() => ({}));

					if (!response.ok || payload.success === false) {
						throw new Error(payload.message || 'Invalid guard credentials.');
					}

					renderGuardState(payload);
					closeModal();
				} catch (error) {
					setModalError(error?.message || 'Invalid guard credentials.');
					clearPasswordField(passwordInput);
					passwordInput?.focus();
				} finally {
					setModalBusy(false);
				}
			};

			const submitEndDuty = async (event) => {
				event.preventDefault();
				setEndDutyError('');

				const password = String(endDutyPassword?.value || '');
				if (!password) {
					setEndDutyError('Password is required.');
					return;
				}

				setEndDutyBusy(true);

				try {
					const response = await fetch(urls.end, {
						method: 'POST',
						headers: csrfHeaders(),
						credentials: 'same-origin',
						body: JSON.stringify({ password }),
					});
					const payload = await response.json().catch(() => ({}));

					if (!response.ok || payload.success === false) {
						throw new Error(payload.message || 'Invalid guard credentials.');
					}

					renderGuardState(payload);
					const shouldLogout = logoutAfterEnd;
					closeEndDutyModal();

					if (shouldLogout) {
						submitLogoutForm();
					}
				} catch (error) {
					setEndDutyError(error?.message || 'Invalid guard credentials.');
					clearPasswordField(endDutyPassword);
					endDutyPassword?.focus();
				} finally {
					setEndDutyBusy(false);
				}
			};

			const handleLogoutClick = async (event) => {
				event.preventDefault();

				try {
					const payload = await fetchCurrentGuard();
					if (payload?.has_active_guard && payload?.shift) {
						openLogoutDutyModal();
						return;
					}
				} catch (error) {
					if (currentShift) {
						openLogoutDutyModal();
						return;
					}
				}

				submitLogoutForm();
			};

			document.getElementById('kioskSetGuardBtn')?.addEventListener('click', () => openModal('assign'));
			document.getElementById('kioskChangeGuardBtn')?.addEventListener('click', () => openModal('change'));
			document.getElementById('kioskEndDutyBtn')?.addEventListener('click', () => openEndDutyModal(false));
			document.getElementById('guardDutyCancelBtn')?.addEventListener('click', closeModal);
			document.getElementById('endDutyCancelBtn')?.addEventListener('click', closeEndDutyModal);
			document.getElementById('logoutDutyCancelBtn')?.addEventListener('click', closeLogoutDutyModal);
			document.getElementById('logoutDutyOnlyBtn')?.addEventListener('click', () => {
				closeLogoutDutyModal();
				submitLogoutForm();
			});
			document.getElementById('logoutDutyEndBtn')?.addEventListener('click', () => openEndDutyModal(true));
			logoutBtn?.addEventListener('click', handleLogoutClick);
			form?.addEventListener('submit', submitGuardDuty);
			endDutyForm?.addEventListener('submit', submitEndDuty);
			modal?.addEventListener('click', (event) => {
				if (event.target === modal) {
					closeModal();
				}
			});
			endDutyModal?.addEventListener('click', (event) => {
				if (event.target === endDutyModal) {
					closeEndDutyModal();
				}
			});
			logoutDutyModal?.addEventListener('click', (event) => {
				if (event.target === logoutDutyModal) {
					closeLogoutDutyModal();
				}
			});
			document.addEventListener('keydown', (event) => {
				if (event.key !== 'Escape') {
					return;
				}
				if (endDutyModal && !endDutyModal.classList.contains('is-hidden')) {
					closeEndDutyModal();
					return;
				}
				if (logoutDutyModal && !logoutDutyModal.classList.contains('is-hidden')) {
					closeLogoutDutyModal();
					return;
				}
				if (modal && !modal.classList.contains('is-hidden')) {
					closeModal();
				}
			});

			if (panel) {
				fetchCurrentGuard().catch(() => {
					renderGuardState({ has_active_guard: false, shift: null });
				});
			}
		})();
	</script>
	@endif
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
	<script nonce="{{ $cspNonce }}">
		const registerMenuGroup = document.getElementById('registerMenuGroup');
		const registerMenuToggle = document.getElementById('registerMenuToggle');
		const flowStepName = document.getElementById('flowStepName');
		const flowStepCount = document.getElementById('flowStepCount');
		const selfRegProgress = document.getElementById('selfRegProgress');
		const selfRegStepperSteps = document.querySelectorAll('.self-reg-step');
		const selfRegStepperLines = document.querySelectorAll('.self-reg-step-line');
		const kioskScanTitleText = document.getElementById('kioskScanTitleText');
		const kioskScanDesc = document.getElementById('kioskScanDesc');
		const kioskCameraBadge = document.getElementById('kioskCameraBadge');
		const kioskCameraBadgeText = document.getElementById('kioskCameraBadgeText');
		const kioskCameraStatusCard = document.getElementById('kioskCameraStatusCard');
		const kioskCameraStatusTitle = document.getElementById('kioskCameraStatusTitle');
		const kioskCameraStatusText = document.getElementById('kioskCameraStatusText');
		const kioskVisitorStep = document.getElementById('kioskVisitorStep');
		const kioskBottomPanels = document.getElementById('kioskBottomPanels');
		const kioskRegFooter = document.querySelector('.kiosk-reg-footer');
		const flowHead = document.querySelector('.flow-head');
		const scannerZone = document.querySelector('.scanner-zone');
		const cameraFeed = document.getElementById('cameraFeed');
		const flipCameraBtn = document.getElementById('flipCameraBtn');
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
		const ticketCapture = document.getElementById('ticketCapture');
		const qrCodeContainer = document.getElementById('qrCodeContainer');
		const ticketControlNumber = document.getElementById('ticketControlNumber');
		const ticketVisitorName = document.getElementById('ticketVisitorName');
		const ticketPassNumber = document.getElementById('ticketPassNumber');
		const ticketPurpose = document.getElementById('ticketPurpose');
		const ticketDestination = document.getElementById('ticketDestination');
		const ticketRouteList = document.getElementById('ticketRouteList');
		const ticketPhoto = document.getElementById('ticketPhoto');
		const ticketPhotoFallback = document.getElementById('ticketPhotoFallback');
		const ticketSaveStatus = document.getElementById('ticketSaveStatus');
		const downloadQrBtn = document.getElementById('downloadQrBtn');
		const printTicketBtn = document.getElementById('printTicketBtn');
		const newVisitorAfterTicketBtn = document.getElementById('newVisitorAfterTicketBtn');
		const pageTitleEl = document.querySelector('h1.page-title');
		const guardTopbarEl = document.querySelector('.guard-topbar');
		const existingVisitorModal = document.getElementById('existingVisitorModal');
		const existingVisitorModalPhotoFrame = document.getElementById('existingVisitorModalPhotoFrame');
		const existingVisitorModalPhoto = document.getElementById('existingVisitorModalPhoto');
		const existingVisitorModalPhotoPlaceholder = document.getElementById('existingVisitorModalPhotoPlaceholder');
		const existingVisitorModalValidationNote = document.getElementById('existingVisitorModalValidationNote');
		const existingVisitorModalTitle = document.getElementById('existingVisitorModalTitle');
		const existingVisitorModalSubtitle = document.getElementById('existingVisitorModalSubtitle');
		const existingVisitorModalName = document.getElementById('existingVisitorModalName');
		const existingVisitorModalContact = document.getElementById('existingVisitorModalContact');
		const existingVisitorModalBirthday = document.getElementById('existingVisitorModalBirthday');
		const existingVisitorModalAddressState = document.getElementById('existingVisitorModalAddressState');
		const existingVisitorModalProgressRow = document.getElementById('existingVisitorModalProgressRow');
		const existingVisitorModalProgress = document.getElementById('existingVisitorModalProgress');
		const existingVisitorModalConfirm = document.getElementById('existingVisitorModalConfirm');
		const existingVisitorModalCancel = document.getElementById('existingVisitorModalCancel');
		const visitorPhoneNumber = document.getElementById('visitorPhoneNumber');
		const destinationOffice = document.getElementById('destinationOffice');
		const destinationOfficeText = document.getElementById('destinationOfficeText');
		const otherDestinationWrap = document.getElementById('otherDestinationWrap');
		const otherDestinationText = document.getElementById('otherDestinationText');
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
		const isSelfRegistrationKiosk = @json($isSelfRegisteredRole);

		const hasFinalStepPanel = Boolean(visitorStepPanel || enrolleeStepPanel);
		const hasRegisterFlow = Boolean(
			flowStepName && flowStepCount && scannerCard && pictureGuide && idGuide &&
			idTypesPanel && hasFinalStepPanel && scanAction && scanActionText &&
			galleryAction && galleryHint && loadingOverlay && loadingText
		);
		let activeStream = null;
		/** @type {'environment'|'user'} */
		let preferredFacingMode = 'environment';
		let currentStep = 1;
		let capturedPictureData = '';
		let selectedOfficeIds = [];
		let selectedOfficeId = null;
		let isOtherDestination = false;
		const OTHERS_OFFICE_VALUE = '__others__';
		const SELECT_ALL_VALUE = '__select_all__';
		let faceIdCapturePublicPath = '';
		let faceIdCapturePreviewUrl = '';
		let hasSavedRegistration = false;
		/** Compact scan text in the on-screen / printed QR (must not call createQrMeta() again). */
		let lastTicketQrPayload = '';
		const QR_DISPLAY_SIZE = 170;
		const QR_PRINT_SIZE = 400;

		const renderQrIntoElement = (container, text, size, correctLevel) => {
			if (!container || !text || typeof QRCode === 'undefined') {
				return false;
			}

			container.innerHTML = '';
			new QRCode(container, {
				text,
				width: size,
				height: size,
				colorDark: '#000000',
				colorLight: '#ffffff',
				correctLevel: correctLevel || QRCode.CorrectLevel.M,
			});

			// Keep all renderer fallbacks (canvas/img/table) because some browsers only display one correctly.
			const canvasEl = container.querySelector('canvas');

			return Boolean(canvasEl || container.querySelector('img') || container.querySelector('table'));
		};
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

		const guardSidebarNavEl = document.getElementById('guardSidebarNav');
		let bypassNativeBeforeUnloadPrompt = false;

		const shouldWarnLeaveRegisterFlow = () => {
			if (!hasRegisterFlow || hasSavedRegistration) {
				return false;
			}
			if (currentStep > 1) {
				return true;
			}
			return Boolean(frozenFrame?.classList.contains('visible'));
		};

		const leaveRegisterConfirmOptions = () => ({
			toast: true,
			position: 'top',
			icon: 'warning',
			iconColor: '#f59e0b',
			title: 'Leave this page?',
			text: 'Unsaved registration progress will be lost.',
			showCancelButton: true,
			confirmButtonText: 'Leave page',
			cancelButtonText: 'Stay here',
			focusCancel: true,
			reverseButtons: true,
			buttonsStyling: false,
			customClass: {
				popup: 'leave-register-toast',
				title: 'leave-register-toast-title',
				htmlContainer: 'leave-register-toast-text',
				confirmButton: 'leave-register-toast-btn leave-register-toast-btn-leave',
				cancelButton: 'leave-register-toast-btn leave-register-toast-btn-stay',
				actions: 'leave-register-toast-actions',
			},
		});

		const confirmKioskNavigation = (targetHref) => {
			if (!targetHref) {
				return;
			}

			Swal.fire(leaveRegisterConfirmOptions()).then((result) => {
				if (result.isConfirmed) {
					bypassNativeBeforeUnloadPrompt = true;
					window.location.href = targetHref;
				}
			});
		};

		document.querySelectorAll('.js-kiosk-type-link').forEach((link) => {
			link.addEventListener('click', (event) => {
				if (link.classList.contains('active')) {
					event.preventDefault();
					return;
				}

				if (!hasRegisterFlow || !shouldWarnLeaveRegisterFlow()) {
					return;
				}

				event.preventDefault();
				confirmKioskNavigation(link.getAttribute('href'));
			});
		});

		const confirmLeaveRegisterFlow = async () => {
			if (typeof window.Swal?.fire !== 'function') {
				return window.confirm('Unsaved registration progress will be lost. Leave this page?');
			}

			const result = await window.Swal.fire(leaveRegisterConfirmOptions());

			return Boolean(result.isConfirmed);
		};

		guardSidebarNavEl?.addEventListener('click', (e) => {
			const anchor = e.target.closest?.('a[href]');
			if (!anchor || !anchor.href) {
				return;
			}
			const hrefAttr = anchor.getAttribute('href') || '';
			if (hrefAttr.startsWith('#') || hrefAttr.startsWith('javascript:')) {
				return;
			}
			if (!shouldWarnLeaveRegisterFlow()) {
				return;
			}
			e.preventDefault();
			e.stopPropagation();

			confirmLeaveRegisterFlow()
				.then((shouldLeave) => {
					if (!shouldLeave) {
						return;
					}
					bypassNativeBeforeUnloadPrompt = true;
					window.location.assign(anchor.href);
				})
				.catch(() => {
					// no-op: stay on page on any modal error
				});
		}, true);

		document.querySelector('.guard-topbar #logout-form')?.addEventListener('submit', (e) => {
			if (!shouldWarnLeaveRegisterFlow()) {
				return;
			}
			e.preventDefault();
			const form = e.currentTarget;
			confirmLeaveRegisterFlow()
				.then((shouldLeave) => {
					if (!shouldLeave) {
						return;
					}
					bypassNativeBeforeUnloadPrompt = true;
					form.submit();
				})
				.catch(() => {});
		});

		document.querySelector('.guard-topbar')?.addEventListener('click', (e) => {
			const anchor = e.target.closest?.('a[href]');
			if (!anchor || !anchor.href) {
				return;
			}
			const hrefAttr = anchor.getAttribute('href') || '';
			if (hrefAttr.startsWith('#') || hrefAttr.startsWith('javascript:')) {
				return;
			}
			if (!shouldWarnLeaveRegisterFlow()) {
				return;
			}
			e.preventDefault();
			e.stopPropagation();
			confirmLeaveRegisterFlow()
				.then((shouldLeave) => {
					if (!shouldLeave) {
						return;
					}
					bypassNativeBeforeUnloadPrompt = true;
					window.location.assign(anchor.href);
				})
				.catch(() => {});
		}, true);

		window.addEventListener('beforeunload', (e) => {
			if (bypassNativeBeforeUnloadPrompt) {
				return;
			}
			if (!shouldWarnLeaveRegisterFlow()) {
				return;
			}
			e.preventDefault();
			e.returnValue = '';
		});

		const updateKioskSummaryProgress = () => {
			if (!isSelfRegistrationKiosk) {
				return;
			}

			const progressPercent = currentStep >= 4
				? 100
				: (currentStep === 3 ? 100 : (currentStep === 2 ? 66 : 33));

			document.querySelectorAll('.kiosk-summary-progress-fill').forEach((fillEl) => {
				fillEl.style.width = `${progressPercent}%`;
			});

			document.querySelectorAll('.kiosk-summary-progress-value').forEach((valueEl) => {
				valueEl.textContent = `${progressPercent}%`;
			});
		};

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
				: (isFormStep ? (registerType === 'enrollee' ? 'Visitor Information' : 'Visitor Information') : (isPictureStep ? 'Face + ID' : 'Visitor Registered'));
			flowStepCount.textContent = isIdStep ? 'Step 1 of 3' : (isFormStep ? 'Step 2 of 3' : (isPictureStep ? 'Step 3 of 3' : 'Completed'));

			const activeStep = isCompleteStep ? 3 : Math.min(currentStep, 3);

			if (selfRegStepperSteps.length) {
				selfRegStepperSteps.forEach((stepEl) => {
					const stepNum = Number(stepEl.dataset.step || 0);
					stepEl.classList.toggle('is-active', stepNum === activeStep && !isCompleteStep);
					stepEl.classList.toggle('is-done', stepNum < activeStep || isCompleteStep);
				});
			}

			if (selfRegStepperLines.length) {
				selfRegStepperLines.forEach((lineEl) => {
					const afterStep = Number(lineEl.dataset.after || 0);
					lineEl.classList.toggle('is-done', afterStep < activeStep || isCompleteStep);
				});
			}

			if (kioskVisitorStep) {
				kioskVisitorStep.textContent = isCompleteStep
					? 'COMPLETED'
					: (isFormStep ? 'STEP 2 OF 3' : (isPictureStep ? 'STEP 3 OF 3' : 'STEP 1 OF 3'));
			}

			if (kioskScanTitleText) {
				kioskScanTitleText.textContent = isIdStep
					? 'Scan Your Identification Card'
					: (isPictureStep ? 'Capture Face and ID' : 'Scan Your Identification Card');
			}

			if (kioskScanDesc) {
				kioskScanDesc.textContent = isIdStep
					? 'Position your ID card within the frame. Ensure all details are clear and visible.'
					: (isPictureStep
						? 'Center your face in the oval and hold your ID beside it before capturing.'
						: 'Position your ID card within the frame. Ensure all details are clear and visible.');
			}

			if (kioskBottomPanels) {
				kioskBottomPanels.classList.toggle('is-hidden', !isIdStep);
			}

			if (kioskRegFooter) {
				kioskRegFooter.classList.toggle('is-hidden', isFormStep || isCompleteStep);
			}

			if (selfRegProgress) {
				selfRegProgress.classList.toggle('is-hidden', isCompleteStep);
			}

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
			if (pageTitleEl) {
				pageTitleEl.classList.toggle('is-hidden', isCompleteStep);
			}
			if (guardTopbarEl) {
				guardTopbarEl.classList.toggle('is-hidden', isCompleteStep);
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
			updateKioskSummaryProgress();
		};

		const getOfficeCheckboxes = () => Array.from(
			destinationOffice?.querySelectorAll(`.office-checkbox:not([value="${OTHERS_OFFICE_VALUE}"]):not([value="${SELECT_ALL_VALUE}"])`) || []
		);

		const getSelectAllInput = () => document.getElementById('officeSelectAll')
			|| document.querySelector(`.office-checkbox[value="${SELECT_ALL_VALUE}"]`);

		const getOthersOfficeInput = () => destinationOffice?.querySelector(`.office-checkbox[value="${OTHERS_OFFICE_VALUE}"]`);

		const syncSelectAllState = () => {
			const selectAllInput = getSelectAllInput();
			const officeInputs = getOfficeCheckboxes();
			if (!selectAllInput || !officeInputs.length) {
				return;
			}

			const checkedCount = officeInputs.filter((input) => input.checked).length;
			selectAllInput.checked = checkedCount === officeInputs.length;
			selectAllInput.indeterminate = checkedCount > 0 && checkedCount < officeInputs.length;
		};

		const bindOfficeOptionKeyboard = (label, input) => {
			label.addEventListener('keydown', (event) => {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					input.checked = !input.checked;
					input.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});
		};

		const showOtherDestinationField = () => {
			otherDestinationWrap?.classList.add('is-visible');
			otherDestinationWrap?.classList.remove('is-hidden');
			otherDestinationText?.setAttribute('aria-required', 'true');
		};

		const hideAndClearOtherDestination = () => {
			otherDestinationWrap?.classList.remove('is-visible');
			otherDestinationWrap?.classList.add('is-hidden');
			if (otherDestinationText) {
				otherDestinationText.value = '';
				otherDestinationText.removeAttribute('aria-required');
			}
		};

		const syncDestinationSelection = (event) => {
			const changed = event?.target;
			const othersInput = getOthersOfficeInput();
			const selectAllInput = getSelectAllInput();
			const officeInputs = getOfficeCheckboxes();

			if (changed?.value === SELECT_ALL_VALUE || changed?.id === 'officeSelectAll') {
				const shouldCheck = Boolean(selectAllInput?.checked);
				officeInputs.forEach((input) => {
					input.checked = shouldCheck;
				});
			}

			isOtherDestination = Boolean(othersInput?.checked);

			if (isOtherDestination) {
				showOtherDestinationField();
			} else {
				hideAndClearOtherDestination();
			}

			const checkedOffices = officeInputs.filter((input) => input.checked);
			selectedOfficeIds = checkedOffices.map((input) => String(input.value));
			selectedOfficeId = selectedOfficeIds.length ? Number(selectedOfficeIds[0]) : null;
			syncSelectAllState();
		};

		const getSelectedDestinationOffices = () => {
			if (registerType === 'contractor') {
				const text = (destinationOfficeText?.value || '').trim();
				return text ? [text] : [];
			}

			if (registerType === 'enrollee') {
				return autoEnrolleeOfficeNames.filter(Boolean);
			}

			const offices = getOfficeCheckboxes()
				.filter((input) => input.checked)
				.map((input) => {
					const label = input.closest('label') || input.parentElement?.querySelector('label') || input.parentElement;
					const named = label?.querySelector('.office-details strong, strong');
					if (named?.textContent) {
						return named.textContent.trim();
					}

					const text = label?.textContent || input.parentElement?.textContent || '';
					return text.replace(/\s*Tap to select\s*/gi, '').trim();
				})
				.filter(Boolean);

			if (isOtherDestination) {
				const text = (otherDestinationText?.value || '').trim();
				if (text) {
					offices.push(text);
				}
			}

			return offices;
		};

		const getSelectedDestinationText = () => {
			const offices = getSelectedDestinationOffices();
			return offices.length ? offices.join(', ') : '-';
		};

		const renderTicketRouteList = () => {
			if (!ticketRouteList) {
				return;
			}

			const offices = getSelectedDestinationOffices();
			const items = offices.length ? offices : ['No destination specified'];

			ticketRouteList.innerHTML = items.map((officeName, index) => `
				<div class="qr-success-route-item">
					<div class="qr-success-route-item-left">
						<span class="qr-success-route-number">${index + 1}</span>
						<strong>${String(officeName)
							.replace(/&/g, '&amp;')
							.replace(/</g, '&lt;')
							.replace(/>/g, '&gt;')
							.replace(/"/g, '&quot;')}</strong>
					</div>
					<svg class="qr-success-route-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
				</div>
			`).join('');
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

		const PROVINCE_REGION_MAP = {
			'metro manila': 'NCR',
			'manila': 'NCR',
			'abra': 'CAR',
			'apayao': 'CAR',
			'benguet': 'CAR',
			'ifugao': 'CAR',
			'kalinga': 'CAR',
			'mountain province': 'CAR',
			'ilocos norte': 'Region I',
			'ilocos sur': 'Region I',
			'la union': 'Region I',
			'pangasinan': 'Region I',
			'batanes': 'Region II',
			'cagayan': 'Region II',
			'isabela': 'Region II',
			'nueva vizcaya': 'Region II',
			'quirino': 'Region II',
			'aurora': 'Region III',
			'bataan': 'Region III',
			'bulacan': 'Region III',
			'nueva ecija': 'Region III',
			'pampanga': 'Region III',
			'tarlac': 'Region III',
			'zambales': 'Region III',
			'batangas': 'Region IV-A',
			'cavite': 'Region IV-A',
			'laguna': 'Region IV-A',
			'quezon': 'Region IV-A',
			'rizal': 'Region IV-A',
			'marinduque': 'Region IV-B',
			'occidental mindoro': 'Region IV-B',
			'oriental mindoro': 'Region IV-B',
			'palawan': 'Region IV-B',
			'romblon': 'Region IV-B',
			'albay': 'Region V',
			'camarines norte': 'Region V',
			'camarines sur': 'Region V',
			'catanduanes': 'Region V',
			'masbate': 'Region V',
			'sorsogon': 'Region V',
			'aklan': 'Region VI',
			'antique': 'Region VI',
			'capiz': 'Region VI',
			'guimaras': 'Region VI',
			'iloilo': 'Region VI',
			'negros occidental': 'Region VI',
			'bohol': 'Region VII',
			'cebu': 'Region VII',
			'negros oriental': 'Region VII',
			'siquijor': 'Region VII',
			'biliran': 'Region VIII',
			'eastern samar': 'Region VIII',
			'leyte': 'Region VIII',
			'northern samar': 'Region VIII',
			'samar': 'Region VIII',
			'southern leyte': 'Region VIII',
			'zamboanga del norte': 'Region IX',
			'zamboanga del sur': 'Region IX',
			'zamboanga sibugay': 'Region IX',
			'bukidnon': 'Region X',
			'camiguin': 'Region X',
			'lanao del norte': 'Region X',
			'misamis occidental': 'Region X',
			'misamis oriental': 'Region X',
			'davao de oro': 'Region XI',
			'davao del norte': 'Region XI',
			'davao del sur': 'Region XI',
			'davao occidental': 'Region XI',
			'davao oriental': 'Region XI',
			'cotabato': 'Region XII',
			'sarangani': 'Region XII',
			'south cotabato': 'Region XII',
			'sultan kudarat': 'Region XII',
			'agusan del norte': 'Region XIII',
			'agusan del sur': 'Region XIII',
			'dinagat islands': 'Region XIII',
			'surigao del norte': 'Region XIII',
			'surigao del sur': 'Region XIII',
			'basilan': 'BARMM',
			'lanao del sur': 'BARMM',
			'maguindanao del norte': 'BARMM',
			'maguindanao del sur': 'BARMM',
			'sulu': 'BARMM',
			'tawi-tawi': 'BARMM',
			'cotabato city': 'BARMM'
		};

		const inferRegionFromProvince = (provinceValue) => {
			const normalized = String(provinceValue || '').trim().toLowerCase().replace(/\s+/g, ' ');
			return PROVINCE_REGION_MAP[normalized] || '';
		};

		const syncRegionFromProvince = () => {
			if (!visitorProvince || !visitorRegion) {
				return;
			}

			visitorRegion.readOnly = true;
			visitorRegion.value = inferRegionFromProvince(visitorProvince.value);
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
			const scanText = String(qrMeta?.qr_scan_text || qrMeta?.qr_payload || '').trim();
			if (!qrMeta || !scanText || !renderQrIntoElement(qrCodeContainer, scanText, QR_DISPLAY_SIZE, QRCode.CorrectLevel.M)) {
				return false;
			}

			lastTicketQrPayload = scanText;

			if (ticketControlNumber) {
				ticketControlNumber.textContent = qrMeta.control_number || '-';
			}
			if (ticketVisitorName) {
				ticketVisitorName.textContent = `${toTitleCase(visitorFirstName?.value)} ${toTitleCase(visitorLastName?.value)}`.trim() || '-';
			}
			if (ticketPassNumber) {
				ticketPassNumber.textContent = (visitorIdPassNumber?.value || '').trim() || '-';
			}
			if (ticketPurpose) {
				ticketPurpose.textContent = getPurposeReasonValue() || '-';
			}
			if (ticketDestination) {
				ticketDestination.textContent = getSelectedDestinationText();
			}
			renderTicketRouteList();

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

			if (kioskCameraBadge) {
				kioskCameraBadge.classList.toggle('is-ready', isOn);
				kioskCameraBadge.classList.toggle('is-error', !isOn);
			}

			if (kioskCameraBadgeText) {
				kioskCameraBadgeText.textContent = isOn ? 'Camera Ready' : 'Camera Unavailable';
			}

			if (kioskCameraStatusCard) {
				kioskCameraStatusCard.classList.toggle('is-error', !isOn);
				const statusIcon = kioskCameraStatusCard.querySelector('.bi');
				if (statusIcon) {
					statusIcon.className = isOn ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill';
				}
			}

			if (kioskCameraStatusTitle) {
				kioskCameraStatusTitle.textContent = isOn ? 'Camera Ready' : 'Camera Unavailable';
			}

			if (kioskCameraStatusText) {
				kioskCameraStatusText.textContent = message;
			}

			if (!isOn) {
				scanActionText.textContent = 'Retry Camera';
			} else if (currentStep === 3) {
				scanActionText.textContent = 'Capture Face + ID';
			} else {
				scanActionText.textContent = 'Scan ID Card';
			}
			scanAction.disabled = false;
		};

		const setScannerAspectRatio = (width, height) => {
			if (!width || !height || isSelfRegistrationKiosk) {
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

		const updateFlipCameraUi = () => {
			if (!flipCameraBtn) {
				return;
			}

			const nextLabel = preferredFacingMode === 'environment'
				? 'Switch to front camera'
				: 'Switch to back camera';
			flipCameraBtn.title = nextLabel;
			flipCameraBtn.setAttribute('aria-label', nextLabel);
			// Mirror only for selfie preview; keep ID text readable on front cam.
			cameraFeed.classList.toggle('is-mirrored', preferredFacingMode === 'user' && currentStep === 3);
		};

		const requestCameraStream = async (facingMode) => {
			const attempts = [
				{ video: { facingMode: { exact: facingMode } }, audio: false },
				{ video: { facingMode: { ideal: facingMode } }, audio: false },
				{ video: true, audio: false }
			];

			let lastError = null;

			for (const constraints of attempts) {
				try {
					return await navigator.mediaDevices.getUserMedia(constraints);
				} catch (error) {
					lastError = error;
				}
			}

			throw lastError || new Error('Unable to access camera.');
		};

		const startCamera = async () => {
			if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
				setCameraState(false, 'Camera access is not supported in this browser.');
				return;
			}

			scanAction.disabled = true;
			if (flipCameraBtn) {
				flipCameraBtn.disabled = true;
			}

			try {
				releaseCamera();
				updateFlipCameraUi();

				const stream = await requestCameraStream(preferredFacingMode);

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
			} finally {
				if (flipCameraBtn) {
					flipCameraBtn.disabled = false;
				}
			}
		};

		const flipCamera = async () => {
			preferredFacingMode = preferredFacingMode === 'environment' ? 'user' : 'environment';
			updateFlipCameraUi();
			cameraStatus.textContent = preferredFacingMode === 'user'
				? 'Switching to front camera...'
				: 'Switching to back camera...';
			await startCamera();
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
			freezeCurrentFrame();
			capturedPictureData = captureCanvas.toDataURL('image/jpeg', 0.70);
			loadingOverlay.classList.remove('is-hidden');
			loadingText.textContent = 'Processing capture...';
			scanAction.disabled = true;

			const finishCaptureFailure = (message) => {
				loadingText.textContent = message || 'Failed to save. Try again.';
				setTimeout(() => {
					loadingOverlay.classList.add('is-hidden');
					clearFrozenFrame();
					scanAction.disabled = false;
					startCamera();
				}, 2000);
			};

			const uploadFaceCapture = (imagePayload) => {
				const formData = new FormData();
				if (imagePayload instanceof Blob) {
					formData.append('image', imagePayload, 'face-id.jpg');
				} else {
					formData.append('image', imagePayload);
				}
				formData.append('step', 3);

				return fetch('/guard/capture', {
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
					},
					body: formData
				}).then(response => response.json());
			};

			const handleCaptureSuccess = (data) => {
				faceIdCapturePublicPath = data.bucket_file_path || data.path || '';
				// Prefer signed preview URL; fall back to the in-memory capture so the ticket always shows the photo.
				faceIdCapturePreviewUrl = data.preview_url || data.public_url || capturedPictureData || '';

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
				return saveNormalVisitorRegistration(qrMeta)
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
			};

			captureCanvas.toBlob((blob) => {
				const uploadPromise = blob
					? uploadFaceCapture(blob)
					: uploadFaceCapture(capturedPictureData);

				uploadPromise
					.then(data => {
						if (data.success) {
							return handleCaptureSuccess(data);
						}
						finishCaptureFailure('Failed to save. Try again.');
					})
					.catch(error => {
						console.error('Capture error:', error);
						finishCaptureFailure(error?.message || 'Error saving capture. Try again.');
					});
			}, 'image/jpeg', 0.70);
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

			// Enrollee QR opens the public progress tracker in a phone browser.
			// Other visitor types keep compact JSON for guard exit / office scanning.
			const qrScanText = registerType === 'enrollee'
				? `${window.location.origin}/enrollee/progress/${encodeURIComponent(qrToken)}`
				: JSON.stringify({
					control_number: controlNumber,
					qr_token: qrToken,
				});

			return {
				control_number: controlNumber,
				qr_token: qrToken,
				qr_scan_text: qrScanText,
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
				contact_no: (() => {
					const digits = String(visitorPhoneNumber?.value || '').replace(/\D/g, '');
					if (isSelfRegistrationKiosk && digits.length === 10) {
						return `0${digits}`;
					}
					return digits || '';
				})(),
				birthday: visitorBirthday?.value || null,
				pass_number: visitorIdPassNumber?.value.trim() || '',
				control_number: qrMeta?.control_number || ensureAutoControlNumber(),
				purpose_reason: getPurposeReasonValue(),
				destination_office_text: registerType === 'contractor'
					? (destinationOfficeText?.value.trim() || '')
					: (registerType === 'normal' && isOtherDestination
						? (otherDestinationText?.value.trim() || '')
						: null),
				contact_person: registerType === 'contractor'
					? (contactPerson?.value.trim() || '')
					: null,
				office_ids: registerType === 'enrollee'
					? selectedOfficeIds.map((value) => Number(value)).filter((value) => Number.isInteger(value) && value > 0)
					: selectedOfficeIds.map((value) => Number(value)).filter((value) => Number.isInteger(value) && value > 0),
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
			syncRegionFromProvince();
			if (visitorPhoneNumber && existingVisitor.contact_no) {
				const digits = String(existingVisitor.contact_no).replace(/\D/g, '');
				visitorPhoneNumber.value = isSelfRegistrationKiosk
					? digits.replace(/^0+/, '').slice(0, 10)
					: digits.slice(0, 11);
			}
			// pass_number is per-visit; leave blank so a new ID pass can be entered
			if (visitorIdPassNumber) {
				visitorIdPassNumber.value = '';
			}
			// control_number is per-visit; leave blank so a new one is generated
			if (visitorControlNumber) {
				visitorControlNumber.value = '';
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
			const unfinished = existingVisitor.unfinished_enrollee && existingVisitor.unfinished_enrollee.has_unfinished
				? existingVisitor.unfinished_enrollee
				: null;

			if (existingVisitorModalTitle) {
				existingVisitorModalTitle.textContent = unfinished
					? 'Returning Enrollee Found'
					: 'Existing Visitor Found';
			}
			if (existingVisitorModalSubtitle) {
				existingVisitorModalSubtitle.textContent = unfinished
					? 'This enrollee has unfinished office steps. Confirm to resume the same enrollment progress on the new QR pass.'
					: 'We found a matching visitor record. Please confirm whether this is the same person before continuing. Tap Cancel to create a new visitor record instead.';
			}
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
			if (existingVisitorModalProgressRow && existingVisitorModalProgress) {
				if (unfinished) {
					const completed = Number(unfinished.completed_steps || 0);
					const total = Number(unfinished.total_steps || 0);
					const currentOffice = String(unfinished.current_office || '').trim();
					existingVisitorModalProgress.textContent = currentOffice
						? `${completed}/${total} done · Next: ${currentOffice}`
						: `${completed}/${total} steps completed`;
					existingVisitorModalProgressRow.classList.remove('is-hidden');
				} else {
					existingVisitorModalProgress.textContent = '-';
					existingVisitorModalProgressRow.classList.add('is-hidden');
				}
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
				existingVisitorModalConfirm.textContent = unfinished
					? 'Yes, Resume Enrollment'
					: 'Yes, Continue';
				existingVisitorModalConfirm.title = unfinished
					? 'Resume unfinished enrollment progress on the new QR.'
					: (hasPreviewPhoto
						? 'Continue after visually validating the saved photo.'
						: 'Continue with limited validation because no saved photo is available.');
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
			const saveResult = await saveNormalVisitorRegistration(qrMeta);

			hasSavedRegistration = true;
			const resumedEnrollment = Boolean(saveResult?.data?.resumed_enrollment);
			ticketSaveStatus.textContent = resumedEnrollment
				? 'Enrollment resumed. Previous office progress was carried over to this QR.'
				: 'Visitor details saved successfully.';
			ticketSaveStatus.classList.remove('error');
			loadingText.textContent = resumedEnrollment
				? 'QR generated and unfinished enrollment progress resumed.'
				: 'QR generated and visitor saved successfully.';
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
						const unfinished = existingVisitorMatch?.unfinished_enrollee?.has_unfinished;
						cameraStatus.textContent = unfinished
							? 'Returning enrollee confirmed. Generate the QR ticket to resume unfinished enrollment progress.'
							: 'Existing visitor confirmed. Review the details and generate the QR ticket.';
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
			if (!visitorStepPanel && !enrolleeStepPanel) {
				console.warn('No step panel found for autofill');
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

		flipCameraBtn?.addEventListener('click', () => {
			if (
				flipCameraBtn.disabled ||
				!activeStream ||
				!loadingOverlay.classList.contains('is-hidden')
			) {
				return;
			}

			flipCamera();
		});

		downloadQrBtn?.addEventListener('click', async () => {
			const captureTarget = ticketCapture || registrationTicketCard;
			if (!captureTarget) {
				alert('Ticket is not ready yet.');
				return;
			}

			if (typeof html2canvas === 'undefined') {
				alert('Ticket download tool is not ready. Please refresh and try again.');
				return;
			}

			const labelEl = downloadQrBtn.querySelector('span:last-child');
			const originalLabel = labelEl?.textContent || 'Download';

			try {
				downloadQrBtn.disabled = true;
				if (labelEl) {
					labelEl.textContent = 'Preparing...';
				}

				const canvas = await html2canvas(captureTarget, {
					backgroundColor: '#f3f6fc',
					scale: 2,
					useCORS: true,
					allowTaint: false,
				});

				const link = document.createElement('a');
				const controlNo = (ticketControlNumber?.textContent || 'visitor-ticket').trim();
				link.href = canvas.toDataURL('image/png');
				link.download = `NU-Secure-QR-Ticket-${controlNo.replace(/[^A-Za-z0-9\-]/g, '-')}.png`;
				link.style.display = 'none';
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);

				if (labelEl) {
					labelEl.textContent = originalLabel;
				}
				downloadQrBtn.disabled = false;
			} catch (error) {
				console.error('Failed to download full ticket image:', error);
				alert('Failed to download ticket image. Please try again.');
				downloadQrBtn.disabled = false;
				if (labelEl) {
					labelEl.textContent = originalLabel;
				}
			}
		});

		const buildThermalQrDataUrl = (payload) => {
			if (!payload || typeof QRCode === 'undefined') {
				return '';
			}

			const holder = document.createElement('div');
			holder.setAttribute('aria-hidden', 'true');
			holder.className = 'qr-print-holder';
			document.body.appendChild(holder);

			try {
				if (!renderQrIntoElement(holder, payload, QR_PRINT_SIZE, QRCode.CorrectLevel.H)) {
					return '';
				}

				const c = holder.querySelector('canvas');
				if (c) {
					try {
						return c.toDataURL('image/png');
					} catch (err) {
						console.error('Thermal QR canvas export failed:', err);
					}
				}
			} catch (err) {
				console.error('Thermal QR generation failed:', err);
			} finally {
				holder.remove();
			}

			return '';
		};

		const printTicketInNewWindow = () => {
			if (!registrationTicketCard) {
				alert('Ticket is not ready to print yet.');
				return;
			}

			// Preview window only — do not tie @page to mm on Windows; many drivers mis-handle it and shift content.
			const thermalPreviewPx = 380;
			const isAndroid = /Android/i.test(navigator.userAgent);

			const esc = (t) => String(t ?? '')
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;');

			const escAttr = (t) => String(t ?? '')
				.replace(/&/g, '&amp;')
				.replace(/"/g, '&quot;');

			let qrSrc = buildThermalQrDataUrl(lastTicketQrPayload);

			if (!qrSrc) {
				const qrCanvas = qrCodeContainer?.querySelector('canvas');
				const qrImgEl = qrCodeContainer?.querySelector('img');
				if (qrCanvas) {
					try {
						qrSrc = qrCanvas.toDataURL('image/png');
					} catch (err) {
						console.error('QR canvas export failed:', err);
					}
				}
				if (!qrSrc && qrImgEl?.src) {
					qrSrc = qrImgEl.src;
				}
			}

			if (!qrSrc) {
				alert('QR is not ready to print yet. Wait for the code to appear, then try again.');
				return;
			}

			const rawName = (ticketVisitorName?.textContent || '-').trim();
			const rawDest = (ticketDestination?.textContent || '-').trim();
			const rawNameUpper = rawName.toUpperCase();

			const formatNameForPrint = (name) => {
				const u = name.trim().toUpperCase();
				if (!u || u === '-') {
					return esc(u || '-');
				}
				const parts = u.split(/\s+/).filter(Boolean);
				if (parts.length <= 1) {
					return esc(u);
				}
				if (u.length <= 22) {
					return esc(u);
				}
				const mid = Math.ceil(parts.length / 2);
				const line1 = parts.slice(0, mid).join(' ');
				const line2 = parts.slice(mid).join(' ');
				return `${esc(line1)}<br/>${esc(line2)}`;
			};

			const formatDestForPrint = (dest) => {
				const t = dest.trim();
				if (!t || t === '-') {
					return esc(t || '-');
				}
				if (t.length <= 18) {
					return esc(t);
				}
				const parts = t.split(/\s+/).filter(Boolean);
				if (parts.length <= 1) {
					return esc(t);
				}
				const mid = Math.ceil(parts.length / 2);
				return `${esc(parts.slice(0, mid).join(' '))}<br/>${esc(parts.slice(mid).join(' '))}`;
			};

			const nameHtml = formatNameForPrint(rawName);
			const destHtml = formatDestForPrint(rawDest);

			const nameSizeClass = isAndroid ? '' : (rawNameUpper.length > 30 ? 'txt-tiny' : (rawNameUpper.length > 18 ? 'txt-small' : ''));
			const destSizeClass = isAndroid ? '' : (rawDest.length > 24 ? 'txt-tiny' : (rawDest.length > 16 ? 'txt-small' : ''));
			const controlNoPrint = esc((ticketControlNumber?.textContent || '-').trim());
			const pageCss = isAndroid
				? '@@page { size: 58mm auto; margin: 0; }'
				: '@@page { size: auto; margin: 2mm 5mm 0 1.5mm; }';
			const viewportContent = isAndroid
				? 'width=220, initial-scale=1'
				: 'width=device-width, initial-scale=1';

			const THERMAL_LINE_WIDTH = isAndroid ? 32 : 28;
			const dashedLineText = '-'.repeat(THERMAL_LINE_WIDTH);

			const printDoc = `<!DOCTYPE html>
<html lang="en" class="${isAndroid ? 'android-thermal-print' : ''}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="${viewportContent}">
<title>Visitor ticket</title>
<style nonce="{{ $cspNonce }}">
/* size: auto avoids Chrome centering a narrow mm box on Letter/A4 (causes right-shift on thermal). */
/* Asymmetric margins: mas malaki sa kanan — maraming thermal na mas kinakain ang kanan. */
/* Android uses 58mm auto so Chrome does not layout as A4 and shrink the ticket. */
${pageCss}
* {
	box-sizing: border-box;
}
html {
	width: 100%;
	margin: 0;
	padding: 0;
}
html, body {
	background: #fff;
	color: #000;
	-webkit-print-color-adjust: exact;
	print-color-adjust: exact;
}
body {
	width: 100%;
	max-width: 100%;
	margin: 0;
	padding: 0;
	font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
	line-height: 1.3;
	text-align: left;
}
.receipt {
	width: 100%;
	max-width: 100%;
	margin: 0;
	padding: 1mm 5mm 0 1mm;
	text-align: left;
}
.receipt-title {
	margin: 0 0 0.45em;
	padding: 0;
	text-align: center;
	font-size: 11pt;
	font-weight: 800;
	letter-spacing: 0.04em;
	text-transform: uppercase;
	line-height: 1.2;
}
.dash-line {
	margin: 0 0 0.95em;
	padding: 0;
	text-align: center;
	font-family: ui-monospace, Consolas, "Liberation Mono", monospace;
	font-size: 8pt;
	font-weight: 400;
	line-height: 1;
	letter-spacing: 0;
	white-space: nowrap;
	overflow: hidden;
}
.fields {
	margin: 0;
	padding: 0;
}
.field-label {
	margin: 0 0 0.12em;
	padding: 0;
	font-size: 8pt;
	font-weight: 800;
	letter-spacing: 0.04em;
	text-transform: uppercase;
}
.field-value {
	margin: 0;
	padding: 0;
	font-size: 10pt;
	font-weight: 600;
	line-height: 1.28;
	white-space: normal;
	word-wrap: break-word;
	overflow-wrap: anywhere;
}
.field-value.name-val {
	text-transform: uppercase;
	font-size: 8pt;
	line-height: 1.2;
	margin-bottom: 0.95em;
}
.field-value.dest-val {
	text-transform: none;
	font-weight: 600;
	margin-bottom: 1.05em;
}
.field-value.name-val.txt-small { font-size: 7.1pt; line-height: 1.18; }
.field-value.name-val.txt-tiny { font-size: 6.35pt; line-height: 1.15; }
.field-value.dest-val.txt-small { font-size: 9pt; }
.field-value.dest-val.txt-tiny { font-size: 8pt; line-height: 1.22; }
.qr-wrap {
	margin: 0 auto;
	width: 100%;
	text-align: center;
	padding: 0.15em 0 0.35em;
}
.qr-table {
	width: 100%;
	border-collapse: collapse;
	margin: 0 auto;
}
.qr-table td {
	padding: 2mm 3mm 2mm 1mm;
	text-align: center;
	vertical-align: middle;
}
.qr-img {
	display: block;
	margin: 0 auto;
	width: 34mm;
	max-width: 86%;
	height: auto;
	aspect-ratio: 1 / 1;
	object-fit: contain;
	image-rendering: pixelated;
	image-rendering: crisp-edges;
}
.control-label {
	margin: 0.35em 0 0.1em;
	padding: 0;
	text-align: center;
	font-size: 7pt;
	font-weight: 800;
	letter-spacing: 0.07em;
	text-transform: uppercase;
}
.control-value {
	margin: 0;
	padding: 0;
	text-align: center;
	font-size: 9pt;
	font-weight: 700;
	letter-spacing: 0.03em;
	line-height: 1.2;
	word-wrap: break-word;
	overflow-wrap: anywhere;
}
.foot {
	margin: 2mm 0 0;
	padding: 0;
	font-size: 8pt;
	font-weight: 500;
	text-align: center;
	line-height: 1.15;
}
@@media print {
	body {
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
		height: auto !important;
		min-height: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
	}
	html {
		height: auto !important;
		min-height: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
	}
	.receipt {
		padding-bottom: 0 !important;
		margin-bottom: 0 !important;
	}
	p.foot {
		margin-bottom: 0 !important;
		padding-bottom: 0 !important;
	}
}
/* Android 58mm thermal only. Windows keeps the rules above unchanged. */
body.android-thermal-print {
	width: 58mm;
	max-width: 58mm;
	margin: 0 auto;
	padding: 0;
	-webkit-text-size-adjust: 100%;
	text-size-adjust: 100%;
	transform: none;
	zoom: 1;
}
html.android-thermal-print {
	width: 58mm;
	max-width: 58mm;
	margin: 0;
	padding: 0;
	-webkit-text-size-adjust: 100%;
	text-size-adjust: 100%;
}
body.android-thermal-print .receipt {
	width: 52mm;
	max-width: 52mm;
	box-sizing: border-box;
	margin: 0 auto;
	padding: 2mm;
	text-align: left;
}
body.android-thermal-print .receipt-title {
	font-size: 16pt;
	font-weight: 900;
	text-align: center;
	letter-spacing: 1px;
	line-height: 1.2;
	margin: 0 0 0.35em;
}
body.android-thermal-print .dash-line {
	font-size: 9pt;
	margin: 0 0 0.7em;
}
body.android-thermal-print .field-label {
	font-size: 11pt;
	font-weight: 800;
	letter-spacing: 0.03em;
}
body.android-thermal-print .field-value.name-val,
body.android-thermal-print .field-value.name-val.txt-small,
body.android-thermal-print .field-value.name-val.txt-tiny {
	font-size: 12pt;
	font-weight: 700;
	line-height: 1.25;
	margin-bottom: 0.7em;
}
body.android-thermal-print .field-value.dest-val,
body.android-thermal-print .field-value.dest-val.txt-small,
body.android-thermal-print .field-value.dest-val.txt-tiny {
	font-size: 12pt;
	font-weight: 700;
	line-height: 1.25;
	margin-bottom: 0.75em;
}
body.android-thermal-print .qr-wrap {
	width: 100%;
	max-width: none;
	text-align: center;
}
body.android-thermal-print .qr-table td {
	padding: 2mm 0;
	text-align: center;
}
body.android-thermal-print .qr-img {
	display: block;
	margin: 0 auto;
	width: 40mm !important;
	height: 40mm !important;
	max-width: none !important;
	max-height: none !important;
	aspect-ratio: 1 / 1;
	object-fit: contain;
	image-rendering: pixelated;
	image-rendering: crisp-edges;
}
body.android-thermal-print .control-label {
	font-size: 12pt;
	font-weight: 900;
	letter-spacing: 0.06em;
	margin: 0.4em 0 0.12em;
}
body.android-thermal-print .control-value {
	font-size: 16pt;
	font-weight: 900;
	letter-spacing: 0.02em;
	line-height: 1.2;
}
body.android-thermal-print .foot {
	font-size: 10.5pt;
	font-weight: 500;
	text-align: center;
	margin: 2.5mm 0 0;
}
@@media print {
	html.android-thermal-print,
	body.android-thermal-print {
		width: 58mm !important;
		max-width: 58mm !important;
		margin: 0 auto !important;
		padding: 0 !important;
		transform: none !important;
		zoom: 1 !important;
		-webkit-text-size-adjust: 100% !important;
		text-size-adjust: 100% !important;
	}
	body.android-thermal-print .receipt {
		width: 52mm !important;
		max-width: 52mm !important;
		margin: 0 auto !important;
		padding: 2mm !important;
		transform: none !important;
	}
	body.android-thermal-print .receipt-title {
		font-size: 16pt !important;
		font-weight: 900 !important;
		letter-spacing: 1px !important;
	}
	body.android-thermal-print .field-label {
		font-size: 11pt !important;
		font-weight: 800 !important;
	}
	body.android-thermal-print .field-value.name-val,
	body.android-thermal-print .field-value.name-val.txt-small,
	body.android-thermal-print .field-value.name-val.txt-tiny,
	body.android-thermal-print .field-value.dest-val,
	body.android-thermal-print .field-value.dest-val.txt-small,
	body.android-thermal-print .field-value.dest-val.txt-tiny {
		font-size: 12pt !important;
	}
	body.android-thermal-print .qr-img {
		width: 40mm !important;
		height: 40mm !important;
		max-width: none !important;
		max-height: none !important;
	}
	body.android-thermal-print .control-label {
		font-size: 12pt !important;
		font-weight: 900 !important;
	}
	body.android-thermal-print .control-value {
		font-size: 16pt !important;
		font-weight: 900 !important;
	}
	body.android-thermal-print .foot {
		font-size: 10.5pt !important;
	}
}
</style>
</head>
<body class="${isAndroid ? 'android-thermal-print' : ''}">
<div class="receipt">
	<p class="receipt-title">VISITOR QR PASS</p>
	<p class="dash-line" aria-hidden="true">${dashedLineText}</p>
	<div class="fields">
		<p class="field-label">NAME:</p>
		<p class="field-value name-val ${nameSizeClass}">${nameHtml}</p>
		<p class="field-label">DESTINATION:</p>
		<p class="field-value dest-val ${destSizeClass}">${destHtml}</p>
	</div>
	<div class="qr-wrap">
		<table class="qr-table" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
			<tr><td align="center">
				<img class="qr-img" src="${escAttr(qrSrc)}" width="400" height="400" alt="">
			</td></tr>
		</table>
		<p class="control-label">CONTROL NUMBER</p>
		<p class="control-value">${controlNoPrint}</p>
	</div>
	<p class="foot">Please present this ticket.</p>
</div>
</body>
</html>`;

			const printWindow = window.open('', '_blank', `width=${thermalPreviewPx},height=720`);

			if (!printWindow) {
				alert('Unable to open print window. Please allow pop-ups for this site.');
				return;
			}

			printWindow.document.open();
			printWindow.document.write(printDoc);
			printWindow.document.close();

			let resetHandled = false;
			const finalizeAfterPrint = () => {
				if (resetHandled) {
					return;
				}

				resetHandled = true;
				window.removeEventListener('focus', fallbackOnFocus);

				/* Close print tab after dialog; short delay avoids a jarring flash. Registration stays on the ticket screen. */
				setTimeout(() => {
					if (!printWindow.closed) {
						printWindow.close();
					}
				}, 650);
			};

			const fallbackOnFocus = () => {
				setTimeout(() => {
					if (!resetHandled) {
						finalizeAfterPrint();
					}
				}, 200);
			};

			const tryPrint = () => {
				if (isAndroid) {
					printWindow.document.documentElement.classList.add('android-thermal-print');
					printWindow.document.body.classList.add('android-thermal-print');
				}

				printWindow.onafterprint = finalizeAfterPrint;
				window.addEventListener('focus', fallbackOnFocus, { once: true });
				printWindow.focus();
				printWindow.print();

				/* Dedicated print window is closed after printing; do not strip Android
				   styles after 1s or the print dialog may lose them mid-preview. */
			};

			if (printWindow.document.readyState === 'complete') {
				setTimeout(tryPrint, 80);
			} else {
				printWindow.addEventListener('load', () => setTimeout(tryPrint, 80), { once: true });
			}
		};

		printTicketBtn?.addEventListener('click', () => {
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

		document.getElementById('kioskVerifyBackBtn')?.addEventListener('click', () => {
			if (hasSavedRegistration) {
				return;
			}

			currentStep = 1;
			preferredFacingMode = 'environment';
			updateStepUI();
			startCamera();
		});

		document.getElementById('kioskVerifyBackBtnMobile')?.addEventListener('click', () => {
			document.getElementById('kioskVerifyBackBtn')?.click();
		});

		document.getElementById('generateQrBtnMobile')?.addEventListener('click', () => {
			generateQrBtn?.click();
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
			const expectedPhoneLength = isSelfRegistrationKiosk ? 10 : 11;
			if (visitorPhoneNumber && cleanPhone.length !== expectedPhoneLength) {
				visitorPhoneNumber.focus();
				alert(isSelfRegistrationKiosk
					? 'Phone Number must be exactly 10 digits (without the leading zero).'
					: 'Phone Number must be exactly 11 digits.');
				return;
			}

			if (registerType === 'contractor') {
				if (!destinationOfficeText?.value.trim()) {
					destinationOfficeText?.focus();
					alert('Please enter Destination Office.');
					return;
				}
			} else if (registerType === 'normal') {
				syncDestinationSelection();
				const hasOffice = selectedOfficeIds.length > 0;
				const hasOther = isOtherDestination;

				if (!hasOffice && !hasOther) {
					destinationOffice?.scrollIntoView({ behavior: 'smooth', block: 'center' });
					alert('Please select at least one office to visit.');
					return;
				}

				if (hasOther) {
					const customDestination = (otherDestinationText?.value || '').trim();
					if (!customDestination) {
						showOtherDestinationField();
						otherDestinationText?.focus();
						alert('Please specify the office or destination you want to visit.');
						return;
					}
				}
			} else if (registerType === 'enrollee' && !selectedOfficeIds.length) {
				destinationOffice?.scrollIntoView({ behavior: 'smooth', block: 'center' });
				alert('No enrollee destination offices are available. Please check enrollee steps setup.');
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
				preferredFacingMode = 'user';
				updateStepUI();
				clearFrozenFrame();
				cameraStatus.textContent = 'Proceed to final step: capture your face with ID.';
				startCamera();
				return;
			}

			currentStep = 3;
			preferredFacingMode = 'user';
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
			let digitsOnly = visitorPhoneNumber.value.replace(/\D/g, '');
			if (isSelfRegistrationKiosk) {
				digitsOnly = digitsOnly.replace(/^0+/, '').slice(0, 10);
			} else {
				digitsOnly = digitsOnly.slice(0, 11);
			}
			if (visitorPhoneNumber.value !== digitsOnly) {
				visitorPhoneNumber.value = digitsOnly;
			}
		});

		visitorProvince?.addEventListener('input', syncRegionFromProvince);
		visitorProvince?.addEventListener('change', syncRegionFromProvince);

		const createKioskOfficeChoice = (officeId, officeName, subtitle = 'Tap to select') => {
			const choice = document.createElement('div');
			choice.className = 'kiosk-office-choice';
			if (officeId === OTHERS_OFFICE_VALUE) {
				choice.classList.add('is-others');
			}

			const inputId = officeId === OTHERS_OFFICE_VALUE
				? 'kiosk_office_others'
				: `kiosk_office_${officeId}`;
			const input = document.createElement('input');
			input.type = 'checkbox';
			input.id = inputId;
			input.name = officeId === OTHERS_OFFICE_VALUE ? 'destination_office_others' : 'destination_office_ids[]';
			input.className = 'office-checkbox';
			input.value = String(officeId);
			input.addEventListener('change', syncDestinationSelection);

			const label = document.createElement('label');
			label.htmlFor = inputId;
			label.tabIndex = 0;
			bindOfficeOptionKeyboard(label, input);

			const indicator = document.createElement('span');
			indicator.className = 'checkbox-indicator';
			indicator.setAttribute('aria-hidden', 'true');

			const details = document.createElement('span');
			details.className = 'office-details';
			const strong = document.createElement('strong');
			strong.textContent = officeName;
			const small = document.createElement('small');
			small.textContent = subtitle;
			details.appendChild(strong);
			details.appendChild(small);

			const check = document.createElement('span');
			check.className = 'office-check';
			check.setAttribute('aria-hidden', 'true');
			check.innerHTML = '<i class="bi bi-check-lg"></i>';

			label.appendChild(indicator);
			label.appendChild(details);
			label.appendChild(check);

			choice.appendChild(input);
			choice.appendChild(label);

			return choice;
		};

		const renderOfficeList = (offices) => {
			autoEnrolleeOfficeNames = [];
			isOtherDestination = false;
			selectedOfficeId = null;
			hideAndClearOtherDestination();

			const selectAllInput = getSelectAllInput();
			if (selectAllInput) {
				selectAllInput.checked = false;
				selectAllInput.indeterminate = false;
			}

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

			const appendGuardOfficeOption = (value, text) => {
				const label = document.createElement('label');
				label.className = 'office-option';
				label.tabIndex = 0;

				const input = document.createElement('input');
				input.type = 'checkbox';
				input.name = value === OTHERS_OFFICE_VALUE ? 'destination_office_others' : 'destination_office_ids[]';
				input.className = 'office-checkbox';
				input.value = String(value);
				input.addEventListener('change', syncDestinationSelection);
				bindOfficeOptionKeyboard(label, input);

				label.appendChild(input);
				label.appendChild(document.createTextNode(text));
				destinationOffice.appendChild(label);
			};

			if (isSelfRegistrationKiosk) {
				normalizedOffices.forEach((office) => {
					destinationOffice.appendChild(createKioskOfficeChoice(office.office_id, office.office_name));
				});
				destinationOffice.appendChild(createKioskOfficeChoice(OTHERS_OFFICE_VALUE, 'Others', 'Tap to enter destination'));
			} else {
				normalizedOffices.forEach((office) => {
					appendGuardOfficeOption(office.office_id, office.office_name);
				});
				appendGuardOfficeOption(OTHERS_OFFICE_VALUE, 'Others — Tap to enter destination');
			}

			selectedOfficeIds = [];
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
				selectedOfficeId = null;
				isOtherDestination = false;
				hideAndClearOtherDestination();
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
			preferredFacingMode = 'environment';
			capturedPictureData = '';
			faceIdCapturePublicPath = '';
			faceIdCapturePreviewUrl = '';
			hasSavedRegistration = false;
			lastTicketQrPayload = '';
			existingVisitorMatch = null;
			existingVisitorConfirmed = false;
			selectedOfficeIds = [];
			selectedOfficeId = null;
			isOtherDestination = false;
			hideAndClearOtherDestination();
			autoEnrolleeOfficeNames = [];

			const selectAllInput = getSelectAllInput();
			if (selectAllInput) {
				selectAllInput.checked = false;
				selectAllInput.indeterminate = false;
			}

			const resetPanel = registerType === 'enrollee' ? enrolleeStepPanel : visitorStepPanel;
			if (resetPanel) {
				const fields = resetPanel.querySelectorAll('input, textarea');
				fields.forEach((field) => {
					if (field.type === 'checkbox' || field.type === 'radio') {
						field.checked = false;
						field.indeterminate = false;
						return;
					}

					field.value = '';
				});
			}

			if (qrCodeContainer) {
				qrCodeContainer.innerHTML = '';
			}

			if (ticketRouteList) {
				ticketRouteList.innerHTML = '';
			}

			if (ticketControlNumber) ticketControlNumber.textContent = '-';
			if (ticketVisitorName) ticketVisitorName.textContent = '-';
			if (ticketPassNumber) ticketPassNumber.textContent = '-';
			if (ticketPurpose) ticketPurpose.textContent = '-';
			if (ticketDestination) ticketDestination.textContent = '-';

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

		newVisitorAfterTicketBtn?.addEventListener('click', (event) => {
			bypassNativeBeforeUnloadPrompt = true;

			if (isSelfRegistrationKiosk) {
				event.preventDefault();
				window.location.href = '/guard/register';
			}
		});

		window.addEventListener('afterprint', () => {
			document.body.classList.remove('print-ticket-mode');
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
			syncRegionFromProvince();
			getSelectAllInput()?.addEventListener('change', syncDestinationSelection);
			if (registerType !== 'contractor') {
				fetchOffices();
			}
			startCamera();
		}
	</script>
</body>
</html>