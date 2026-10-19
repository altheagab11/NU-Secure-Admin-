<section class="office-management-card" aria-labelledby="directoryHeading" id="officeDirectory">
	<div class="office-management-card-head">
		<div class="office-management-heading"><i class="bi bi-buildings" aria-hidden="true"></i><div><h2 id="directoryHeading">Office Directory</h2><p>List of university offices with their room and floor information.</p></div></div>
		<button type="button" class="btn btn-primary directory-primary" id="directoryAddOffice"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add Office</button>
	</div>
	<div class="office-management-card-body">
		@if(session('directory_success'))<div class="alert alert-success js-auto-dismiss-alert" role="status">{{ session('directory_success') }}</div>@endif
		@if($errors->officeDirectory->any() && old('directory_form') !== 'office')
			<div class="alert alert-danger" role="alert">{{ $errors->officeDirectory->first() }}</div>
		@endif
		<form method="GET" class="directory-filters" role="search" aria-label="Office directory filters">
			@foreach(request()->only(['search', 'office', 'position', 'page', 'per_page', 'directory_per_page']) as $key => $value)
				<input type="hidden" name="{{ $key }}" value="{{ $value }}">
			@endforeach
			<div class="directory-search"><i class="bi bi-search" aria-hidden="true"></i><input class="form-control" name="directory_search" value="{{ request('directory_search') }}" placeholder="Search office name..." aria-label="Search Office Name" maxlength="150"></div>
			<select name="directory_status" class="form-select" aria-label="Office directory status"><option value="">All Status</option><option value="active" @selected(request('directory_status') === 'active')>Active</option><option value="inactive" @selected(request('directory_status') === 'inactive')>Inactive</option></select>
			<button type="submit" class="btn btn-outline-primary">Search</button>
			<a class="btn btn-outline-secondary" href="{{ request()->fullUrlWithoutQuery(['directory_search', 'directory_status', 'directory_page']) }}">Clear Filters</a>
		</form>
		<div class="office-management-table-wrap">
			<table class="office-table directory-table" aria-label="Office directory table">
				<thead><tr><th scope="col">#</th><th scope="col">Office Name</th><th scope="col">Room Number</th><th scope="col">Floor Number</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
				<tbody>
					@forelse($directoryOffices as $entry)
					<tr>
						<td>{{ $directoryOffices->firstItem() + $loop->index }}</td><td class="directory-office-name">{{ $entry->office_name }}</td><td>{{ $entry->room_number ?: '—' }}</td><td>{{ $entry->floor ?: '—' }}</td>
						<td><span class="directory-status {{ $entry->is_active ? 'is-active' : 'is-inactive' }}">{{ $entry->is_active ? 'Active' : 'Inactive' }}</span></td>
						<td><div class="directory-row-actions">
							<button type="button" class="directory-icon-action" data-directory-edit data-office-id="{{ $entry->office_id }}" data-office-name="{{ $entry->office_name }}" data-room="{{ $entry->room_number }}" data-floor="{{ $entry->floor }}" data-active="{{ $entry->is_active ? '1' : '0' }}" aria-label="Edit {{ $entry->office_name }}" title="Edit office"><i class="bi bi-pencil-square" aria-hidden="true"></i></button>
							<button type="button" class="directory-icon-action {{ $entry->is_active ? 'is-danger' : 'is-success' }}" data-directory-status data-office-id="{{ $entry->office_id }}" data-office-name="{{ $entry->office_name }}" data-active="{{ $entry->is_active ? '1' : '0' }}" aria-label="{{ $entry->is_active ? 'Deactivate' : 'Reactivate' }} {{ $entry->office_name }}" title="{{ $entry->is_active ? 'Deactivate office' : 'Reactivate office' }}"><i class="bi {{ $entry->is_active ? 'bi-slash-circle' : 'bi-check-circle' }}" aria-hidden="true"></i></button>
						</div></td>
					</tr>
					@empty<tr><td colspan="6" class="table-empty-cell">No offices match your filters.</td></tr>@endforelse
				</tbody>
			</table>
		</div>
		@include('admin.partials.table-pagination', ['paginator' => $directoryOffices, 'perPageParam' => 'directory_per_page', 'ariaLabel' => 'Office directory pagination'])
	</div>
</section>
