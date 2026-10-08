document.addEventListener('error', event => {
    if (event.target instanceof HTMLImageElement && event.target.classList.contains('guard-visitor-photo')) {
        event.target.hidden = true; event.target.nextElementSibling.hidden = false;
    }
}, true);
document.querySelectorAll('[data-preview-filters]').forEach(form => {
    const kind = form.dataset.previewFilters;
    const records = document.getElementById(kind + 'Preview');
    const pagination = document.getElementById(kind + 'Pagination');
    const size = pagination.querySelector('.table-page-size');
    let page = 1, perPage = 5, request = null, timer = null;
    async function loadPage() {
        request?.abort();
        const current = new AbortController(); request = current;
        const params = new URLSearchParams(new FormData(form));
        params.set('list', kind); params.set('page', page); params.set('per_page', perPage);
        records.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(@json(url('/guard/alert')) + '?' + params, {signal: current.signal, credentials: 'same-origin', cache: 'no-store', headers: {Accept: 'application/json'}});
            const payload = await response.json();
            if (!response.ok || !payload.success) throw new Error(payload.message || 'Unable to load records. Please try again.');
            if (request !== current) return;
            records.innerHTML = payload.html;
            (payload.alerts || []).forEach(alert => {
                const index = ALERTS.findIndex(row => String(row.alert_id) === String(alert.alert_id));
                if (index < 0) ALERTS.push(alert); else ALERTS[index] = alert;
            });
            const meta = payload.meta; page = meta.current_page;
            pagination.hidden = !meta.total;
            pagination.querySelector('.table-pagination-range').textContent = `${meta.from} to ${meta.to} of ${meta.total}`;
            pagination.querySelector('.table-pagination-page').innerHTML = `Page <strong>${page}</strong> of ${meta.last_page}`;
            const targets = [1, page - 1, page + 1, meta.last_page];
            pagination.querySelectorAll('.table-pagination-nav').forEach((link, index) => {
                const disabled = index < 2 ? page <= 1 : page >= meta.last_page;
                link.dataset.page = targets[index]; link.href = '#'; link.tabIndex = disabled ? -1 : 0;
                link.classList.toggle('is-disabled', disabled); link.setAttribute('aria-disabled', String(disabled));
            });
        } catch (error) {
            if (error.name === 'AbortError' || request !== current) return;
            pagination.hidden = true;
            const message = document.createElement('p'); message.className = 'guard-list-error'; message.setAttribute('role', 'alert'); message.textContent = error.message;
            const retry = document.createElement('button'); retry.type = 'button'; retry.className = 'alert-action-btn'; retry.textContent = 'Try again'; retry.addEventListener('click', loadPage);
            records.replaceChildren(message, retry);
        } finally { if (request === current) { records.setAttribute('aria-busy', 'false'); request = null; } }
    }
    const apply = () => { clearTimeout(timer); page = 1; loadPage(); };
    form.addEventListener('submit', event => { event.preventDefault(); apply(); });
    form.querySelector('input').addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(apply, 350); });
    form.querySelectorAll('select').forEach(select => select.addEventListener('change', apply));
    size.addEventListener('change', () => { perPage = Number(size.value); apply(); });
    pagination.addEventListener('click', event => {
        const link = event.target.closest('.table-pagination-nav'); if (!link) return;
        event.preventDefault(); if (link.classList.contains('is-disabled') || link.getAttribute('aria-disabled') === 'true') return;
        clearTimeout(timer);
        page = Number(link.dataset.page || new URL(link.href, location.href).searchParams.get('page') || 1);
        loadPage();
    });
});