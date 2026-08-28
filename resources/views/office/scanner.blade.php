@php $activeNav = 'scanner'; @endphp
@extends('office.layouts.office-layout')

@section('title', 'QR Scanner')

@push('styles')
<style nonce="{{ $cspNonce }}">
	.scan-layout {
		display: grid;
		grid-template-columns: minmax(0, 1.55fr) minmax(280px, 0.85fr);
		gap: 18px;
		align-items: start;
	}
	.scanner-card {
		background: #fff;
		border: 1px solid rgba(6, 74, 159, .08);
		border-radius: 20px;
		padding: 18px;
		box-shadow: var(--shadow-soft);
	}
	.scanner-card-top {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 14px;
		flex-wrap: wrap;
		margin-bottom: 16px;
	}
	.scanner-card-top h2 {
		margin: 0 0 6px;
		font-size: 1.15rem;
		font-weight: 800;
	}
	.scanner-card-top p {
		margin: 0;
		font-size: .9rem;
		color: var(--nu-muted);
	}
	.mode-toggle {
		display: inline-flex;
		background: #eef2ff;
		border-radius: 999px;
		padding: 4px;
		gap: 2px;
	}
	.mode-toggle button {
		border: 0;
		background: transparent;
		color: #3f4db8;
		font-size: .78rem;
		font-weight: 700;
		padding: 8px 12px;
		border-radius: 999px;
		cursor: pointer;
	}
	.mode-toggle button.active {
		background: #fff;
		color: #243c96;
		box-shadow: 0 2px 8px rgba(63, 77, 184, .18);
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
		font-size: .9rem;
		color: #475569;
		line-height: 1.5;
	}
	.scanner-input {
		height: 60px;
		border: 2px solid #c7d2fe;
		border-radius: 14px;
		background: #fff;
		padding: 0 16px;
		font-size: 1.15rem;
		font-weight: 600;
		letter-spacing: .02em;
		outline: none;
		width: 100%;
		transition: .15s ease;
	}
	.scanner-input:focus {
		border-color: #3f4db8;
		box-shadow: 0 0 0 4px rgba(63, 77, 184, .15);
	}
	.scanner-helper-text { margin: 14px 0 16px; }
	.helper-normal {
		margin: 0 0 6px;
		font-size: .85rem;
		color: #3f4db8;
		font-weight: 600;
	}
	.helper-detail {
		margin: 0;
		font-size: .82rem;
		color: #64748b;
	}
	.scanner-actions {
		display: flex;
		justify-content: center;
		gap: 10px;
		flex-wrap: wrap;
	}
	.scan-btn {
		border: 1px solid #e5e7eb;
		outline: none;
		height: 46px;
		padding: 0 16px;
		border-radius: 12px;
		font-size: .88rem;
		font-weight: 600;
		display: inline-flex;
		align-items: center;
		gap: 8px;
		cursor: pointer;
		background: #fff;
		color: #111827;
		transition: .15s ease;
	}
	.scan-btn:hover { background: #f8fafc; }
	.scan-btn:disabled { opacity: .55; cursor: not-allowed; }
	.scan-btn-primary {
		background: linear-gradient(135deg, #3f4db8, #33419e);
		border-color: transparent;
		color: #fff;
	}
	.scan-btn-primary:hover { color: #fff; filter: brightness(1.05); }

	.manual-entry-wrap {
		margin-top: 12px;
		display: none;
		gap: 8px;
		justify-content: center;
		flex-wrap: wrap;
		align-items: center;
	}
	.manual-entry-wrap.show {
		display: flex;
	}
	.manual-input {
		height: 44px;
		width: 260px;
		max-width: 100%;
		flex: 0 0 auto;
		border: 1px solid #d1d5db;
		border-radius: 10px;
		padding: 0 12px;
		font-size: .9rem;
		outline: none;
		background: #fff;
	}
	.manual-input:focus {
		border-color: #3f4db8;
		box-shadow: 0 0 0 3px rgba(63, 77, 184, .12);
	}
	.manual-entry-wrap .scan-btn-primary {
		min-width: 150px;
		flex: 0 0 auto;
	}

	.camera-panel { display: none; }
	.camera-panel.active { display: block; }
	.hardware-panel { display: none; }
	.hardware-panel.active { display: block; }

	.scanner-stage {
		position: relative;
		background: #0b1b3a;
		border-radius: 18px;
		overflow: hidden;
		min-height: 320px;
	}
	#qrReader {
		width: 100%;
		min-height: 320px;
	}
	#qrReader video {
		width: 100% !important;
		border-radius: 18px;
		object-fit: cover;
	}
	.scan-frame {
		pointer-events: none;
		position: absolute;
		inset: 18%;
		border: 3px solid #F5B400;
		border-radius: 18px;
		box-shadow: 0 0 0 9999px rgba(0,0,0,.28);
		z-index: 2;
	}
	.camera-toolbar {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin-top: 12px;
	}

	.side-stack { display: flex; flex-direction: column; gap: 14px; }
	.side-card {
		background: #fff;
		border: 1px solid rgba(6, 74, 159, .08);
		border-radius: 18px;
		box-shadow: var(--shadow-soft);
		padding: 16px;
	}
	.side-card h3 {
		margin: 0 0 8px;
		font-size: 1rem;
		font-weight: 800;
	}
	.side-tip {
		display: flex;
		gap: 12px;
		align-items: flex-start;
	}
	.side-tip-icon {
		width: 40px;
		height: 40px;
		border-radius: 12px;
		background: #eef2ff;
		color: #3f4db8;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}
	.side-tip-icon.tip-success {
		background: #ecfdf3;
		color: #16a34a;
	}
	.side-tip ul {
		margin: 0;
		padding-left: 18px;
		font-size: .85rem;
		color: var(--nu-muted);
		line-height: 1.65;
	}
	.side-tip p {
		margin: 0;
		font-size: .85rem;
		color: var(--nu-muted);
		line-height: 1.55;
	}

	@media (max-width: 992px) {
		.scan-layout { grid-template-columns: 1fr; }
	}

	@include('admin.partials.table-pagination-styles')

	.side-card .table-pagination-bar {
		margin-top: 12px;
	}
</style>
@endpush

@section('content')
<div class="scan-layout">
	<div class="scanner-card">
		<div class="scanner-card-top">
			<div>
				<h2>Scan Visitor QR / Barcode</h2>
				<p id="modeSubtitle">Scan the visitor pass using the handheld scanner. The code will auto-submit when Enter is received.</p>
			</div>
			<div class="mode-toggle" role="group" aria-label="Scanner mode">
				<button type="button" id="modeHardwareBtn" class="active" data-mode="hardware">Hardware</button>
				<button type="button" id="modeCameraBtn" data-mode="camera">Camera</button>
			</div>
		</div>

		<div class="hardware-panel active" id="hardwarePanel">
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
					spellcheck="false"
					inputmode="none"
				>
			</div>

			<div class="scanner-helper-text">
				<p class="helper-normal" id="scannerStatus">Scanner input ready.</p>
				<p class="helper-detail" id="scannerHint">Scanner input is active. Press Enter or scan a real pass.</p>
			</div>

			<div class="scanner-actions">
				<button type="button" class="scan-btn" id="manualEntryBtn">
					<i class="bi bi-keyboard" aria-hidden="true"></i> Manual Entry
				</button>
				<button type="button" class="scan-btn" id="clearScanBtn">
					<i class="bi bi-eraser" aria-hidden="true"></i> Clear
				</button>
				<button type="button" class="scan-btn" id="testScannerBtn">
					<i class="bi bi-eyedropper" aria-hidden="true"></i> Test Scanner
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
				<button type="button" class="scan-btn scan-btn-primary" id="submitManualEntryBtn">
					<i class="bi bi-check-lg" aria-hidden="true"></i> Submit Code
				</button>
			</div>
		</div>

		<div class="camera-panel" id="cameraPanel">
			<div class="scanner-stage mb-2">
				<div id="qrReader" aria-label="QR camera preview"></div>
				<div class="scan-frame" aria-hidden="true"></div>
			</div>
			<div class="camera-toolbar">
				<button type="button" class="scan-btn" id="switchCameraBtn" disabled>
					<i class="bi bi-arrow-repeat" aria-hidden="true"></i> Switch Camera
				</button>
				<button type="button" class="scan-btn" id="cameraManualEntryBtn">
					<i class="bi bi-keyboard" aria-hidden="true"></i> Manual Entry
				</button>
			</div>
			<div class="manual-entry-wrap" id="cameraManualEntryWrap">
				<input
					type="text"
					class="manual-input"
					id="cameraManualQrInput"
					placeholder="QR-… / 2026-… / or full QR JSON"
					autocomplete="off"
					spellcheck="false"
				>
				<button type="button" class="scan-btn scan-btn-primary" id="cameraSubmitManualEntryBtn">
					<i class="bi bi-check-lg" aria-hidden="true"></i> Submit Code
				</button>
			</div>
			<div id="cameraError" class="alert alert-danger d-none mt-3 mb-0" role="alert"></div>
			<div class="scanner-helper-text mb-0 mt-3">
				<p class="helper-normal" id="cameraStatus">Starting camera...</p>
				<p class="helper-detail" id="cameraHint">Hold the QR steady inside the gold frame.</p>
			</div>
		</div>
	</div>

	<div class="side-stack">
		<div class="side-card">
			<div class="side-tip">
				<div class="side-tip-icon" aria-hidden="true"><i class="bi bi-info-circle"></i></div>
				<div>
					<h3>Scanning Tips</h3>
					<ul>
						<li>Use Hardware mode with a USB/Bluetooth barcode scanner.</li>
						<li>Use Camera mode if no handheld scanner is available.</li>
						<li>Scan when the visitor is done at this office.</li>
						<li>Confirm visitor details before recording check-in.</li>
					</ul>
				</div>
			</div>
		</div>

		<div class="side-card">
			<div class="side-tip">
				<div class="side-tip-icon tip-success" aria-hidden="true"><i class="bi bi-shield-check"></i></div>
				<div>
					<h3>Office Check-in</h3>
					<p>The system verifies the visitor is expected at <strong>{{ $office->office_name }}</strong> before recording the scan.</p>
				</div>
			</div>
		</div>

		<div class="side-card">
			<div class="d-flex justify-content-between align-items-center mb-2">
				<h3 class="mb-0">Today's Recent Scans</h3>
				<a href="{{ route('office.visit-history') }}" class="btn btn-sm btn-nu-outline">History</a>
			</div>
			<div id="scannerRecentScans">
			@if($recentScans->isEmpty())
				<div class="empty-state py-3">
					<i class="bi bi-qr-code" aria-hidden="true"></i>
					<p class="mb-0">No scans yet today.</p>
					<p class="card-muted mb-0">Today's QR scans at this office will appear here.</p>
				</div>
			@else
				<div class="table-scroll">
					<table class="table-office">
						<thead>
							<tr>
								<th>Visitor</th>
								<th>Time</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							@foreach($recentScans as $row)
								<tr>
									<td>
										<div class="fw-semibold">{{ $row->visitor_name }}</div>
										<div class="small text-muted">{{ $row->control_number ?: '—' }}</div>
									</td>
									<td>{{ $row->scan_time_label ?? ($row->scan_time ? \Carbon\Carbon::parse($row->scan_time)->timezone('Asia/Manila')->format('g:i A') : '—') }}</td>
									<td>
										@include('office.components.status-badge', [
											'tone' => strtolower((string) $row->validation_status),
											'label' => $row->validation_status ?: '—',
										])
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@endif
			</div>
			<div id="scannerRecentPagination">
				@include('admin.partials.table-pagination', [
					'paginator' => $recentScans,
					'perPageParam' => 'scans_per_page',
					'ariaLabel' => "Today's recent scans pagination",
				])
			</div>
		</div>
	</div>
</div>

@include('office.components.scan-result-modal')
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@include('office.partials.scan-scripts')
<script nonce="{{ $cspNonce }}">
(function () {
	const hardwarePanel = document.getElementById('hardwarePanel');
	const cameraPanel = document.getElementById('cameraPanel');
	const modeHardwareBtn = document.getElementById('modeHardwareBtn');
	const modeCameraBtn = document.getElementById('modeCameraBtn');
	const modeSubtitle = document.getElementById('modeSubtitle');
	const scannerInput = document.getElementById('scannerInput');
	const scannerStatus = document.getElementById('scannerStatus');
	const scannerHint = document.getElementById('scannerHint');
	const clearScanBtn = document.getElementById('clearScanBtn');
	const testScannerBtn = document.getElementById('testScannerBtn');
	const manualEntryBtn = document.getElementById('manualEntryBtn');
	const manualEntryWrap = document.getElementById('manualEntryWrap');
	const manualQrInput = document.getElementById('manualQrInput');
	const submitManualEntryBtn = document.getElementById('submitManualEntryBtn');
	const cameraManualEntryBtn = document.getElementById('cameraManualEntryBtn');
	const cameraManualEntryWrap = document.getElementById('cameraManualEntryWrap');
	const cameraManualQrInput = document.getElementById('cameraManualQrInput');
	const cameraSubmitManualEntryBtn = document.getElementById('cameraSubmitManualEntryBtn');
	const cameraStatus = document.getElementById('cameraStatus');
	const cameraHint = document.getElementById('cameraHint');
	const cameraError = document.getElementById('cameraError');
	const switchBtn = document.getElementById('switchCameraBtn');

	let currentMode = 'hardware';
	let html5QrCode = null;
	let cameras = [];
	let cameraIndex = 0;
	let scanningPaused = false;
	let lastScanAt = 0;
	let processing = false;
	let cameraStarting = false;

	function setHardwareStatus(status, hint) {
		if (scannerStatus) scannerStatus.textContent = status;
		if (scannerHint) scannerHint.textContent = hint || '';
	}

	function setCameraStatus(status, hint) {
		if (cameraStatus) cameraStatus.textContent = status;
		if (cameraHint) cameraHint.textContent = hint || '';
	}

	function escapeHtml(value) {
		return String(value ?? '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function scanBadgeClass(status) {
		const key = String(status || '').toLowerCase();
		const map = {
			valid: 'badge-success',
			invalid: 'badge-danger',
			unauthorized: 'badge-danger',
			success: 'badge-success',
			danger: 'badge-danger',
			warning: 'badge-warning',
		};
		if (key.includes('wrong') || key.includes('unauthor')) return 'badge-danger';
		return map[key] || 'badge-info';
	}

	function renderRecentScans(payload) {
		const wrap = document.getElementById('scannerRecentScans');
		const pagination = document.getElementById('scannerRecentPagination');
		if (!wrap) return;
		const rows = Array.isArray(payload) ? payload : (payload?.data || []);
		const meta = Array.isArray(payload) ? null : (payload?.meta || null);
		if (!Array.isArray(rows) || rows.length === 0) {
			wrap.innerHTML = `
				<div class="empty-state py-3">
					<i class="bi bi-qr-code" aria-hidden="true"></i>
					<p class="mb-0">No scans yet today.</p>
					<p class="card-muted mb-0">Today's QR scans at this office will appear here.</p>
				</div>
			`;
			if (meta) updateScannerPagination(pagination, meta);
			return;
		}
		wrap.innerHTML = `
			<div class="table-scroll">
				<table class="table-office">
					<thead>
						<tr>
							<th>Visitor</th>
							<th>Time</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						${rows.map((row) => `
							<tr>
								<td>
									<div class="fw-semibold">${escapeHtml(row.visitor_name)}</div>
									<div class="small text-muted">${escapeHtml(row.control_number)}</div>
								</td>
								<td>${escapeHtml(row.time_label)}</td>
								<td><span class="badge-status ${scanBadgeClass(row.validation_status)}">${escapeHtml(row.validation_status)}</span></td>
							</tr>
						`).join('')}
					</tbody>
				</table>
			</div>
		`;
		if (meta) updateScannerPagination(pagination, meta);
	}

	function updateScannerPagination(root, meta) {
		if (!root || !meta) return;
		const range = root.querySelector('.table-pagination-range');
		const pageLabel = root.querySelector('.table-pagination-page');
		const pageSize = root.querySelector('.table-page-size');
		const links = root.querySelectorAll('.table-pagination-nav');
		if (range) {
			range.textContent = (meta.from || 0) + ' to ' + (meta.to || 0) + ' of ' + (meta.total || 0);
		}
		if (pageLabel) {
			pageLabel.innerHTML = 'Page <strong>' + (meta.current_page || 1) + '</strong> of ' + (meta.last_page || 1);
		}
		if (pageSize && String(pageSize.value) !== String(meta.per_page)) {
			pageSize.value = String(meta.per_page);
		}
		const onFirst = (meta.current_page || 1) <= 1;
		const onLast = (meta.current_page || 1) >= (meta.last_page || 1);
		const setNav = function (link, disabled, href) {
			if (!link) return;
			link.classList.toggle('is-disabled', disabled);
			if (disabled) {
				link.setAttribute('aria-disabled', 'true');
				link.setAttribute('tabindex', '-1');
				link.setAttribute('href', '#');
			} else {
				link.removeAttribute('aria-disabled');
				link.removeAttribute('tabindex');
				if (href) link.setAttribute('href', href);
			}
		};
		setNav(links[0], onFirst, meta.first_url);
		setNav(links[1], onFirst, meta.prev_url);
		setNav(links[2], onLast, meta.next_url);
		setNav(links[3], onLast, meta.last_url);
	}

	function showCameraError(message) {
		cameraError.textContent = message;
		cameraError.classList.remove('d-none');
	}

	function clearCameraError() {
		cameraError.classList.add('d-none');
		cameraError.textContent = '';
	}

	function focusScannerInput() {
		if (currentMode !== 'hardware' || !scannerInput) return;
		if (manualEntryWrap?.classList.contains('show')) return;
		scannerInput.focus({ preventScroll: true });
	}

	function toggleManualEntry(wrap, input) {
		if (!wrap) return;
		const isShown = wrap.classList.toggle('show');
		if (isShown && input) {
			input.focus();
		}
	}

	function submitManualCode(input, wrap) {
		const code = String(input?.value || '').trim();
		if (!code) {
			if (currentMode === 'hardware') {
				setHardwareStatus('Manual entry needed.', 'Enter a QR code, control number, or pass number.');
			} else {
				setCameraStatus('Manual entry needed.', 'Enter a QR code, control number, or pass number.');
			}
			input?.focus();
			return;
		}

		if (currentMode === 'hardware') {
			setHardwareStatus('Processing manual entry...', 'Checking visitor against this office.');
		} else {
			setCameraStatus('Processing manual entry...', 'Checking visitor against this office.');
		}

		processScan(code, 'manual').then(() => {
			if (input) input.value = '';
			if (wrap) wrap.classList.remove('show');
		});
	}

	async function stopCamera() {
		try {
			if (html5QrCode && html5QrCode.isScanning) {
				await html5QrCode.stop();
				await html5QrCode.clear();
			}
		} catch (e) {}
		if (switchBtn) switchBtn.disabled = true;
		clearCameraError();
	}

	async function setMode(mode) {
		if (mode === currentMode) return;
		currentMode = mode;

		modeHardwareBtn.classList.toggle('active', mode === 'hardware');
		modeCameraBtn.classList.toggle('active', mode === 'camera');
		hardwarePanel.classList.toggle('active', mode === 'hardware');
		cameraPanel.classList.toggle('active', mode === 'camera');
		manualEntryWrap?.classList.remove('show');
		cameraManualEntryWrap?.classList.remove('show');

		if (mode === 'hardware') {
			modeSubtitle.textContent = 'Scan the visitor pass using the handheld scanner. The code will auto-submit when Enter is received.';
			await stopCamera();
			setHardwareStatus('Scanner input ready.', 'Scanner input is active. Press Enter or scan a real pass.');
			setTimeout(focusScannerInput, 50);
		} else {
			modeSubtitle.textContent = 'Use your webcam to scan the visitor QR ticket inside the gold frame.';
			setCameraStatus('Starting camera...', 'Allow camera access if the browser asks.');
			await startCamera();
		}
	}

	async function processScan(payload, method) {
		const value = String(payload || '').trim();
		if (!value || processing) return;
		processing = true;

		if (method === 'hardware') {
			setHardwareStatus('Validating scan...', 'Checking visitor against this office.');
		} else if (method === 'manual') {
			// status already set by submitManualCode / caller
		} else {
			setCameraStatus('QR detected', 'Verifying visitor details...');
		}

		try {
			await OfficeScan.verify(value, method);
		} finally {
			processing = false;
			if (scannerInput) scannerInput.value = '';
			focusScannerInput();
		}
	}

	async function onScanSuccess(decodedText) {
		const now = Date.now();
		if (scanningPaused || processing || (now - lastScanAt) < 2500) return;
		lastScanAt = now;
		scanningPaused = true;

		try {
			if (html5QrCode && html5QrCode.isScanning) {
				await html5QrCode.pause(true);
			}
		} catch (e) {}

		await processScan(decodedText, 'camera');
	}

	async function startCamera() {
		if (cameraStarting) return;
		cameraStarting = true;
		clearCameraError();
		setCameraStatus('Starting camera...', 'Allow camera access if the browser asks.');

		if (typeof Html5Qrcode === 'undefined') {
			showCameraError('QR scanner library failed to load. Please refresh the page.');
			setCameraStatus('Camera unavailable', 'Refresh the page or use Hardware / Manual Entry.');
			cameraStarting = false;
			return;
		}

		try {
			if (html5QrCode && html5QrCode.isScanning) {
				await html5QrCode.stop();
				await html5QrCode.clear();
			}

			if (!html5QrCode) {
				html5QrCode = new Html5Qrcode('qrReader');
			}
			cameras = await Html5Qrcode.getCameras();
			if (!cameras.length) {
				showCameraError('No camera found on this device.');
				setCameraStatus('No camera', 'Use Hardware mode or Manual Entry instead.');
				if (switchBtn) switchBtn.disabled = true;
				return;
			}

			cameraIndex = Math.min(cameraIndex, cameras.length - 1);
			await html5QrCode.start(
				cameras[cameraIndex].id,
				{ fps: 8, qrbox: { width: 250, height: 250 }, aspectRatio: 1.777 },
				onScanSuccess,
				() => {}
			);

			setCameraStatus('Camera active', 'Hold the QR steady inside the gold frame.');
			if (switchBtn) switchBtn.disabled = cameras.length < 2;
			scanningPaused = false;
		} catch (error) {
			const msg = String(error && error.message ? error.message : error);
			if (/NotAllowedError|Permission|denied/i.test(msg)) {
				showCameraError('Camera permission denied. Allow camera access, then switch to Camera mode again.');
				setCameraStatus('Permission denied', 'Check browser camera permissions.');
			} else {
				showCameraError('Unable to start camera: ' + msg);
				setCameraStatus('Camera error', 'Switch modes and try again, or use Hardware mode.');
			}
			if (switchBtn) switchBtn.disabled = true;
		} finally {
			cameraStarting = false;
		}
	}

	async function switchCamera() {
		if (cameras.length < 2 || cameraStarting) return;
		cameraIndex = (cameraIndex + 1) % cameras.length;
		await stopCamera();
		await startCamera();
	}

	async function resumeScanning() {
		scanningPaused = false;
		processing = false;
		if (currentMode === 'hardware') {
			setHardwareStatus('Scanner input ready.', 'Scanner input is active. Press Enter or scan a real pass.');
			focusScannerInput();
			return;
		}
		try {
			if (html5QrCode && html5QrCode.isScanning) {
				html5QrCode.resume();
			}
		} catch (e) {}
		setCameraStatus('Scanner ready', 'You can scan another QR code.');
	}

	modeHardwareBtn.addEventListener('click', () => setMode('hardware'));
	modeCameraBtn.addEventListener('click', () => setMode('camera'));

	scannerInput?.addEventListener('keydown', (event) => {
		if (event.key !== 'Enter') return;
		event.preventDefault();
		processScan(scannerInput.value, 'hardware');
	});

	manualEntryBtn?.addEventListener('click', () => toggleManualEntry(manualEntryWrap, manualQrInput));
	cameraManualEntryBtn?.addEventListener('click', () => toggleManualEntry(cameraManualEntryWrap, cameraManualQrInput));
	submitManualEntryBtn?.addEventListener('click', () => submitManualCode(manualQrInput, manualEntryWrap));
	cameraSubmitManualEntryBtn?.addEventListener('click', () => submitManualCode(cameraManualQrInput, cameraManualEntryWrap));

	manualQrInput?.addEventListener('keydown', (event) => {
		if (event.key !== 'Enter') return;
		event.preventDefault();
		submitManualCode(manualQrInput, manualEntryWrap);
	});
	cameraManualQrInput?.addEventListener('keydown', (event) => {
		if (event.key !== 'Enter') return;
		event.preventDefault();
		submitManualCode(cameraManualQrInput, cameraManualEntryWrap);
	});

	clearScanBtn?.addEventListener('click', () => {
		if (scannerInput) scannerInput.value = '';
		if (manualQrInput) manualQrInput.value = '';
		manualEntryWrap?.classList.remove('show');
		setHardwareStatus('Scanner input ready.', 'Scanner input is active. Press Enter or scan a real pass.');
		focusScannerInput();
	});

	testScannerBtn?.addEventListener('click', () => {
		if (!scannerInput) return;
		scannerInput.value = 'TEST-SCANNER-INPUT';
		setHardwareStatus('Test input loaded.', 'Press Enter to try submitting, or Clear to reset.');
		focusScannerInput();
	});

	switchBtn?.addEventListener('click', switchCamera);

	document.addEventListener('click', (event) => {
		if (currentMode !== 'hardware') return;
		const target = event.target;
		if (!(target instanceof Element)) return;
		if (target.closest('input, textarea, button, a, .modal, .mode-toggle, .manual-entry-wrap')) return;
		focusScannerInput();
	});

	OfficeScan.init({
		onSuccess: function () {
			if (currentMode === 'hardware') {
				setHardwareStatus('Check-in recorded.', 'Ready for the next scan.');
			} else {
				setCameraStatus('Check-in recorded.', 'Ready for the next scan.');
			}
		},
		onRecentScans: function (rows) {
			renderRecentScans(rows);
		},
		onResume: function () {
			resumeScanning();
		}
	});

	window.addEventListener('beforeunload', () => {
		if (html5QrCode && html5QrCode.isScanning) {
			html5QrCode.stop().catch(() => {});
		}
	});

	focusScannerInput();
})();
</script>
<script nonce="{{ $cspNonce }}">
	@include('admin.partials.table-pagination-script')
</script>
@endpush
