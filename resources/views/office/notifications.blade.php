@php $activeNav = 'notifications'; @endphp
@extends('office.layouts.office-layout')

@section('title', 'Notifications')

@section('content')
<div class="office-card">
	<h2>Notifications</h2>
	<p class="card-muted">Alerts and scan notices for your office account</p>

	@if($notificationList->isEmpty())
		<div class="empty-state">
			<i class="bi bi-bell" aria-hidden="true"></i>
			<p class="mb-0">No notifications yet.</p>
		</div>
	@else
		<div class="table-scroll">
			<table class="table-office">
				<thead>
					<tr>
						<th>Type</th>
						<th>Message</th>
						<th>Sent</th>
						<th>Status</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach($notificationList as $notif)
						<tr class="{{ empty($notif->read_at) ? '' : 'opacity-75' }}">
							<td>{{ $notif->notif_type_name ?: 'Notice' }}</td>
							<td>{{ $notif->message }}</td>
							<td>{{ $notif->sent_at ? \Carbon\Carbon::parse($notif->sent_at)->timezone('Asia/Manila')->format('M j, g:i A') : '—' }}</td>
							<td>
								@include('office.components.status-badge', [
									'tone' => empty($notif->read_at) ? 'warning' : 'muted',
									'label' => empty($notif->read_at) ? 'Unread' : 'Read',
								])
							</td>
							<td>
								@if(empty($notif->read_at))
									<form method="POST" action="{{ route('office.notifications.read', $notif->notif_id) }}">
										@csrf
										<button type="submit" class="btn btn-sm btn-nu-outline">Mark read</button>
									</form>
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	@endif

	@include('admin.partials.table-pagination', [
		'paginator' => $notificationList,
		'perPageParam' => 'per_page',
		'ariaLabel' => 'Notifications pagination',
	])
</div>
@endsection

@push('styles')
<style>
	@include('admin.partials.table-pagination-styles')

	.office-card .table-pagination-bar {
		margin-top: 12px;
	}
</style>
@endpush

@push('scripts')
<script nonce="{{ $cspNonce }}">
	@include('admin.partials.table-pagination-script')
</script>
@endpush
