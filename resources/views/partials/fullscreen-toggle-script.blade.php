<script nonce="{{ $cspNonce }}">
(function () {
	const buttons = document.querySelectorAll('.js-fullscreen-toggle');
	if (!buttons.length) {
		return;
	}

	const docEl = document.documentElement;

	const getFullscreenElement = function () {
		return document.fullscreenElement
			|| document.webkitFullscreenElement
			|| document.msFullscreenElement
			|| null;
	};

	const requestFullscreen = async function () {
		try {
			if (getFullscreenElement()) {
				return;
			}
			if (typeof docEl.requestFullscreen === 'function') {
				await docEl.requestFullscreen();
			} else if (typeof docEl.webkitRequestFullscreen === 'function') {
				docEl.webkitRequestFullscreen();
			} else if (typeof docEl.msRequestFullscreen === 'function') {
				docEl.msRequestFullscreen();
			}
		} catch (error) {
			console.error('Fullscreen mode failed:', error);
		}
	};

	const exitFullscreen = async function () {
		try {
			if (!getFullscreenElement()) {
				return;
			}
			if (typeof document.exitFullscreen === 'function') {
				await document.exitFullscreen();
			} else if (typeof document.webkitExitFullscreen === 'function') {
				document.webkitExitFullscreen();
			} else if (typeof document.msExitFullscreen === 'function') {
				document.msExitFullscreen();
			}
		} catch (error) {
			console.error('Exit fullscreen failed:', error);
		}
	};

	const syncButtons = function () {
		const active = Boolean(getFullscreenElement());
		buttons.forEach(function (btn) {
			btn.setAttribute('aria-pressed', active ? 'true' : 'false');
			btn.classList.toggle('is-fullscreen-active', active);
			const label = active ? 'Exit full screen' : 'Enter full screen';
			btn.setAttribute('aria-label', label);
			btn.setAttribute('title', label);
			const icon = btn.querySelector('[data-fullscreen-icon]');
			if (icon) {
				icon.className = active ? 'bi bi-fullscreen-exit' : 'bi bi-arrows-fullscreen';
				icon.setAttribute('data-fullscreen-icon', '');
			}
			const text = btn.querySelector('[data-fullscreen-label]');
			if (text) {
				text.textContent = active ? 'Exit Full Screen' : 'Full Screen';
			}
		});
	};

	buttons.forEach(function (btn) {
		btn.addEventListener('click', function () {
			if (getFullscreenElement()) {
				exitFullscreen();
			} else {
				requestFullscreen();
			}
		});
	});

	['fullscreenchange', 'webkitfullscreenchange', 'MSFullscreenChange'].forEach(function (evt) {
		document.addEventListener(evt, syncButtons);
	});

	syncButtons();
})();
</script>
