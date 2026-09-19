<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>NU Lipa Enrollee Visit Tracker</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<style nonce="{{ $cspNonce }}">
		:root {
			--bg: #eef1f6;
			--card: #ffffff;
			--ink: #0f172a;
			--muted: #64748b;
			--blue: #1e3a8a;
			--blue-soft: #dbeafe;
			--blue-mid: #2563eb;
			--green: #16a34a;
			--green-soft: #dcfce7;
			--green-border: #86efac;
			--orange: #ea580c;
			--orange-soft: #ffedd5;
			--orange-border: #fdba74;
			--red: #dc2626;
			--red-soft: #fef2f2;
			--red-border: #fecaca;
			--gray: #94a3b8;
			--gray-soft: #f8fafc;
			--gray-border: #e2e8f0;
			--shadow: 0 8px 28px rgba(15, 23, 42, 0.06);
			--radius: 18px;
			font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
		}

		* { box-sizing: border-box; }

		body {
			margin: 0;
			min-height: 100vh;
			background: var(--bg);
			color: var(--ink);
		}

		.page {
			width: min(1120px, calc(100% - 32px));
			margin: 0 auto;
			padding: 22px 0 36px;
		}

		.topbar {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 16px;
			margin-bottom: 20px;
		}

		.brand {
			display: flex;
			align-items: center;
			gap: 12px;
			min-width: 0;
		}

		.brand-mark {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			background: var(--blue);
			color: #fff;
			display: grid;
			place-items: center;
			font-weight: 800;
			font-size: 15px;
			letter-spacing: 0.02em;
			flex-shrink: 0;
		}

		.brand h1 {
			margin: 0;
			font-size: clamp(1.05rem, 2.2vw, 1.35rem);
			font-weight: 800;
			color: var(--blue);
			line-height: 1.2;
		}

		.brand p {
			margin: 3px 0 0;
			font-size: 0.82rem;
			color: var(--muted);
		}

		.pass-pill {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 10px 16px;
			border-radius: 999px;
			background: var(--blue-soft);
			border: 1px solid #bfdbfe;
			color: var(--blue);
			font-size: 0.86rem;
			font-weight: 700;
			white-space: nowrap;
		}

		.pass-pill .dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: var(--blue-mid);
			box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.18);
		}

		.grid-top {
			display: grid;
			grid-template-columns: minmax(0, 1.25fr) minmax(240px, 0.85fr) minmax(220px, 0.75fr);
			gap: 18px;
			margin-bottom: 18px;
		}

		.enrollee-name-chip {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			margin-top: 8px;
			padding: 6px 12px;
			border-radius: 999px;
			background: #fff;
			border: 1px solid var(--gray-border);
			color: var(--ink);
			font-size: 0.88rem;
			font-weight: 700;
			max-width: 100%;
		}

		.enrollee-name-chip .bi {
			color: var(--blue-mid);
			font-size: 0.95rem;
			flex-shrink: 0;
		}

		.enrollee-name-chip span {
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}

		.header-meta {
			display: flex;
			flex-direction: column;
			align-items: flex-end;
			gap: 8px;
		}

		.qr-pass-card {
			display: flex;
			flex-direction: column;
			align-items: center;
			text-align: center;
			padding: 20px 18px;
		}

		.qr-pass-card h2 {
			margin: 0 0 4px;
			font-size: 1.05rem;
			font-weight: 800;
			color: var(--ink);
		}

		.qr-pass-card .qr-enrollee-name {
			margin: 0 0 14px;
			font-size: 0.95rem;
			font-weight: 700;
			color: var(--blue);
			line-height: 1.3;
			word-break: break-word;
		}

		.qr-pass-card .qr-hint {
			margin: 12px 0 0;
			font-size: 0.78rem;
			line-height: 1.4;
			color: var(--muted);
			max-width: 16rem;
		}

		.qr-frame {
			width: 180px;
			height: 180px;
			padding: 10px;
			border-radius: 16px;
			background: #fff;
			border: 1.5px solid var(--gray-border);
			box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
			display: grid;
			place-items: center;
		}

		.qr-frame #officeQrCode,
		.qr-frame #officeQrCode img,
		.qr-frame #officeQrCode canvas {
			width: 160px !important;
			height: 160px !important;
		}

		.qr-pass-code {
			margin-top: 10px;
			font-size: 0.78rem;
			font-weight: 700;
			color: var(--muted);
			letter-spacing: 0.02em;
		}

		.card {
			background: var(--card);
			border: 1px solid var(--gray-border);
			border-radius: var(--radius);
			box-shadow: var(--shadow);
			padding: 22px 24px;
		}

		.eyebrow {
			margin: 0 0 8px;
			font-size: 0.72rem;
			font-weight: 800;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: var(--blue-mid);
		}

		.card-title {
			margin: 0 0 8px;
			font-size: clamp(1.35rem, 2.5vw, 1.75rem);
			font-weight: 800;
			letter-spacing: -0.02em;
			line-height: 1.15;
			color: var(--ink);
		}

		.card-copy {
			margin: 0 0 18px;
			font-size: 0.95rem;
			line-height: 1.55;
			color: var(--muted);
			max-width: 42rem;
		}

		.progress-head {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 12px;
			margin-bottom: 8px;
			font-size: 0.86rem;
			font-weight: 600;
			color: var(--muted);
		}

		.progress-head strong {
			color: var(--ink);
		}

		.progress-track {
			height: 10px;
			border-radius: 999px;
			background: #e2e8f0;
			overflow: hidden;
			margin-bottom: 16px;
		}

		.progress-fill {
			height: 100%;
			width: {{ max(0, min(100, $percent)) }}%;
			border-radius: 999px;
			background: linear-gradient(90deg, #16a34a, #22c55e);
			transition: width 0.4s ease;
		}

		.stat-row {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 10px;
		}

		.stat-box {
			background: var(--gray-soft);
			border: 1px solid var(--gray-border);
			border-radius: 14px;
			padding: 14px 12px;
			text-align: center;
		}

		.stat-box strong {
			display: block;
			font-size: 1.35rem;
			font-weight: 800;
			color: var(--ink);
			line-height: 1.1;
		}

		.stat-box span {
			display: block;
			margin-top: 4px;
			font-size: 0.78rem;
			color: var(--muted);
			font-weight: 600;
		}

		.current-card h2 {
			margin: 0 0 14px;
			font-size: 1.05rem;
			font-weight: 800;
			color: var(--ink);
		}

		.current-panel {
			display: flex;
			gap: 14px;
			align-items: flex-start;
			padding: 16px;
			border-radius: 16px;
			background: var(--orange-soft);
			border: 1.5px solid var(--orange-border);
			margin-bottom: 12px;
		}

		.current-panel.is-complete {
			background: var(--green-soft);
			border-color: var(--green-border);
		}

		.current-num {
			width: 52px;
			height: 52px;
			border-radius: 50%;
			background: var(--orange);
			color: #fff;
			display: grid;
			place-items: center;
			font-size: 1.35rem;
			font-weight: 800;
			flex-shrink: 0;
		}

		.current-panel.is-complete .current-num {
			background: var(--green);
		}

		.current-panel h3 {
			margin: 0 0 6px;
			font-size: 1rem;
			font-weight: 800;
			line-height: 1.3;
			color: var(--ink);
		}

		.current-panel p {
			margin: 0;
			font-size: 0.86rem;
			line-height: 1.45;
			color: #9a3412;
		}

		.current-panel.is-complete p {
			color: #166534;
		}

		.wrong-office-modal {
			position: fixed;
			inset: 0;
			z-index: 1200;
			display: none;
			align-items: center;
			justify-content: center;
			padding: 20px;
			background: rgba(15, 23, 42, 0.55);
			backdrop-filter: blur(3px);
		}

		.wrong-office-modal.is-open {
			display: flex;
		}

		.wrong-office-dialog {
			width: min(420px, 100%);
			background: #fff;
			border-radius: 20px;
			border: 1.5px solid var(--red-border);
			box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
			padding: 24px 22px 20px;
			text-align: center;
			animation: wrongOfficePop 0.22s ease-out;
		}

		@keyframes wrongOfficePop {
			from {
				opacity: 0;
				transform: translateY(12px) scale(0.96);
			}
			to {
				opacity: 1;
				transform: translateY(0) scale(1);
			}
		}

		.wrong-office-dialog .wrong-icon {
			width: 64px;
			height: 64px;
			margin: 0 auto 14px;
			border-radius: 50%;
			background: var(--red);
			color: #fff;
			display: grid;
			place-items: center;
			font-size: 1.7rem;
		}

		.wrong-office-dialog h3 {
			margin: 0 0 8px;
			font-size: 1.25rem;
			font-weight: 800;
			color: #991b1b;
		}

		.wrong-office-dialog p {
			margin: 0;
			font-size: 0.92rem;
			line-height: 1.5;
			color: #9f1239;
		}

		.wrong-office-dialog .wrong-scanned {
			margin-top: 10px;
			font-weight: 700;
			color: #7f1d1d;
		}

		.wrong-office-dialog .wrong-next {
			margin-top: 14px;
			padding: 12px 14px;
			border-radius: 14px;
			background: var(--red-soft);
			border: 1px solid var(--red-border);
			color: #991b1b;
			font-size: 0.95rem;
			font-weight: 800;
			line-height: 1.4;
		}

		.wrong-office-dialog .wrong-office-ok {
			margin-top: 18px;
			width: 100%;
			border: 0;
			border-radius: 12px;
			padding: 12px 16px;
			background: var(--red);
			color: #fff;
			font-size: 0.95rem;
			font-weight: 800;
			cursor: pointer;
		}

		.wrong-office-dialog .wrong-office-ok:hover,
		.wrong-office-dialog .wrong-office-ok:focus-visible {
			background: #b91c1c;
			outline: none;
		}

		.info-box {
			display: flex;
			gap: 10px;
			align-items: flex-start;
			padding: 12px 14px;
			border-radius: 12px;
			background: #eff6ff;
			border: 1px solid #bfdbfe;
			color: #1e40af;
			font-size: 0.84rem;
			line-height: 1.45;
		}

		.info-box .bi {
			font-size: 1rem;
			margin-top: 1px;
			flex-shrink: 0;
		}

		.route-card {
			padding-top: 20px;
		}

		.route-head {
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 16px;
		}

		.route-head h2 {
			margin: 0 0 4px;
			font-size: 1.2rem;
			font-weight: 800;
		}

		.route-head p {
			margin: 0;
			font-size: 0.88rem;
			color: var(--muted);
		}

		.legend {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			font-size: 0.8rem;
			font-weight: 600;
			color: var(--muted);
		}

		.legend span {
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		.legend i {
			width: 9px;
			height: 9px;
			border-radius: 50%;
			display: inline-block;
		}

		.legend .done { background: var(--green); }
		.legend .current { background: var(--orange); }
		.legend .pending { background: var(--gray); }

		.step-list {
			display: grid;
			gap: 10px;
		}

		.step-row {
			display: flex;
			align-items: center;
			gap: 14px;
			padding: 14px 16px;
			border-radius: 14px;
			border: 1.5px solid var(--gray-border);
			background: #fff;
		}

		.step-row.done {
			background: #f0fdf4;
			border-color: var(--green-border);
		}

		.step-row.current {
			background: #fff7ed;
			border-color: var(--orange-border);
		}

		.step-row.pending {
			background: #fff;
			border-color: var(--gray-border);
		}

		.step-num {
			width: 38px;
			height: 38px;
			border-radius: 50%;
			display: grid;
			place-items: center;
			font-weight: 800;
			font-size: 0.95rem;
			flex-shrink: 0;
			color: #fff;
		}

		.step-row.done .step-num { background: var(--green); }
		.step-row.current .step-num { background: var(--orange); }
		.step-row.pending .step-num { background: #94a3b8; }

		.step-body {
			flex: 1;
			min-width: 0;
		}

		.step-body strong {
			display: block;
			font-size: 0.95rem;
			font-weight: 700;
			color: var(--ink);
			line-height: 1.3;
		}

		.step-body .step-floor {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			margin-top: 4px;
			font-size: 0.8rem;
			font-weight: 700;
			color: var(--blue-mid);
		}

		.step-body .step-floor .bi {
			font-size: 0.78rem;
		}

		.step-body span {
			display: block;
			margin-top: 3px;
			font-size: 0.8rem;
			color: var(--muted);
			line-height: 1.4;
		}

		.current-panel .current-floor {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			margin: 0 0 6px;
			font-size: 0.82rem;
			font-weight: 700;
			color: #c2410c;
		}

		.current-panel.is-complete .current-floor {
			color: #166534;
		}

		.step-badge {
			flex-shrink: 0;
			padding: 6px 12px;
			border-radius: 999px;
			font-size: 0.75rem;
			font-weight: 800;
		}

		.step-row.done .step-badge {
			background: #dcfce7;
			color: #166534;
		}

		.step-row.current .step-badge {
			background: #ffedd5;
			color: #c2410c;
		}

		.step-row.pending .step-badge {
			background: #f1f5f9;
			color: #64748b;
		}

		.footer-note {
			display: flex;
			gap: 10px;
			align-items: flex-start;
			margin-top: 14px;
			padding: 14px 16px;
			border-radius: 12px;
			background: #eff6ff;
			border: 1px solid #bfdbfe;
			color: #1e40af;
			font-size: 0.86rem;
			line-height: 1.45;
		}

		.footer-note .bi {
			flex-shrink: 0;
			margin-top: 1px;
		}

		.empty-route {
			padding: 28px 16px;
			text-align: center;
			color: var(--muted);
			border: 1px dashed var(--gray-border);
			border-radius: 14px;
		}

		@media (max-width: 1100px) {
			.grid-top {
				grid-template-columns: 1fr 1fr;
			}

			.qr-pass-card {
				grid-column: 1 / -1;
			}
		}

		@media (max-width: 900px) {
			.grid-top {
				grid-template-columns: 1fr;
			}

			.qr-pass-card {
				grid-column: auto;
			}

			.topbar {
				flex-direction: column;
				align-items: flex-start;
			}

			.header-meta {
				align-items: flex-start;
				width: 100%;
			}

			.pass-pill {
				max-width: 100%;
				white-space: normal;
			}
		}

		@media (max-width: 640px) {
			.page {
				width: min(100%, calc(100% - 20px));
				padding: 14px 0 28px;
			}

			.card {
				padding: 16px;
			}

			.stat-row {
				grid-template-columns: 1fr;
			}

			.step-row {
				flex-wrap: wrap;
			}

			.step-badge {
				margin-left: auto;
			}
		}
	</style>
</head>
<body>
	<div class="page">
		<header class="topbar">
			<div class="brand">
				<div class="brand-mark" aria-hidden="true">NU</div>
				<div>
					<h1>NU Lipa Enrollee Visit Tracker</h1>
					<p>QR browser view for enrollee progress monitoring.</p>
					<div class="enrollee-name-chip" title="{{ $visitor_name }}">
						<i class="bi bi-person-fill" aria-hidden="true"></i>
						<span>{{ $visitor_name }}</span>
					</div>
				</div>
			</div>
			<div class="header-meta">
				<div class="pass-pill">
					<span class="dot" aria-hidden="true"></span>
					<span>QR Pass Active: {{ $pass_code }}</span>
				</div>
			</div>
		</header>

		<section class="grid-top">
			<article class="card">
				<p class="eyebrow">Enrollment Route</p>
				<h2 class="card-title">Track each office step in order.</h2>
				<p class="card-copy">
					Your enrollment visit updates automatically when office staff scans your QR pass.
					Follow the route below and proceed only to your current office.
				</p>

				<div class="progress-head">
					<span>Overall progress</span>
					<strong>{{ $completed_steps }} of {{ $total_steps }} completed</strong>
				</div>
				<div class="progress-track" aria-hidden="true">
					<div class="progress-fill" id="progressFill"></div>
				</div>

				<div class="stat-row">
					<div class="stat-box">
						<strong id="statPercent">{{ $percent }}%</strong>
						<span>Completion</span>
					</div>
					<div class="stat-box">
						<strong id="statCurrent">
							@if ($is_complete)
								Done
							@elseif ($current_step)
								Step {{ $current_step['order'] }}
							@else
								—
							@endif
						</strong>
						<span>Current office</span>
					</div>
					<div class="stat-box">
						<strong id="statRemaining">{{ $remaining_steps }}</strong>
						<span>Remaining steps</span>
					</div>
				</div>
			</article>

			<article class="card current-card">
				<h2>Current Step</h2>

				@if ($is_complete)
					<div class="current-panel is-complete">
						<div class="current-num" aria-hidden="true"><i class="bi bi-check-lg"></i></div>
						<div>
							<h3>Enrollment route completed</h3>
							<p>All office steps are done. You may keep this page for reference or exit the campus when finished.</p>
						</div>
					</div>
				@elseif ($current_step)
					<div class="current-panel">
						<div class="current-num" aria-hidden="true">{{ $current_step['order'] }}</div>
						<div>
							<h3>{{ $current_step['title'] }}</h3>
							@if (!empty($current_step['floor']))
								<p class="current-floor"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i>{{ $current_step['floor'] }}</p>
							@endif
							<p>{{ $current_step['subtitle'] ?: 'Proceed to the assigned office and present your QR pass.' }}</p>
						</div>
					</div>
				@else
					<div class="current-panel">
						<div class="current-num" aria-hidden="true">—</div>
						<div>
							<h3>No active step yet</h3>
							<p>Your enrollment route will appear here once offices are assigned to your visit.</p>
						</div>
					</div>
				@endif

				<div class="info-box">
					<i class="bi bi-info-circle-fill" aria-hidden="true"></i>
					<span>Keep this page open or screenshot your QR pass. Progress refreshes when an office validates your visit.</span>
				</div>
			</article>

			<article class="card qr-pass-card">
				<h2>Office Scan QR</h2>
				<p class="qr-enrollee-name">{{ $visitor_name }}</p>
				<div class="qr-frame" aria-label="QR code for office scanning">
					<div id="officeQrCode"></div>
				</div>
				<p class="qr-pass-code">Pass: {{ $pass_code }}</p>
				<p class="qr-hint">One QR for all offices. Staff scan this from their office portal — the system detects which office scanned.</p>
			</article>
		</section>

		<section class="card route-card">
			<div class="route-head">
				<div>
					<h2>Visit Route in Order</h2>
					<p>Complete each office step as staff scan and validate your QR.</p>
				</div>
				<div class="legend" aria-label="Status legend">
					<span><i class="done"></i> Done</span>
					<span><i class="current"></i> Current</span>
					<span><i class="pending"></i> Pending</span>
				</div>
			</div>

			@if (count($steps) === 0)
				<div class="empty-route">No enrollment steps are configured for this visit yet.</div>
			@else
				<div class="step-list" id="stepList">
					@foreach ($steps as $step)
						<div class="step-row {{ $step['state'] }}">
							<div class="step-num">{{ $step['order'] }}</div>
							<div class="step-body">
								<strong>{{ $step['title'] }}</strong>
								@if (!empty($step['floor']))
									<span class="step-floor"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i>{{ $step['floor'] }}</span>
								@endif
								@if (!empty($step['subtitle']))
									<span>{{ $step['subtitle'] }}</span>
								@endif
							</div>
							<span class="step-badge">{{ $step['badge'] }}</span>
						</div>
					@endforeach
				</div>
			@endif

			<div class="footer-note">
				<i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
				<span>This page is for enrollee viewing. Office staff should scan the QR code from their assigned office portal to validate and record the step.</span>
			</div>
		</section>
	</div>

	<div
		class="wrong-office-modal"
		id="wrongOfficeModal"
		role="dialog"
		aria-modal="true"
		aria-labelledby="wrongOfficeTitle"
		aria-hidden="true"
	>
		<div class="wrong-office-dialog">
			<div class="wrong-icon" aria-hidden="true"><i class="bi bi-exclamation-triangle-fill"></i></div>
			<h3 id="wrongOfficeTitle">Wrong office</h3>
			<p class="wrong-scanned" id="wrongOfficeScanned">You were scanned at an unlisted office.</p>
			<p>That is not your current destination on the enrollment route.</p>
			<div class="wrong-next" id="wrongOfficeNext">Please proceed to your next assigned office.</div>
			<button type="button" class="wrong-office-ok" id="wrongOfficeOkBtn">Got it</button>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
	<script nonce="{{ $cspNonce }}">
		(() => {
			const officeQrPayload = @json($office_qr_payload);
			const officeQrHost = document.getElementById('officeQrCode');
			const wrongOfficeData = @json($wrong_office ?? null);
			const wrongOfficeModal = document.getElementById('wrongOfficeModal');
			const wrongOfficeScanned = document.getElementById('wrongOfficeScanned');
			const wrongOfficeNext = document.getElementById('wrongOfficeNext');
			const wrongOfficeOkBtn = document.getElementById('wrongOfficeOkBtn');
			const dismissKey = 'enrollee_wrong_office_dismissed';

			const getDismissedScanId = () => {
				try {
					return Number(sessionStorage.getItem(dismissKey) || 0);
				} catch (error) {
					return 0;
				}
			};

			const setDismissedScanId = (scanId) => {
				try {
					sessionStorage.setItem(dismissKey, String(scanId || 0));
				} catch (error) {
					// Ignore storage failures on private browsers.
				}
			};

			const closeWrongOfficeModal = () => {
				if (!wrongOfficeModal) return;
				wrongOfficeModal.classList.remove('is-open');
				wrongOfficeModal.setAttribute('aria-hidden', 'true');
				document.body.style.overflow = '';
			};

			const openWrongOfficeModal = (notice) => {
				if (!wrongOfficeModal || !notice) return;

				const scanId = Number(notice.scan_id || 0);
				if (scanId > 0 && scanId === getDismissedScanId()) {
					return;
				}

				if (wrongOfficeScanned) {
					wrongOfficeScanned.textContent = `You were scanned at ${notice.scanned_office || 'an unlisted office'}.`;
				}
				if (wrongOfficeNext) {
					wrongOfficeNext.textContent = `Please proceed to ${notice.next_destination || 'your next assigned office'}.`;
				}

				wrongOfficeModal.classList.add('is-open');
				wrongOfficeModal.setAttribute('aria-hidden', 'false');
				document.body.style.overflow = 'hidden';
				wrongOfficeOkBtn?.focus();
			};

			wrongOfficeOkBtn?.addEventListener('click', () => {
				if (wrongOfficeData?.scan_id) {
					setDismissedScanId(wrongOfficeData.scan_id);
				}
				closeWrongOfficeModal();
			});

			wrongOfficeModal?.addEventListener('click', (event) => {
				if (event.target === wrongOfficeModal) {
					if (wrongOfficeData?.scan_id) {
						setDismissedScanId(wrongOfficeData.scan_id);
					}
					closeWrongOfficeModal();
				}
			});

			document.addEventListener('keydown', (event) => {
				if (event.key === 'Escape' && wrongOfficeModal?.classList.contains('is-open')) {
					if (wrongOfficeData?.scan_id) {
						setDismissedScanId(wrongOfficeData.scan_id);
					}
					closeWrongOfficeModal();
				}
			});

			if (wrongOfficeData) {
				openWrongOfficeModal(wrongOfficeData);
			}

			if (officeQrHost && officeQrPayload && typeof QRCode !== 'undefined') {
				officeQrHost.innerHTML = '';
				new QRCode(officeQrHost, {
					text: officeQrPayload,
					width: 160,
					height: 160,
					colorDark: '#0f172a',
					colorLight: '#ffffff',
					correctLevel: QRCode.CorrectLevel.M,
				});
			}

			const pollUrl = @json($poll_url);
			if (!pollUrl) return;

			const reloadIfChanged = async () => {
				try {
					const response = await fetch(pollUrl, {
						headers: { Accept: 'application/json' },
						cache: 'no-store',
					});
					if (!response.ok) return;
					const payload = await response.json();
					if (!payload?.success || !payload?.data) return;

					const next = payload.data;
					const currentKey = @json(
						$completed_steps
						. '|' . $percent
						. '|' . ($current_step['order'] ?? 0)
						. '|' . ($is_complete ? 1 : 0)
						. '|' . ($wrong_office['scan_id'] ?? 0)
					);
					const nextKey = [
						next.completed_steps,
						next.percent,
						next.current_step?.order || 0,
						next.is_complete ? 1 : 0,
						next.wrong_office?.scan_id || 0,
					].join('|');

					if (nextKey !== currentKey) {
						// New wrong-office scan should always pop up again.
						if (next.wrong_office?.scan_id && next.wrong_office.scan_id !== (wrongOfficeData?.scan_id || 0)) {
							try {
								sessionStorage.removeItem(dismissKey);
							} catch (error) {
								// Ignore storage failures.
							}
						}
						window.location.reload();
					}
				} catch (error) {
					// Silent — keep page usable offline between polls.
				}
			};

			setInterval(reloadIfChanged, 5000);
			setTimeout(reloadIfChanged, 1500);
		})();
	</script>
</body>
</html>
