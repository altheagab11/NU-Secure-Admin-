<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Exit Scan</title>
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
			background: #f7f8ff;
			padding: 24px 32px;
			overflow-y: auto;
			margin-left: 260px;
		}

		.exit-content {
			min-height: calc(100vh - 48px);
			max-width: 720px;
			margin: 0 auto;
			display: flex;
			flex-direction: column;
			justify-content: center;
			padding: 18px 0;
		}

		.page-title {
			margin: 0;
			font-size: 28px;
			font-weight: 700;
			color: #0f172a;
			text-align: center;
		}

		.page-subtitle {
			margin: 6px 0 16px;
			font-size: 14px;
			color: #475569;
			text-align: center;
		}

		.exit-panel {
			width: 100%;
			background: #ffffff;
			border: 1px solid #d9dde4;
			border-radius: 14px;
			padding: 16px;
			box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
		}

		.scanner-zone {
			position: relative;
			overflow: hidden;
			background: #d9dee6;
			border-radius: 10px;
			padding: 50px 32px;
			display: flex;
			justify-content: center;
			align-items: center;
			min-height: 380px;
		}

		.camera-feed {
			position: absolute;
			inset: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: none;
			background: #cbd2dc;
		}

		.scanner-zone.camera-on .camera-feed {
			display: block;
		}

		.scanner-overlay {
			position: relative;
			z-index: 2;
			width: 100%;
			height: 100%;
			display: flex;
			justify-content: center;
			align-items: center;
		}

		.qr-guide {
			width: 250px;
			height: 250px;
			position: relative;
			border-radius: 16px;
			background: transparent;
		}

		.qr-guide .corner {
			position: absolute;
			width: 44px;
			height: 44px;
			border-color: #3f4a9f;
			border-style: solid;
			pointer-events: none;
		}

		.qr-guide .corner.tl {
			top: 0;
			left: 0;
			border-width: 5px 0 0 5px;
			border-top-left-radius: 16px;
		}

		.qr-guide .corner.tr {
			top: 0;
			right: 0;
			border-width: 5px 5px 0 0;
			border-top-right-radius: 16px;
		}

		.qr-guide .corner.bl {
			bottom: 0;
			left: 0;
			border-width: 0 0 5px 5px;
			border-bottom-left-radius: 16px;
		}

		.qr-guide .corner.br {
			bottom: 0;
			right: 0;
			border-width: 0 5px 5px 0;
			border-bottom-right-radius: 16px;
		}

		.camera-status {
			margin: 10px 0 0;
			font-size: 14px;
			color: #475569;
			text-align: center;
		}

		.scan-result {
			margin: 10px 0 0;
			font-size: 14px;
			text-align: center;
			min-height: 20px;
		}

		.scan-result.success {
			color: #15803d;
		}

		.scan-result.error {
			color: #dc2626;
		}

		.scan-button {
			width: min(100%, 380px);
			margin: 16px auto 4px;
			height: 56px;
			border: 0;
			border-radius: 10px;
			background: #3f4a9f;
			color: #f8faff;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			font-size: 16px;
			font-weight: 500;
			cursor: pointer;
		}

		.scan-button svg {
			width: 20px;
			height: 20px;
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

			.brand-title {
				font-size: 22px;
			}

			.exit-content {
				min-height: auto;
				max-width: none;
				padding: 0;
			}

			.exit-panel {
				padding: 10px;
			}

			.scanner-zone {
				padding: 28px 12px;
				min-height: 260px;
			}

			.qr-guide {
				width: 180px;
				height: 180px;
			}

			.scan-button {
				height: 54px;
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
			<div class="exit-content">
				<h1 class="page-title">Exit Scan</h1>
				<p class="page-subtitle">Process visitor exit</p>

				<section class="exit-panel" aria-label="Exit Scanner">
					<div class="scanner-zone">
						<video id="cameraFeed" class="camera-feed" autoplay playsinline muted></video>
						<div class="scanner-overlay">
							<div class="qr-guide" aria-hidden="true">
								<span class="corner tl"></span>
								<span class="corner tr"></span>
								<span class="corner bl"></span>
								<span class="corner br"></span>
							</div>
						</div>
					</div>
					<p class="camera-status" id="cameraStatus">Starting camera...</p>
					<p class="scan-result" id="scanResult" aria-live="polite"></p>

					<button type="button" class="scan-button" id="scanButton">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M4 7V4h3M17 4h3v3M4 17v3h3M20 17v3h-3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<path d="M8 8h2v2H8zM11 8h2v2h-2zM14 8h2v2h-2zM8 11h2v2H8zM12 11h1v1h-1zM14 11h2v2h-2zM8 14h2v2H8zM11 14h2v2h-2zM14 14h2v2h-2z" fill="currentColor"/>
						</svg>
						<span id="scanButtonText">Scan Exit QR</span>
					</button>
					<canvas id="scanCanvas" style="display:none;"></canvas>
				</section>
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