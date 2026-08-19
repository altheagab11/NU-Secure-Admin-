<script>
window.OfficeScan = (function () {
	const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
	const checkInUrl = @json(route('office.scanner.check-in'));

	let pendingPayload = null;
	let pendingMethod = 'camera';
	let pendingAuthorized = false;
	let processingScan = false;
	let onSuccess = null;
	let onResume = null;
	let onRecentScans = null;

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

	function setDoneButtonLabel(btn, label) {
		if (!btn) return;
		btn.innerHTML = '<i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i> ' + label;
	}

	function fillModal(data) {
		const visitor = data.visitor || {};
		const visit = data.visit || {};
		const previous = data.previous_office || {};
		const current = data.current_office || {};
		const staff = data.staff_office || {};
		const authorized = !!data.authorized;
		pendingAuthorized = authorized;

		document.getElementById('scanVisitorName').textContent = visitor.full_name || 'Visitor';
		document.getElementById('scanControlNumber').textContent = visit.control_number || '—';
		document.getElementById('scanPassNumber').textContent = visit.pass_number || '—';
		document.getElementById('scanPurpose').textContent = visit.purpose_reason || '—';
		document.getElementById('scanDestination').textContent = visit.destination_display || visit.destination_text || current.office_name || '—';
		document.getElementById('scanPreviousOffice').textContent = previous.office_name || 'Main Lobby';
		document.getElementById('scanCurrentOffice').textContent = current.office_name || '—';
		document.getElementById('scanVisitDate').textContent = visit.entry_time || '—';
		document.getElementById('scanVisitStatus').textContent = visit.exit_status || 'Active';

		const staffOfficeEl = document.getElementById('scanStaffOffice');
		const staffOfficeLabel = document.getElementById('scanStaffOfficeLabel');
		if (staffOfficeEl) {
			staffOfficeEl.textContent = staff.office_name || '—';
		}
		if (staffOfficeLabel && staffOfficeEl) {
			staffOfficeLabel.classList.toggle('d-none', authorized);
			staffOfficeEl.classList.toggle('d-none', authorized);
		}

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
		const confirmBtn = document.getElementById('scanConfirmBtn');
		const cancelBtn = document.getElementById('scanCancelBtn');
		const guidance = document.getElementById('scanWrongOfficeGuidance');

		if (authorized) {
			badge.className = 'badge-status badge-success';
			badge.textContent = 'Correct destination';
			if (confirmBtn) {
				confirmBtn.disabled = false;
				setDoneButtonLabel(confirmBtn, 'Done');
			}
			cancelBtn?.classList.add('d-none');
			guidance?.classList.add('d-none');
		} else {
			badge.className = 'badge-status badge-warning';
			badge.textContent = 'Wrong office destination';
			if (confirmBtn) {
				confirmBtn.disabled = false;
				setDoneButtonLabel(confirmBtn, 'Done');
			}
			cancelBtn?.classList.add('d-none');
			guidance?.classList.remove('d-none');
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

	function openResultModal() {
		const modal = bootstrap.Modal.getOrCreateInstance(modalEl());
		modal.show();
	}

	function dismissModal() {
		const modal = bootstrap.Modal.getInstance(modalEl());
		if (modal) modal.hide();
	}

	function notifyRecentScans(result) {
		if (typeof onRecentScans === 'function' && result && result.recent_scans) {
			onRecentScans(result.recent_scans);
		}
	}

	function handleScanResult(result, responseOk) {
		const code = result.code || '';
		const hasVisitorData = !!(result.data && (result.data.visitor || result.data.visit));
		const isWrongOfficeResult = ['WRONG_OFFICE', 'PREVIOUS_INCOMPLETE'].includes(code) && hasVisitorData;

		notifyRecentScans(result);

		if (responseOk && result.success && hasVisitorData) {
			fillModal(result.data);
			showAlert(result.message || 'Office check-in recorded successfully.', 'success');
			openResultModal();
			toast(result.message || 'Office check-in recorded successfully.', 'success');
			if (typeof onSuccess === 'function') onSuccess(result);
			return result;
		}

		if (isWrongOfficeResult) {
			fillModal(result.data);
			showAlert(result.message || 'Wrong office destination.', 'warning');
			openResultModal();
			return result;
		}

		const message = result.message || 'Unable to record this scan. Please try again.';
		toast(message, 'danger');
		if (typeof onResume === 'function') onResume(false);
		return result;
	}

	async function verify(payload, scanMethod) {
		if (processingScan || !payload) return;
		processingScan = true;
		pendingPayload = payload;
		pendingMethod = scanMethod || 'camera';
		pendingAuthorized = false;
		hideAlert();

		const confirmBtn = document.getElementById('scanConfirmBtn');
		if (confirmBtn) {
			confirmBtn.disabled = true;
			confirmBtn.innerHTML = 'Saving...';
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
					qr_payload: payload,
					scan_method: scanMethod || 'camera',
					scans_per_page: Number(document.querySelector('#scans_per_page')?.value || 5) || 5,
				}),
			});

			const result = await response.json().catch(() => ({}));
			return handleScanResult(result, response.ok);
		} catch (error) {
			toast('Unable to record this scan. Please check your connection and try again.', 'danger');
			if (typeof onResume === 'function') onResume(false);
			return null;
		} finally {
			processingScan = false;
			if (confirmBtn) {
				confirmBtn.disabled = false;
				setDoneButtonLabel(confirmBtn, 'Done');
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
		document.getElementById('scanConfirmBtn')?.addEventListener('click', dismissModal);
		modalEl()?.addEventListener('hidden.bs.modal', () => {
			pendingAuthorized = false;
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
			onRecentScans = options?.onRecentScans || null;
			bind();
		},
		verify,
		openManual,
		toast,
	};
})();
</script>
