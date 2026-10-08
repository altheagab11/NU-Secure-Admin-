<input type="search" name="search" class="alerts-search-input" placeholder="Search visitor..." aria-label="Search visitor" maxlength="255">
@if($kind === 'completed')
    <select name="status" class="alerts-filter-select" aria-label="Visitor status"><option value="">All Status</option><option value="Ready to Exit">Ready to Exit</option><option value="Completed">Completed</option></select>
@else
    <select name="severity" class="alerts-filter-select" aria-label="Alert severity"><option value="">All Severities</option>@foreach(['Critical','High','Medium','Low'] as $severity)<option>{{ $severity }}</option>@endforeach</select>
    <select name="type" class="alerts-filter-select" aria-label="Alert type"><option value="">All Alert Types</option>@foreach($alertTypes ?? [] as $type)<option value="{{ $type }}">{{ ucwords(strtolower($type)) }}</option>@endforeach</select>
@endif
