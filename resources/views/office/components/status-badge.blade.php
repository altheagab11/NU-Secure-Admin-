@php
	$map = [
		'info' => 'badge-info',
		'warning' => 'badge-warning',
		'success' => 'badge-success',
		'danger' => 'badge-danger',
		'muted' => 'badge-muted',
		'expected' => 'badge-info',
		'waiting' => 'badge-warning',
		'ready' => 'badge-info',
		'checked_in' => 'badge-success',
		'completed' => 'badge-success',
		'cancelled' => 'badge-danger',
		'expired' => 'badge-muted',
		'valid' => 'badge-success',
		'invalid' => 'badge-danger',
		'unauthorized' => 'badge-danger',
	];
	$key = strtolower((string) ($tone ?? $status ?? 'info'));
	$class = $map[$key] ?? 'badge-info';
@endphp
<span class="badge-status {{ $class }}">{{ $label ?? $status ?? 'Status' }}</span>
