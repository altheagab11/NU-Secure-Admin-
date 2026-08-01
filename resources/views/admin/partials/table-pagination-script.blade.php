document.querySelectorAll('.table-page-size').forEach((select) => {
	select.addEventListener('change', () => {
		const params = new URLSearchParams(window.location.search);
		const perPageParam = select.dataset.perPageParam;
		const pageParam = select.dataset.pageParam;

		if (perPageParam) {
			params.set(perPageParam, select.value);
		}
		if (pageParam) {
			params.set(pageParam, '1');
		}

		const query = params.toString();
		window.location.href = window.location.pathname + (query ? `?${query}` : '');
	});
});
