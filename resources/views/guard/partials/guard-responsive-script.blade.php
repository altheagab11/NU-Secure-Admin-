<script nonce="{{ $cspNonce }}">
(function () {
	const backdrop = document.getElementById('guardNavBackdrop');
	const openBtn = document.getElementById('guardNavOpen');
	const layout = document.querySelector('.layout');
	const sidebar = layout ? layout.querySelector('.sidebar') : null;
	if (!backdrop || !openBtn || !sidebar) {
		return;
	}

	const mq = window.matchMedia('(max-width: 991.98px)');

	const setOpen = function (open) {
		document.body.classList.toggle('guard-sidebar-open', open);
		document.body.style.overflow = open ? 'hidden' : '';
		backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
		openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
	};

	const close = function () {
		setOpen(false);
	};

	openBtn.addEventListener('click', function () {
		setOpen(!document.body.classList.contains('guard-sidebar-open'));
	});

	backdrop.addEventListener('click', close);

	sidebar.querySelectorAll('a.sidebar-link, a.submenu-link, a.quick-action-btn').forEach(function (el) {
		el.addEventListener('click', function () {
			if (mq.matches) {
				close();
			}
		});
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			close();
		}
	});

	mq.addEventListener('change', function (e) {
		if (!e.matches) {
			close();
		}
	});
})();
</script>
