<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Exit Scan</title>
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

		.main {
			flex: 1;
			background: #f7f8ff;
			padding: 24px 32px;
			overflow-y: auto;
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
			}

			.main {
				display: none;
			}
		}

		@media (max-width: 480px) {
			.menu-item,
			.admin-row,
			.logout-btn {
				font-size: 16px;
			}

			.brand-title span:first-child {
				font-size: 22px;
			}

			.brand-title span:last-child {
				font-size: 20px;
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
		<aside class="sidebar">
			<div class="brand-row">
				<div class="brand-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
					</svg>
				</div>
				<div>
					<p class="brand-title"><span>SVMS</span><span>Guard</span></p>
					<p class="brand-subtitle">Smart Visitor Monitoring System</p>
				</div>
			</div>

			<nav class="menu" aria-label="Sidebar Navigation">
				<a href="/guard/dashboard" class="menu-item">
					<span class="inner">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
							<rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
							<rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
							<rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
						</svg>
						Dashboard
					</span>
				</a>

				<div class="menu-group" id="registerMenuGroup">
					<button type="button" class="menu-item menu-toggle" id="registerMenuToggle" aria-expanded="false" aria-controls="registerSubmenu">
						<span class="inner">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
								<path d="M5 5h5M7.5 2.5v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							Register
						</span>
						<svg class="caret" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
					<div class="submenu" id="registerSubmenu">
						<a href="/guard/register?type=normal" class="submenu-item">Normal Visitor</a>
						<a href="/guard/register?type=enrollee" class="submenu-item">Enrollee</a>
						<a href="/guard/register?type=contractor" class="submenu-item">Contractor</a>
					</div>
				</div>

				<a href="/guard/exit" class="menu-item active">
					<span class="inner">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M4 4h3v3M17 4h3v3M4 17h3v3M17 17h3v3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M9 8h2v2H9zM13 8h2v2h-2zM9 12h2v2H9zM13 12h2v2h-2z" fill="currentColor"/>
						</svg>
						Exit Scan
					</span>
				</a>

				<a href="/guard/alert" class="menu-item">
					<span class="inner">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 17H5.8a1 1 0 0 1-.8-1.6L7 12.7V10a5 5 0 1 1 10 0v2.7l2 2.7a1 1 0 0 1-.8 1.6H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M10 20a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
						Active Alerts
					</span>
				</a>
			</nav>

			<div class="spacer" aria-hidden="true"></div>

			<div class="bottom">
				<div class="admin-row">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.866 0-7 2.015-7 4.5V20h14v-1.5c0-2.485-3.134-4.5-7-4.5Z" fill="currentColor"/>
					</svg>
					<span>Officer Martinez</span>
				</div>

				<div class="logout-wrap">
					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<button type="submit" class="logout-btn">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 7 20 12 15 17M20 12H9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M11 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
						</svg>
						Logout
						</button>
					</form>
				</div>
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

	<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
	<script>
		const registerMenuGroup = document.getElementById('registerMenuGroup');
		const registerMenuToggle = document.getElementById('registerMenuToggle');

		if (registerMenuGroup && registerMenuToggle) {
			registerMenuToggle.addEventListener('click', () => {
				const isOpen = registerMenuGroup.classList.toggle('open');
				registerMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		}

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