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

	function fitAdminSidebar() {
		if (!sidebar) {
			return;
		}

		var nav = sidebar.children[0];
		var footer = sidebar.querySelector('.sidebar-footer');
		if (!nav) {
			return;
		}

		if (!nav.classList.contains('sidebar-fit-inner')) {
			nav.classList.add('sidebar-fit-inner');
		}

		nav.style.transform = 'scale(1)';
		nav.style.marginBottom = '0px';

		var styles = window.getComputedStyle(sidebar);
		var padY = (parseFloat(styles.paddingTop) || 0) + (parseFloat(styles.paddingBottom) || 0);
		var footerH = footer ? footer.offsetHeight : 0;
		var available = sidebar.clientHeight - padY - footerH;
		var used = nav.scrollHeight;
		var scale = used > available && available > 0 ? Math.max(0.72, available / used) : 1;

		nav.style.transform = 'scale(' + scale + ')';
		nav.style.marginBottom = ((scale - 1) * used) + 'px';
	}

	fitAdminSidebar();
	window.addEventListener('resize', fitAdminSidebar);
	window.addEventListener('orientationchange', fitAdminSidebar);
	window.addEventListener('load', fitAdminSidebar);
	setTimeout(fitAdminSidebar, 250);

	var userMenuToggle = document.getElementById('userMenuToggle');
	var userMenuGroup = document.getElementById('userMenuGroup');
	if (userMenuToggle && userMenuGroup) {
		userMenuToggle.addEventListener('click', function () {
			setTimeout(fitAdminSidebar, 50);
		});
	}

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
		setTimeout(fitAdminSidebar, 40);
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
			fitAdminSidebar();
		});
	} else if (typeof mq.addListener === 'function') {
		mq.addListener(function (e) {
			if (!e.matches) {
				close();
			}
			fitAdminSidebar();
		});
	}
})();
</script>
