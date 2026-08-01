{{--
  Auto-refresh when campus data changes (visits, scans, alerts, office progress).
  Pauses while modals/drawers are open, inputs are focused, or the tab is hidden.
--}}
@php
	$__liveRefreshIntervalMs = (int) ($liveRefreshIntervalMs ?? 10000);
	$__liveRefreshPauseSelectors = $liveRefreshPauseSelectors ?? [
		'#alertModal',
		'#resolveModal',
		'#visitorDetailModal.open',
		'#visitorDrawer.active',
		'.visitor-drawer.active',
		'.resolve-flow-modal',
		'.alert-modal',
	];
@endphp
<script>
(function () {
	const pollUrl = @json(url('/live/status'));
	const intervalMs = @json($__liveRefreshIntervalMs);
	const pauseSelectors = @json($__liveRefreshPauseSelectors);

	let currentFingerprint = null;
	let inFlight = false;
	let reloadScheduled = false;

	const isVisibleOverlay = function (el) {
		if (!el) return false;
		const style = window.getComputedStyle(el);
		if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
			return false;
		}
		if (el.classList.contains('open') || el.classList.contains('active')) return true;
		return style.display !== 'none';
	};

	const shouldPause = function () {
		if (document.hidden) return true;
		if (reloadScheduled || inFlight) return true;

		const active = document.activeElement;
		if (active) {
			const tag = String(active.tagName || '').toLowerCase();
			if (tag === 'input' || tag === 'textarea' || tag === 'select' || active.isContentEditable) {
				return true;
			}
		}

		return pauseSelectors.some(function (selector) {
			const nodes = document.querySelectorAll(selector);
			return Array.from(nodes).some(function (node) {
				return isVisibleOverlay(node);
			});
		});
	};

	const reloadPage = function () {
		if (reloadScheduled) return;
		reloadScheduled = true;
		window.location.reload();
	};

	const poll = async function () {
		if (shouldPause()) return;
		inFlight = true;
		try {
			const response = await fetch(pollUrl, {
				headers: { Accept: 'application/json' },
				cache: 'no-store',
				credentials: 'same-origin',
			});
			if (!response.ok) return;
			const payload = await response.json();
			if (!payload || !payload.success || !payload.fingerprint) return;

			if (currentFingerprint === null) {
				currentFingerprint = payload.fingerprint;
				return;
			}

			if (payload.fingerprint !== currentFingerprint) {
				reloadPage();
			}
		} catch (error) {
			// Silent — keep page usable between polls.
		} finally {
			inFlight = false;
		}
	};

	document.addEventListener('visibilitychange', function () {
		if (!document.hidden) {
			poll();
		}
	});

	setInterval(poll, intervalMs);
	poll();
})();
</script>
