@php
	$tone = $tone ?? 'info';
	$icon = $icon ?? 'bi-bar-chart';
@endphp
<div class="stat-card">
	<div class="d-flex justify-content-between align-items-start">
		<div class="icon-box" aria-hidden="true"><i class="bi {{ $icon }}"></i></div>
	</div>
	<div class="label">{{ $label }}</div>
	<div class="value" data-stat="{{ $statKey ?? '' }}">{{ number_format((int) $value) }}</div>
</div>
