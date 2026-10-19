@php($directoryEditing = $errors->officeDirectory->any() && old('directory_form') === 'office' && old('directory_office_id'))
<div class="modal fade office-directory-modal" id="officeDirectoryModal" tabindex="-1" aria-labelledby="officeDirectoryModalTitle" aria-hidden="true" data-reopen="{{ $errors->officeDirectory->any() && old('directory_form') === 'office' ? '1' : '0' }}">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
		<div class="modal-header"><h2 class="modal-title fs-5" id="officeDirectoryModalTitle"><i class="bi bi-building me-2" aria-hidden="true"></i><span id="directoryFormTitle">{{ $directoryEditing ? 'Edit Office' : 'Add New Office' }}</span></h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
		<form id="officeDirectoryForm" method="POST" action="{{ $directoryEditing ? route('admin.offices.update', old('directory_office_id')) : route('admin.offices.store') }}">
			@csrf
			<input type="hidden" name="_method" id="directoryFormMethod" value="{{ $directoryEditing ? 'PUT' : 'POST' }}">
			<input type="hidden" name="directory_form" value="office">
			<input type="hidden" name="directory_office_id" id="directoryOfficeId" value="{{ old('directory_office_id') }}">
			<div class="modal-body">
				@if($errors->officeDirectory->any())<div class="alert alert-danger directory-validation" role="alert"><ul class="mb-0 ps-3">@foreach($errors->officeDirectory->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
				<div class="mb-3"><label for="directoryOfficeName" class="form-label">Office Name <span class="text-danger">*</span></label><input id="directoryOfficeName" name="office_name" class="form-control" value="{{ old('office_name') }}" required maxlength="150" placeholder="e.g. Scholarship Office"></div>
				<div class="row g-3 mb-3">
					<div class="col-sm-6"><label for="directoryRoom" class="form-label">Room Number <span class="text-danger directory-required">*</span></label><input id="directoryRoom" name="room_number" class="form-control" value="{{ old('room_number') }}" maxlength="30" pattern="[A-Za-z0-9]+(-[A-Za-z0-9]+)*" placeholder="e.g. 204" @required(!$directoryEditing)></div>
					<div class="col-sm-6"><label for="directoryFloor" class="form-label">Floor Number <span class="text-danger directory-required">*</span></label><input id="directoryFloor" name="floor" class="form-control" value="{{ old('floor') }}" maxlength="100" placeholder="e.g. 2nd Floor" @required(!$directoryEditing)></div>
				</div>
				<label class="form-label" for="directoryActive">Status</label><input type="hidden" name="is_active" value="0">
				<div class="form-check form-switch"><input class="form-check-input" id="directoryActive" name="is_active" type="checkbox" role="switch" value="1" @checked(old('is_active', '1')) aria-describedby="directoryStatusHelp"><label class="form-check-label" for="directoryActive" id="directoryActiveLabel">{{ old('is_active', '1') ? 'Active' : 'Inactive' }}</label></div>
				<p class="form-text mt-2 mb-0" id="directoryStatusHelp">Inactive offices cannot be selected for new visitor destinations.</p>
			</div>
			<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary directory-primary"><i class="bi bi-floppy me-2" aria-hidden="true"></i><span id="directorySubmitLabel">{{ $directoryEditing ? 'Update Office' : 'Save Office' }}</span></button></div>
		</form>
	</div></div>
</div>
<div class="modal fade office-directory-modal" id="officeDirectoryStatusModal" tabindex="-1" aria-labelledby="directoryStatusTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered"><div class="modal-content">
		<div class="modal-header"><h2 id="directoryStatusTitle" class="modal-title fs-5">Deactivate Office?</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
		<form id="officeDirectoryStatusForm" method="POST">@csrf @method('PATCH')<input type="hidden" name="directory_form" value="status"><input id="directoryStatusValue" name="is_active" type="hidden">
			<div class="modal-body"><p id="directoryStatusOffice" class="fw-semibold"></p><p id="directoryStatusMessage" class="mb-0">This office will no longer be available for new visitor destinations. Existing visitor history will be preserved.</p></div>
			<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button id="directoryStatusSubmit" type="submit" class="btn btn-danger">Deactivate Office</button></div>
		</form>
	</div></div>
</div>
