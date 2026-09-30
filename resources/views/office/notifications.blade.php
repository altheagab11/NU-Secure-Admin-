@php $activeNav = 'notifications'; @endphp
@extends('office.layouts.office-layout')

@section('title', 'Notifications')

@section('content')
<div class="office-card notif-page">
	<div class="notif-page-head">
		<div>
			<h2>Notifications</h2>
			<p class="card-muted mb-0">Incoming visitors and scan notices for {{ $office->office_name ?? 'your office' }}.</p>
		</div>
		@if(($notifications ?? collect())->count() > 0)
			<span class="notif-unread-chip">{{ ($notifications ?? collect())->count() }} unread</span>
		@endif
	</div>

	@if($notificationList->isEmpty())
		<div class="empty-state notif-empty">
			<i class="bi bi-bell" aria-hidden="true"></i>
			<p class="mb-1 fw-semibold">No notifications yet</p>
			<p class="mb-0 text-muted small">When a visitor is registered for your office, an incoming notice will appear here automatically.</p>
		</div>
	@else
		<div class="notif-list" role="list">
			@foreach($notificationList as $notif)
				@php
					$isIncoming = (bool) ($notif->is_incoming ?? false);
					$isUnread = (bool) ($notif->is_unread ?? empty($notif->read_at));
					$tone = $isIncoming ? 'incoming' : 'alert';
				@endphp
				<article class="notif-item tone-{{ $tone }} {{ $isUnread ? 'is-unread' : 'is-read' }}" role="listitem">
					<div class="notif-icon" aria-hidden="true">
						<i class="bi {{ $isIncoming ? 'bi-person-walking' : 'bi-exclamation-triangle' }}"></i>
					</div>
					<div class="notif-body">
						<div class="notif-meta">
							<span class="notif-type">{{ $notif->display_type ?? ($notif->notif_type_name ?: 'Notice') }}</span>
							<span class="notif-time">
								{{ $notif->sent_at ? \Carbon\Carbon::parse($notif->sent_at)->timezone('Asia/Manila')->format('M j, Y · g:i A') : '—' }}
							</span>
						</div>
						@if(!empty($notif->visitor_name))
							<h3 class="notif-title">{{ $notif->visitor_name }}</h3>
						@endif
						<p class="notif-message">{{ $notif->message }}</p>
						@if(!empty($notif->control_number))
							<span class="notif-control">Control No. {{ $notif->control_number }}</span>
						@endif
					</div>
					<div class="notif-actions">
						@if($isUnread)
							<span class="notif-status unread">Unread</span>
							<form method="POST" action="{{ route('office.notifications.read', $notif->notif_id) }}">
								@csrf
								<button type="submit" class="btn btn-sm btn-nu-outline">Mark read</button>
							</form>
						@else
							<span class="notif-status read">Read</span>
						@endif
					</div>
				</article>
			@endforeach
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
<style nonce="{{ $cspNonce }}">
	@include('admin.partials.table-pagination-styles')

	.notif-page-head {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 12px;
		margin-bottom: 18px;
	}

	.notif-page-head h2 {
		margin: 0 0 4px;
	}

	.notif-unread-chip {
		display: inline-flex;
		align-items: center;
		padding: 6px 10px;
		border-radius: 999px;
		background: #eef2ff;
		color: #273b9e;
		font-size: 12px;
		font-weight: 700;
		white-space: nowrap;
	}

	.notif-empty {
		padding: 36px 18px;
	}

	.notif-list {
		display: grid;
		gap: 12px;
	}

	.notif-item {
		display: grid;
		grid-template-columns: auto 1fr auto;
		gap: 14px;
		align-items: start;
		padding: 14px 16px;
		border: 1px solid #e8ecf1;
		border-radius: 14px;
		background: #fff;
	}

	.notif-item.is-unread {
		border-color: #c7d2fe;
		background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
		box-shadow: 0 1px 2px rgba(39, 59, 158, 0.06);
	}

	.notif-item.is-read {
		opacity: 0.88;
	}

	.notif-icon {
		width: 42px;
		height: 42px;
		border-radius: 12px;
		display: grid;
		place-items: center;
		flex-shrink: 0;
		font-size: 18px;
	}

	.notif-item.tone-incoming .notif-icon {
		background: #eef2ff;
		color: #273b9e;
	}

	.notif-item.tone-alert .notif-icon {
		background: #fff7ed;
		color: #c2410c;
	}

	.notif-meta {
		display: flex;
		flex-wrap: wrap;
		gap: 8px 12px;
		align-items: center;
		margin-bottom: 4px;
	}

	.notif-type {
		font-size: 11px;
		font-weight: 800;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		color: #273b9e;
	}

	.notif-item.tone-alert .notif-type {
		color: #c2410c;
	}

	.notif-time {
		font-size: 12px;
		color: #64748b;
	}

	.notif-title {
		margin: 0 0 4px;
		font-size: 16px;
		font-weight: 700;
		color: #0f172a;
		line-height: 1.25;
	}

	.notif-message {
		margin: 0;
		font-size: 13px;
		color: #475569;
		line-height: 1.45;
	}

	.notif-control {
		display: inline-flex;
		margin-top: 8px;
		padding: 4px 8px;
		border-radius: 999px;
		background: #f1f5f9;
		color: #334155;
		font-size: 11px;
		font-weight: 700;
	}

	.notif-actions {
		display: flex;
		flex-direction: column;
		align-items: flex-end;
		gap: 8px;
		min-width: 96px;
	}

	.notif-status {
		font-size: 11px;
		font-weight: 700;
		padding: 4px 8px;
		border-radius: 999px;
	}

	.notif-status.unread {
		background: #fef3c7;
		color: #92400e;
	}

	.notif-status.read {
		background: #f1f5f9;
		color: #64748b;
	}

	.office-card .table-pagination-bar {
		margin-top: 16px;
	}

	@media (max-width: 720px) {
		.notif-item {
			grid-template-columns: auto 1fr;
		}

		.notif-actions {
			grid-column: 1 / -1;
			flex-direction: row;
			justify-content: space-between;
			align-items: center;
			min-width: 0;
		}
	}
</style>
@endpush

@push('scripts')
<script nonce="{{ $cspNonce }}">
	@include('admin.partials.table-pagination-script')
</script>
@endpush
