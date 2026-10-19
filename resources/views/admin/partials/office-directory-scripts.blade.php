<script nonce="{{ $cspNonce }}">
(() => {
	const modalEl = document.getElementById('officeDirectoryModal');
	if (!modalEl) return;
	const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
	const form = document.getElementById('officeDirectoryForm');
	const officeBase = @json(url('/admin/offices'));
	const field = id => document.getElementById(id);
	function setMode(editing) {
		field('directoryFormTitle').textContent = editing ? 'Edit Office' : 'Add New Office';
		field('directorySubmitLabel').textContent = editing ? 'Update Office' : 'Save Office';
		field('directoryFormMethod').value = editing ? 'PUT' : 'POST';
		field('directoryRoom').required = !editing;
		field('directoryFloor').required = !editing;
		form.querySelectorAll('.directory-required').forEach(mark => mark.classList.toggle('d-none', editing));
		field('directoryActiveLabel').textContent = field('directoryActive').checked ? 'Active' : 'Inactive';
	}
	document.getElementById('directoryAddOffice').addEventListener('click', () => {
		form.reset(); form.querySelector('.directory-validation')?.classList.add('d-none');
		field('directoryOfficeId').value = '';
		['directoryOfficeName', 'directoryRoom', 'directoryFloor'].forEach(id => field(id).value = '');
		field('directoryActive').checked = true;
		form.action = officeBase; setMode(false); modal.show();
	});
	document.querySelectorAll('[data-directory-edit]').forEach(button => button.addEventListener('click', () => {
		form.querySelector('.directory-validation')?.classList.add('d-none');
		field('directoryOfficeId').value = button.dataset.officeId;
		field('directoryOfficeName').value = button.dataset.officeName;
		field('directoryRoom').value = button.dataset.room;
		field('directoryFloor').value = button.dataset.floor;
		field('directoryActive').checked = button.dataset.active === '1';
		form.action = officeBase + '/' + encodeURIComponent(button.dataset.officeId);
		setMode(true); modal.show();
	}));
	field('directoryActive').addEventListener('change', () => { field('directoryActiveLabel').textContent = field('directoryActive').checked ? 'Active' : 'Inactive'; });
	modalEl.addEventListener('shown.bs.modal', () => field('directoryOfficeName').focus());
	document.querySelectorAll('[data-directory-status]').forEach(button => button.addEventListener('click', () => {
		const active = button.dataset.active === '1';
		field('officeDirectoryStatusForm').action = officeBase + '/' + encodeURIComponent(button.dataset.officeId) + '/status';
		field('directoryStatusValue').value = active ? '0' : '1';
		field('directoryStatusTitle').textContent = active ? 'Deactivate Office?' : 'Reactivate Office?';
		field('directoryStatusOffice').textContent = button.dataset.officeName;
		field('directoryStatusMessage').textContent = active
			? 'This office will no longer be available for new visitor destinations. Existing visitor history will be preserved.'
			: 'This office will be available for new visitor destinations again. Existing assignments and history will be preserved.';
		field('directoryStatusSubmit').textContent = active ? 'Deactivate Office' : 'Reactivate Office';
		field('directoryStatusSubmit').className = 'btn ' + (active ? 'btn-danger' : 'btn-success');
		bootstrap.Modal.getOrCreateInstance(field('officeDirectoryStatusModal')).show();
	}));
	[form, field('officeDirectoryStatusForm')].forEach(current => {
		let saving = false;
		const host = current.closest('.modal');
		current.addEventListener('submit', event => {
			if (saving) { event.preventDefault(); return; }
			saving = true;
			current.querySelectorAll('button').forEach(button => button.disabled = true);
			host.querySelector('.btn-close').disabled = true;
			if (current === form) field('directorySubmitLabel').textContent = 'Saving...';
			else current.querySelector('[type="submit"]').textContent = 'Saving...';
			current.setAttribute('aria-busy', 'true');
		});
		host.addEventListener('hide.bs.modal', event => { if (saving) event.preventDefault(); });
		window.addEventListener('pageshow', event => {
			if (!event.persisted) return;
			saving = false;
			current.querySelectorAll('button').forEach(button => button.disabled = false);
			host.querySelector('.btn-close').disabled = false;
			current.removeAttribute('aria-busy');
			if (current === form) setMode(!!field('directoryOfficeId').value);
			else field('directoryStatusSubmit').textContent = field('directoryStatusValue').value === '0' ? 'Deactivate Office' : 'Reactivate Office';
		});
	});
	if (modalEl.dataset.reopen === '1') { setMode(!!field('directoryOfficeId').value); modal.show(); }
})();
</script>
