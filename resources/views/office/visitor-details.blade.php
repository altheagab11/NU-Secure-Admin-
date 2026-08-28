@php $activeNav = ''; @endphp
@extends('office.layouts.office-layout')

@section('title', 'Visitor Details')

@push('styles')
<style nonce="{{ $cspNonce }}">
	.route-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
	.route-timeline::before {
		content: ""; position: absolute; left: 14px; top: 8px; bottom: 8px; width: 3px;
		background: #d7e4f8; border-radius: 4px;
	}
	.route-timeline li {
		position: relative; padding: 0 0 18px 48px;
	}
	.route-timeline li:last-child { padding-bottom: 0; }
	.route-dot {
		position: absolute; left: 6px; top: 4px; width: 20px; height: 20px; border-radius: 50%;
		background: #fff; border: 3px solid #9bb6e8; z-index: 1;
	}
	.route-timeline li.is-done .route-dot { border-color: #19B766; background: #19B766; }
	.route-timeline li.is-current .route-dot { border-color: #F5B400; background: #F5B400; box-shadow: 0 0 0 4px rgba(245,180,0,.2); }
	.route-title { font-weight: 700; margin-bottom: 2px; }
	.route-meta { color: #6b7a99; font-size: .86rem; }
</style>
@endpush

@section('content')
@php
	$visit = $details['visit'];
	$timeline = $details['timeline'];
@endphp

<div class="mb-3">
	<a href="#" class="btn btn-nu-outline btn-sm" data-history-back><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back</a>
</div>

<div class="row g-3">
	<div class="col-lg-4">
		<div class="office-card">
			<div class="text-center mb-3">
				@if($details['photo_url'])
					<img src="{{ $details['photo_url'] }}" alt="Photo of {{ $details['visitor_name'] }}" class="img-fluid rounded-4 visitor-detail-photo">
				@else
					<div class="rounded-4 d-flex align-items-center justify-content-center visitor-detail-photo-fallback">
						<i class="bi bi-person-bounding-box scan-photo-icon" aria-hidden="true"></i>
					</div>
				@endif
			</div>
			<h2 class="text-center">{{ $details['visitor_name'] }}</h2>
			<p class="text-center card-muted">{{ $visit->visit_type_name ?: 'Visitor' }}</p>
			<dl class="row small mb-0">
				<dt class="col-5 text-muted">Control No.</dt><dd class="col-7">{{ $visit->control_number ?: '—' }}</dd>
				<dt class="col-5 text-muted">Pass No.</dt><dd class="col-7">{{ $visit->pass_number ?: '—' }}</dd>
				<dt class="col-5 text-muted">Contact</dt><dd class="col-7">{{ $visit->contact_no ?: '—' }}</dd>
				<dt class="col-5 text-muted">QR Status</dt><dd class="col-7">
					@include('office.components.status-badge', [
						'tone' => $details['qr_status'] === 'Active' ? 'success' : 'muted',
						'label' => $details['qr_status'],
					])
				</dd>
			</dl>
			@if(empty($visit->exit_time) && $details['current'] && (int) $details['current']->office_id === (int) $office->office_id)
				<a href="{{ route('office.scanner') }}" class="btn btn-nu-primary w-100 mt-3">Scan / Check-in</a>
			@endif
		</div>
	</div>

	<div class="col-lg-8">
		<div class="office-card mb-3">
			<h2>Visit Information</h2>
			<div class="row g-3 small">
				<div class="col-md-6">
					<div class="text-muted">Visit date</div>
					<div class="fw-semibold">{{ $visit->entry_time ? \Carbon\Carbon::parse($visit->entry_time)->timezone('Asia/Manila')->format('M j, Y g:i A') : '—' }}</div>
				</div>
				<div class="col-md-6">
					<div class="text-muted">Current status</div>
					<div class="fw-semibold">{{ $visit->exit_status_name ?: (empty($visit->exit_time) ? 'Active' : 'Completed') }}</div>
				</div>
				<div class="col-md-6">
					<div class="text-muted">Purpose</div>
					<div class="fw-semibold">{{ $visit->purpose_reason ?: '—' }}</div>
				</div>
				<div class="col-md-6">
					<div class="text-muted">Person / department</div>
					<div class="fw-semibold">{{ $visit->primary_office_name ?: ($visit->destination_text ?: '—') }}</div>
				</div>
			</div>
		</div>

		<div class="office-card mb-3">
			<h2>Office Route Timeline</h2>
			<p class="card-muted">Completed, current, and remaining offices</p>
			@if(empty($timeline))
				<div class="empty-state"><p class="mb-0">No route configured for this visit.</p></div>
			@else
				<ul class="route-timeline">
					@foreach($timeline as $step)
						<li class="{{ $step['state'] === 'done' ? 'is-done' : ($step['state'] === 'current' ? 'is-current' : '') }}">
							<span class="route-dot" aria-hidden="true"></span>
							<div class="route-title">{{ $step['office_name'] }}</div>
							<div class="route-meta">
								@if($step['state'] === 'done')
									Completed
									@if($step['scan_time'])
										— {{ \Carbon\Carbon::parse($step['scan_time'])->timezone('Asia/Manila')->format('g:i A') }}
									@endif
									@if(!empty($step['staff_name']))
										· {{ $step['staff_name'] }}
									@endif
								@elseif($step['state'] === 'current')
									@if((int) ($details['current']->office_id ?? 0) === (int) $office->office_id)
										Current Office — Ready to Scan
									@else
										Current Office — {{ $step['office_name'] }}
									@endif
								@else
									Pending
								@endif
							</div>
							@if(!empty($step['remarks']))
								<div class="route-meta">Remarks: {{ $step['remarks'] }}</div>
							@endif
						</li>
					@endforeach
				</ul>
			@endif
		</div>

		<div class="office-card">
			<h2>Office Check-in Log</h2>
			@if($details['scans']->isEmpty())
				<div class="empty-state"><p class="mb-0">No office scans recorded yet.</p></div>
			@else
				<div class="table-scroll">
					<table class="table-office">
						<thead>
							<tr>
								<th>Time</th>
								<th>Office</th>
								<th>Staff</th>
								<th>Status</th>
								<th>Remarks</th>
							</tr>
						</thead>
						<tbody>
							@foreach($details['scans'] as $scan)
								<tr>
									<td>{{ $scan->scan_time ? \Carbon\Carbon::parse($scan->scan_time)->timezone('Asia/Manila')->format('M j, g:i A') : '—' }}</td>
									<td>{{ $scan->office_name }}</td>
									<td>{{ $scan->staff_name ?: '—' }}</td>
									<td>
										@include('office.components.status-badge', [
											'tone' => strtolower((string) $scan->validation_status),
											'label' => $scan->validation_status ?: '—',
										])
									</td>
									<td>{{ $scan->remarks ?: '—' }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@endif
		</div>
	</div>
</div>
@endsection
