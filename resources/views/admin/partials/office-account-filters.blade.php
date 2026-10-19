<form method="GET" action="" class="office-filters-form">
					@foreach(request()->only(['directory_search', 'directory_status', 'directory_page', 'directory_per_page', 'per_page']) as $key => $value)
<input type="hidden" name="{{ $key }}" value="{{ $value }}">
@endforeach
<div class="filters-card" role="search" aria-label="Office filters">
						<div class="search-wrap">
							<input class="filter-input" type="text" name="search" value="{{ request('search', '') }}" placeholder="Search name or email" aria-label="Search users">
						</div>

						<select name="office" class="filter-select" aria-label="Filter by office" data-auto-submit>
							<option value="">All offices</option>
							@foreach($officeFilterOptions ?? $officeOptions ?? [] as $opt)
								@php
									$optOfficeId = data_get($opt, 'office_id', data_get($opt, 'id'));
									$optOfficeName = data_get($opt, 'office_name', data_get($opt, 'name'));
								@endphp
								<option value="{{ $optOfficeId }}" @selected(request('office') == $optOfficeId)>{{ $optOfficeName }}</option>
							@endforeach
						</select>

						<select name="position" class="filter-select" aria-label="Filter by position" data-auto-submit>
							<option value="">All positions</option>
							@foreach($positions ?? [] as $pos)
								@if(!empty($pos))
									<option value="{{ $pos }}" @selected(request('position') === (string) $pos)>{{ $pos }}</option>
								@endif
							@endforeach
						</select>

						<div class="filter-actions">
							<button type="submit" class="add-guard-btn">Apply</button>
							<a href="{{ request()->fullUrlWithoutQuery(['search', 'office', 'position', 'page']) }}" class="clear-filters-btn">Clear</a>
						</div>
					</div>
				</form>
