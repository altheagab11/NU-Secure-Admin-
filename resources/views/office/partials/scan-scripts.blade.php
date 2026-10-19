<script nonce="{{ $cspNonce }}">
window.OfficeScan = (function () {
	const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
	const checkInUrl = @json(route('office.scanner.check-in'));
	const addOfficeUrl = @json(route('office.scanner.add-office'));
	const wrongOfficeUrl = @json(route('office.scanner.wrong-office'));

	let pendingPayload = null;
	let pendingMethod = 'camera';
	let pendingAuthorized = false;
	let processingScan = false;
	let onSuccess = null;
	let onResume = null;
	let onRecentScans = null;
	let pendingScanId = null;
	let adjustmentAllowed = false;
	let submittingAdjustment = false;

	const modalEl = () => document.getElementById('scanResultModal');
	const manualEl = () => document.getElementById('manualPayloadModal');

	function showAlert(message, type) {
		const el = document.getElementById('scanModalAlert');
		if (!el) return;
		el.className = 'alert verification-alert alert-' + (type || 'info');
		document.getElementById('scanModalAlertText').textContent = message;
		document.getElementById('scanModalAlertIcon').className = 'bi ' + (type === 'warning' ? 'bi-exclamation-triangle-fill' : (type === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill'));
		el.classList.remove('d-none');
	}

	function hideAlert() {
		const el = document.getElementById('scanModalAlert');
		if (!el) return;
		el.classList.add('d-none');
		document.getElementById('scanModalAlertText').textContent = '';
	}

	function fillModal(data) {
		const visitor = data.visitor || {};
		const visit = data.visit || {};
		const previous = data.previous_office || {};
		const current = data.current_office || {};
		const staff = data.staff_office || {};
		const authorized = !!data.authorized;
		pendingAuthorized = authorized;
		pendingScanId = data.wrong_office_scan_id || null;
		adjustmentAllowed = !!data.can_adjust_office && !authorized && !!pendingScanId;
		document.getElementById('officeVerificationActions')?.classList.toggle('d-none', !adjustmentAllowed);
		closeAdjustmentPanels();
		document.getElementById('addOfficeForm')?.reset();
		document.getElementById('addOfficeSubmit').textContent = 'Add Office and Check-in';
		document.getElementById('wrongOfficeForm')?.reset();
		document.getElementById('officeToAdd').value = staff.office_name || '';
		document.getElementById('wrongCurrentOffice').value = staff.office_name || '';
		document.getElementById('wrongExpectedOffice').value = data.expected_destination || current.office_name || '';

		document.getElementById('scanVisitorName').textContent = visitor.full_name || 'Visitor';
		document.getElementById('scanControlNumber').textContent = visit.control_number || '—';
		document.getElementById('scanPassNumber').textContent = visit.pass_number || '—';
		document.getElementById('scanPurpose').textContent = visit.purpose_reason || '—';
		document.getElementById('scanDestination').textContent = visit.destination_display || visit.destination_text || current.office_name || '—';
		document.getElementById('scanPreviousOffice').textContent = previous.office_name || 'Main Lobby';
		document.getElementById('scanCurrentOffice').textContent = current.office_name || '—';
		document.getElementById('scanVisitDate').textContent = visit.entry_time || '—';
		const status = visit.exit_status || 'Active';
		const statusEl = document.getElementById('scanVisitStatus');
		statusEl.textContent = status;
		statusEl.className = 'verification-status' + (/^(active|ongoing|still inside)$/i.test(status.trim()) ? ' is-active' : '');

		const staffOfficeEl = document.getElementById('scanStaffOffice');
		if (staffOfficeEl) {
			staffOfficeEl.textContent = staff.office_name || '—';
			staffOfficeEl.classList.toggle('is-mismatch', !authorized);
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
		badge.className = 'verification-auth-badge' + (authorized ? ' is-authorized' : '');
		badge.innerHTML = authorized
			? '<i class="bi bi-shield-check" aria-hidden="true"></i><span>Correct destination</span>'
			: '<i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><span>Wrong office destination</span>';

		const remaining = document.getElementById('scanRemainingRoute');
		remaining.innerHTML = '';
		const steps = data.remaining_route || [];
		const nextIndex = Math.max(0, steps.findIndex((step) => (data.current_expectation_id != null && step.expectation_id === data.current_expectation_id) || (current.office_id != null && step.office_id === current.office_id)));
		steps.forEach((step, index) => {
			const isNext = index === nextIndex;
			const item = document.createElement('li');
			item.className = 'verification-route-step' + (isNext ? ' is-next' : '');
			if (isNext) item.setAttribute('aria-current', 'step');
			const number = document.createElement('span');
			number.className = 'verification-route-number';
			number.textContent = String(index + 1);
			number.setAttribute('aria-hidden', 'true');
			const content = document.createElement('div');
			const name = document.createElement('span');
			name.className = 'verification-route-name';
			name.textContent = step.office_name;
			const note = document.createElement('span');
			note.className = 'verification-route-note';
			note.textContent = isNext ? 'Next destination' : (step.optional ? 'Optional destination' : 'Upcoming office');
			content.appendChild(name);
			content.appendChild(note);
			item.appendChild(number);
			item.appendChild(content);
			remaining.appendChild(item);
		});
		if (!steps.length) {
			const empty = document.createElement('li');
			empty.className = 'verification-route-empty';
			empty.textContent = 'No remaining destinations.';
			remaining.appendChild(empty);
		}
	}

	function openResultModal() {
		const modal = bootstrap.Modal.getOrCreateInstance(modalEl());
		modal.show();
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
			if (result.data) result.data.expected_destination = result.expected_office;
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
		if (processingScan || submittingAdjustment || modalEl()?.classList.contains('show') || !payload) return;
		processingScan = true;
		pendingPayload = payload;
		pendingMethod = scanMethod || 'camera';
		pendingAuthorized = false;
		hideAlert();

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

	function closeAdjustmentPanels() {
		document.getElementById('scanResultModalLabel').textContent = 'Visitor Verification';
		['addOfficePanel', 'wrongOfficePanel', 'addOfficeError', 'wrongOfficeError'].forEach((id) => document.getElementById(id)?.classList.add('d-none'));
		['showAddOffice', 'showWrongOffice'].forEach((id) => document.getElementById(id)?.setAttribute('aria-expanded', 'false'));
	}

	function openAdjustment(adding) {
		if (!adjustmentAllowed || submittingAdjustment) return;
		closeAdjustmentPanels();
		document.getElementById('scanResultModalLabel').textContent = adding ? 'Add Additional Office' : 'Log Wrong Office Note';
		const panel = document.getElementById(adding ? 'addOfficePanel' : 'wrongOfficePanel');
		panel?.classList.remove('d-none');
		document.getElementById(adding ? 'showAddOffice' : 'showWrongOffice')?.setAttribute('aria-expanded', 'true');
		panel?.scrollIntoView({ block: 'nearest' });
		document.getElementById(adding ? 'officeAdditionReason' : 'wrongOfficeRemarks')?.focus();
	}

	async function submitAdjustment(event, adding) {
		event.preventDefault();
		if (!adjustmentAllowed || submittingAdjustment || !pendingPayload || !pendingScanId) return;
		const input = document.getElementById(adding ? 'officeAdditionReason' : 'wrongOfficeRemarks');
		const error = document.getElementById(adding ? 'addOfficeError' : 'wrongOfficeError');
		const value = input.value.trim();
		error.classList.add('d-none');
		if (!value) {
			error.textContent = adding ? 'Please enter a reason for visiting this office.' : 'Please enter the wrong-office remarks.';
			error.classList.remove('d-none');
			input.focus();
			return;
		}
		const submit = event.target.querySelector('[type="submit"]');
		const label = submit.textContent;
		const buttons = Array.from(modalEl().querySelectorAll('button, input, textarea'));
		const disabled = buttons.map((button) => button.disabled);
		submittingAdjustment = true;
		buttons.forEach((button) => button.disabled = true);
		submit.textContent = 'Saving...';
		try {
			const response = await fetch(adding ? addOfficeUrl : wrongOfficeUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
				credentials: 'same-origin',
				body: JSON.stringify({
					qr_payload: pendingPayload, scan_id: pendingScanId, scan_method: pendingMethod,
					[adding ? 'reason' : 'remarks']: value,
					automatic_check_in: document.getElementById('officeAutomaticCheckIn').checked,
					scans_per_page: Number(document.querySelector('#scans_per_page')?.value || 5),
				}),
			});
			const result = await response.json().catch(() => ({}));
			if (!response.ok || !result.success) {
				throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || 'Unable to save the office adjustment.');
			}
			fillModal(result.data || {});
			showAlert(result.message, 'success');
			notifyRecentScans(result);
			toast(result.message, 'success');
			if (typeof onSuccess === 'function') onSuccess(result);
			document.getElementById('scanModalAlert')?.scrollIntoView({ block: 'nearest' });
		} catch (failure) {
			error.textContent = failure.message || 'Unable to save. Check your connection and try again.';
			error.classList.remove('d-none');
		} finally {
			submittingAdjustment = false;
			buttons.forEach((button, index) => button.disabled = disabled[index]);
			submit.textContent = label;
		}
	}

	function bind() {
		document.getElementById('showAddOffice')?.addEventListener('click', () => openAdjustment(true));
		document.getElementById('showWrongOffice')?.addEventListener('click', () => openAdjustment(false));
		document.querySelectorAll('[data-cancel-adjustment]').forEach((button) => button.addEventListener('click', () => {
			closeAdjustmentPanels();
			document.getElementById('showAddOffice')?.focus();
		}));
		document.getElementById('addOfficeForm')?.addEventListener('submit', (event) => submitAdjustment(event, true));
		document.getElementById('wrongOfficeForm')?.addEventListener('submit', (event) => submitAdjustment(event, false));
		document.getElementById('officeAutomaticCheckIn')?.addEventListener('change', (event) => {
			document.getElementById('addOfficeSubmit').textContent = event.target.checked ? 'Add Office and Check-in' : 'Add Office';
		});
		modalEl()?.addEventListener('hide.bs.modal', (event) => { if (submittingAdjustment) event.preventDefault(); });
		modalEl()?.addEventListener('hidden.bs.modal', () => {
			pendingAuthorized = false;
			adjustmentAllowed = false;
			pendingScanId = null;
			closeAdjustmentPanels();
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
