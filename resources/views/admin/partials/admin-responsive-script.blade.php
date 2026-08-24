<script nonce="{{ $cspNonce }}">
(function () {
	document.querySelectorAll('[data-auto-submit]').forEach(function (el) {
		el.addEventListener('change', function () {
			if (el.form) {
				el.form.submit();
			}
		});
	});

	document.querySelectorAll('form[data-confirm]').forEach(function (form) {
		form.addEventListener('submit', function (event) {
			if (!window.confirm(form.getAttribute('data-confirm') || 'Are you sure?')) {
				event.preventDefault();
			}
		});
	});

	document.querySelectorAll('form[data-prevent-submit]').forEach(function (form) {
		form.addEventListener('submit', function (event) {
			event.preventDefault();
		});
	});

	const layout = document.querySelector('.layout');
	const sidebar = layout ? layout.querySelector('.sidebar') : null;
	const openBtn = document.getElementById('adminNavOpen');
	if (!sidebar || !openBtn) {
		return;
	}

	let backdrop = document.getElementById('adminNavBackdrop');
	if (!backdrop) {
		backdrop = document.createElement('div');
		backdrop.id = 'adminNavBackdrop';
		backdrop.className = 'admin-nav-backdrop';
		backdrop.setAttribute('aria-hidden', 'true');
		document.body.appendChild(backdrop);
	}

	const mq = window.matchMedia('(max-width: 991.98px)');

	const setOpen = function (open) {
		document.body.classList.toggle('admin-sidebar-open', open);
		document.body.style.overflow = open ? 'hidden' : '';
		backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
		openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
	};

	const close = function () {
		setOpen(false);
	};

	openBtn.addEventListener('click', function () {
		setOpen(!document.body.classList.contains('admin-sidebar-open'));
	});

	backdrop.addEventListener('click', close);

	sidebar.querySelectorAll('a.sidebar-link, a.submenu-link').forEach(function (el) {
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

	if (typeof mq.addEventListener === 'function') {
		mq.addEventListener('change', function (e) {
			if (!e.matches) {
				close();
			}
		});
	} else if (typeof mq.addListener === 'function') {
		mq.addListener(function (e) {
			if (!e.matches) {
				close();
			}
		});
	}
})();
</script>
