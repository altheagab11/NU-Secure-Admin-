<script>
window.OfficeScan = (function () {
	const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
	const verifyUrl = @json(route('office.scanner.verify'));
	const checkInUrl = @json(route('office.scanner.check-in'));

	let pendingPayload = null;
	let pendingMethod = 'camera';
	let verifying = false;
	let confirming = false;
	let onSuccess = null;
	let onResume = null;

	const modalEl = () => document.getElementById('scanResultModal');
	const manualEl = () => document.getElementById('manualPayloadModal');

	function showAlert(message, type) {
		const el = document.getElementById('scanModalAlert');
		if (!el) return;
		el.className = 'alert alert-' + (type || 'info');
		el.textContent = message;
		el.classList.remove('d-none');
	}

	function hideAlert() {
		const el = document.getElementById('scanModalAlert');
		if (!el) return;
		el.classList.add('d-none');
		el.textContent = '';
	}

	function fillModal(data) {
		const visitor = data.visitor || {};
		const visit = data.visit || {};
		const previous = data.previous_office || {};
		const current = data.current_office || {};

		document.getElementById('scanVisitorName').textContent = visitor.full_name || 'Visitor';
		document.getElementById('scanControlNumber').textContent = visit.control_number || '—';
		document.getElementById('scanPassNumber').textContent = visit.pass_number || '—';
		document.getElementById('scanPurpose').textContent = visit.purpose_reason || '—';
		document.getElementById('scanDestination').textContent = visit.destination_text || current.office_name || '—';
		document.getElementById('scanPreviousOffice').textContent = previous.office_name || 'Main Lobby';
		document.getElementById('scanCurrentOffice').textContent = current.office_name || '—';
		document.getElementById('scanVisitDate').textContent = visit.entry_time || '—';
		document.getElementById('scanVisitStatus').textContent = visit.exit_status || 'Active';

		const photo = document.getElementById('scanVisitorPhoto');
		const fallback = document.getElementById('scanVisitorPhotoFallback');
		if (visitor.photo_url) {
			photo.src = visitor.photo_url;
			photo.classList.remove('d-none');
			fallback.classList.add('d-none');
		} else {
			photo.classList.add('d-none');
			fallback.classList.remove('d-none');
		}

		const badge = document.getElementById('scanAuthBadge');
		if (data.authorized) {
			badge.className = 'badge-status badge-success';
			badge.textContent = 'Authorized to check in';
			document.getElementById('scanConfirmBtn').disabled = false;
		} else {
			badge.className = 'badge-status badge-danger';
			badge.textContent = 'Not authorized for this office';
			document.getElementById('scanConfirmBtn').disabled = true;
		}

		const remaining = document.getElementById('scanRemainingRoute');
		remaining.innerHTML = '';
		(data.remaining_route || []).forEach((step) => {
			const chip = document.createElement('span');
			chip.className = 'badge-status ' + (step.state === 'current' ? 'badge-warning' : 'badge-muted');
			chip.textContent = step.office_name;
			remaining.appendChild(chip);
		});
	}

	async function verify(payload, scanMethod) {
		if (verifying || !payload) return;
		verifying = true;
		pendingPayload = payload;
		pendingMethod = scanMethod || 'camera';
		hideAlert();

		try {
			const response = await fetch(verifyUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					'X-CSRF-TOKEN': csrf,
				},
				credentials: 'same-origin',
				body: JSON.stringify({
					qr_payload: payload,
					scan_method: scanMethod || 'camera',
				}),
			});

			const result = await response.json().catch(() => ({}));

			if (!response.ok || !result.success) {
				const message = result.message || 'Unable to verify the QR code. Please check your connection and try again.';
				toast(message, 'danger');
				if (typeof onResume === 'function') onResume(false);
				return result;
			}

			fillModal(result.data || {});
			showAlert(result.message || 'Visitor verified successfully.', 'success');
			const modal = bootstrap.Modal.getOrCreateInstance(modalEl());
			modal.show();
			return result;
		} catch (error) {
			toast('Unable to verify the QR code. Please check your connection and try again.', 'danger');
			if (typeof onResume === 'function') onResume(false);
			return null;
		} finally {
			verifying = false;
		}
	}

	async function confirmCheckIn() {
		if (confirming || !pendingPayload) return;
		confirming = true;
		const btn = document.getElementById('scanConfirmBtn');
		if (btn) {
			btn.disabled = true;
			btn.textContent = 'Recording...';
		}

		try {
			const response = await fetch(checkInUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					'X-CSRF-TOKEN': csrf,
				},
				credentials: 'same-origin',
				body: JSON.stringify({
					qr_payload: pendingPayload,
					scan_method: pendingMethod || 'camera',
				}),
			});
			const result = await response.json().catch(() => ({}));

			if (!response.ok || !result.success) {
				showAlert(result.message || 'Check-in failed.', 'danger');
				toast(result.message || 'Check-in failed.', 'danger');
				if (typeof onResume === 'function') onResume(false);
				return;
			}

			showAlert(result.message || 'Office check-in recorded successfully.', 'success');
			toast(result.message || 'Office check-in recorded successfully.', 'success');
			if (typeof onSuccess === 'function') onSuccess(result);

			setTimeout(() => {
				const modal = bootstrap.Modal.getInstance(modalEl());
				if (modal) modal.hide();
			}, 900);
		} catch (error) {
			showAlert('Unable to verify the QR code. Please check your connection and try again.', 'danger');
			if (typeof onResume === 'function') onResume(false);
		} finally {
			confirming = false;
			if (btn) {
				btn.disabled = false;
				btn.textContent = 'Confirm Check-in';
			}
		}
	}

	function toast(message, type) {
		let host = document.getElementById('officeToastHost');
		if (!host) {
			host = document.createElement('div');
			host.id = 'officeToastHost';
			host.className = 'position-fixed top-0 end-0 p-3';
			host.style.zIndex = '2000';
			document.body.appendChild(host);
		}
		const el = document.createElement('div');
		el.className = 'alert alert-' + (type || 'info') + ' shadow border-0';
		el.setAttribute('role', 'status');
		el.textContent = message;
		host.appendChild(el);
		setTimeout(() => el.remove(), 4500);
	}

	function openManual() {
		const modal = bootstrap.Modal.getOrCreateInstance(manualEl());
		document.getElementById('manualError')?.classList.add('d-none');
		modal.show();
		setTimeout(() => document.getElementById('manualQrPayload')?.focus(), 250);
	}

	function bind() {
		document.getElementById('scanConfirmBtn')?.addEventListener('click', confirmCheckIn);
		modalEl()?.addEventListener('hidden.bs.modal', () => {
			if (typeof onResume === 'function') onResume(true);
		});

		document.getElementById('manualPayloadForm')?.addEventListener('submit', async (e) => {
			e.preventDefault();
			const err = document.getElementById('manualError');
			err?.classList.add('d-none');

			let payload = (document.getElementById('manualQrPayload')?.value || '').trim();
			const control = (document.getElementById('manualControlNumber')?.value || '').trim();
			const pass = (document.getElementById('manualPassNumber')?.value || '').trim();

			if (!payload && (control || pass)) {
				payload = JSON.stringify({
					control_number: control || null,
					pass_number: pass || null,
				});
			}

			if (!payload) {
				if (err) {
					err.textContent = 'Enter a QR payload, control number, or pass number.';
					err.classList.remove('d-none');
				}
				return;
			}

			const modal = bootstrap.Modal.getInstance(manualEl());
			if (modal) modal.hide();
			await verify(payload, 'manual');
		});

		document.querySelectorAll('[data-open-manual-scan]').forEach((btn) => {
			btn.addEventListener('click', openManual);
		});
	}

	return {
		init(options) {
			onSuccess = options?.onSuccess || null;
			onResume = options?.onResume || null;
			bind();
		},
		verify,
		openManual,
		toast,
	};
})();
</script>
