<style>
	.kiosk-privacy-modal {
		position: fixed;
		inset: 0;
		z-index: 2000;
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
</style>

<div class="kiosk-privacy-modal is-hidden" id="guardPrivacyModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="guardPrivacyTitle">
	<div class="kiosk-privacy-card">
		<div class="kiosk-privacy-header">
			<span class="kiosk-privacy-badge"><i class="bi bi-shield-lock-fill"></i> Data Privacy Notice</span>
			<h2 class="kiosk-privacy-title" id="guardPrivacyTitle">Consent to Collect and Process Visitor Data</h2>
			<p class="kiosk-privacy-subtitle">Please explain this notice to the visitor before continuing with registration.</p>
		</div>
		<div class="kiosk-privacy-body">
			<p>
				National University uses the Visitor Monitoring System (VMS) for campus security and safety.
				Registration involves collecting and processing the visitor's personal information in accordance
				with the Data Privacy Act of 2012 (RA 10173).
			</p>
			<h3>Information collected</h3>
			<ul>
				<li>Identification details from a scanned or uploaded ID, including name, birthday, and address</li>
				<li>Contact information, destination, and reason for visit</li>
				<li>A photograph of the visitor together with their ID for verification</li>
				<li>A QR ticket and control number for entry and exit monitoring</li>
			</ul>
			<h3>How the information is used</h3>
			<ul>
				<li>To verify identity and complete visitor check-in</li>
				<li>To document the visit and destination inside the campus</li>
				<li>To support security monitoring, alerts, and incident response</li>
				<li>To generate and validate the temporary visitor QR ticket</li>
			</ul>
			<h3>Access and consent</h3>
			<p>
				Only authorized University personnel may access the information for visitor management and campus
				security. Registration is voluntary. Select <strong>I Agree</strong> only after the visitor confirms
				that they understand and consent to this processing.
			</p>
		</div>
		<div class="kiosk-privacy-footer">
			<button type="button" class="kiosk-privacy-btn secondary" id="guardPrivacyDecline">Decline</button>
			<button type="button" class="kiosk-privacy-btn primary" id="guardPrivacyAccept">I Agree</button>
		</div>
	</div>
</div>

<script nonce="{{ $cspNonce }}">
(function () {
	const privacyModal = document.getElementById('guardPrivacyModal');
	const privacyAccept = document.getElementById('guardPrivacyAccept');
	const privacyDecline = document.getElementById('guardPrivacyDecline');
	const typeLinks = document.querySelectorAll('.js-privacy-consent-link');
	const registerToggle = document.querySelector('[data-bs-target="#registerMenu"]');
	const registerMenu = document.getElementById('registerMenu');

	const syncRegisterHighlight = () => {
		if (!registerToggle || !registerMenu) {
			return;
		}

		const isOpen = registerMenu.classList.contains('show');
		registerToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		registerToggle.classList.toggle('open', isOpen);
	};

	if (registerMenu) {
		registerMenu.addEventListener('shown.bs.collapse', syncRegisterHighlight);
		registerMenu.addEventListener('hidden.bs.collapse', syncRegisterHighlight);
		syncRegisterHighlight();
	}

	if (!privacyModal || !typeLinks.length) {
		return;
	}

	let pendingHref = '';

	const openPrivacyModal = (href) => {
		pendingHref = href || '';
		privacyModal.classList.remove('is-hidden');
		privacyModal.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
		privacyAccept?.focus();
	};

	const closePrivacyModal = () => {
		pendingHref = '';
		privacyModal.classList.add('is-hidden');
		privacyModal.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';
	};

	const isSameRegisterDestination = (href) => {
		try {
			const target = new URL(href, window.location.origin);
			const current = new URL(window.location.href);

			if (current.pathname.indexOf('/guard/register') !== 0 || current.pathname !== target.pathname) {
				return false;
			}

			const currentType = current.searchParams.get('type');
			const targetType = target.searchParams.get('type') || 'normal';

			// Welcome / type-picker page has no type yet — always show privacy.
			if (!currentType) {
				return false;
			}

			return currentType === targetType;
		} catch (error) {
			return false;
		}
	};

	typeLinks.forEach((link) => {
		link.addEventListener('click', (event) => {
			if (link.classList.contains('is-disabled') || link.getAttribute('aria-disabled') === 'true') {
				event.preventDefault();
				return;
			}

			const href = link.getAttribute('href');
			if (!href) {
				return;
			}

			// Skip only when already on that exact register type page.
			if (isSameRegisterDestination(href)) {
				return;
			}

			event.preventDefault();
			openPrivacyModal(href);
		});
	});

	privacyAccept?.addEventListener('click', () => {
		const href = pendingHref;
		if (!href) {
			return;
		}

		closePrivacyModal();
		window.location.href = href;
	});

	privacyDecline?.addEventListener('click', closePrivacyModal);

	privacyModal.addEventListener('click', (event) => {
		if (event.target === privacyModal) {
			closePrivacyModal();
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && !privacyModal.classList.contains('is-hidden')) {
			closePrivacyModal();
		}
	});
})();
</script>
