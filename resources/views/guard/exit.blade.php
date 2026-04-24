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

								<button type="button" class="scan-btn-secondary">
									<i class="fas fa-keyboard"></i>
									Manual Entry
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

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
	<script>
		const scannerZone = document.querySelector('.scanner-zone');
		const cameraFeed = document.getElementById('cameraFeed');
		const scanButton = document.getElementById('scanButton');
		const scanButtonText = document.getElementById('scanButtonText');
		const cameraStatus = document.getElementById('cameraStatus');
		const scanResult = document.getElementById('scanResult');
		const scanCanvas = document.getElementById('scanCanvas');
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

		window.addEventListener('beforeunload', () => {
			releaseCamera();
		});

		startCamera();
	</script>
</body>
</html>