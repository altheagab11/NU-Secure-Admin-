const escapeHtml = value => String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const initialsOf = name => String(name || 'Visitor').trim().split(/\s+/).slice(0, 2).map(part => part.charAt(0).toUpperCase()).join('');
const badgeClass = status => ({ valid: 'badge-success', invalid: 'badge-danger', unauthorized: 'badge-danger', ready: 'badge-info', waiting: 'badge-warning', checked_in: 'badge-success', success: 'badge-success', warning: 'badge-warning', muted: 'badge-muted', danger: 'badge-danger' }[String(status || '').toLowerCase()] || 'badge-info');
const emptyMessage = kind => kind === 'scans' ? 'No scans yet today.' : kind === 'ready' ? 'No visitors waiting to be scanned.' : 'No visitors are currently expected at your office.';

function renderRecords(root, rows, kind, full = false) {
    if (!rows.length) {
        root.innerHTML = `<div class="empty-state dashboard-empty"><i class="bi bi-${kind === 'scans' ? 'qr-code-scan' : 'people'}" aria-hidden="true"></i><p class="mb-1">${emptyMessage(kind)}</p>${kind === 'scans' ? '<p class="card-muted mb-0">QR scans made at this office will appear here.</p>' : ''}</div>`;
        return;
    }
    root.innerHTML = rows.map(row => {
        const name = row.visitor_name || 'Visitor';
        const status = kind === 'ready' ? (full ? row.status || 'Ready' : 'Ready') : kind === 'scans' ? row.validation_status || '—' : row.route_status || 'Expected';
        const tone = kind === 'ready' ? 'ready' : kind === 'scans' ? status : row.badge;
        const photo = row.photo_url ? `<img src="${escapeHtml(row.photo_url)}" alt="${escapeHtml(name)}" class="dashboard-visitor-photo" loading="lazy"><span class="dashboard-avatar d-none" aria-hidden="true">${escapeHtml(initialsOf(name))}</span>` : `<span class="dashboard-avatar" aria-hidden="true">${escapeHtml(initialsOf(name))}</span>`;
        const meta = [];
        if (kind === 'ready' || (kind === 'expected' && full)) meta.push(`From ${row.previous_office || 'Main Lobby'}`);
        if (kind === 'scans') meta.push(row.time_label || '—');
        if (kind === 'expected' || full) meta.push(row.purpose || '—');
        if (full && kind === 'expected') meta.push(`Expected: ${row.expected_label || '—'}`);
        if (full && kind === 'ready' && row.route_progress) meta.push(`Route: ${row.route_progress}`);
        const scan = kind === 'ready' ? `${scannerUrl}?visit=${encodeURIComponent(row.visit_id)}` : kind === 'expected' && row.route_status_key === 'ready' ? row.scan_url : null;
        const detail = row.view_url || `${visitorUrl}/${encodeURIComponent(row.visit_id)}`;
        const details = full ? `<div class="dashboard-record-meta">${escapeHtml(row.control_number || '—')}</div>${meta.map(value => `<div class="dashboard-record-meta">${escapeHtml(value)}</div>`).join('')}` : `<div class="dashboard-record-meta">${[row.control_number || '—', ...meta].map(escapeHtml).join(' · ')}</div>`;
        return `<article class="dashboard-record"><div class="dashboard-record-main">${photo}<div class="dashboard-record-copy"><div class="dashboard-record-name">${escapeHtml(name)}</div>${details}</div></div><div class="dashboard-record-actions"><span class="badge-status ${badgeClass(tone)}">${escapeHtml(status)}</span>${scan ? `<a href="${escapeHtml(scan)}" class="btn btn-sm btn-nu-primary">Scan</a>` : ''}${kind !== 'ready' || full ? `<a href="${escapeHtml(detail)}" class="record-detail-link" aria-label="View ${escapeHtml(name)}">View</a>` : ''}</div></article>`;
    }).join('');
}

// Use a capturing listener because image error events do not bubble; no inline handlers under CSP.
document.addEventListener('error', event => {
    const photo = event.target;
    if (photo instanceof HTMLImageElement && photo.classList.contains('dashboard-visitor-photo')) {
        photo.classList.add('d-none');
        photo.nextElementSibling?.classList.remove('d-none');
    }
}, true);

const modalEl = document.getElementById('dashboardListModal');
const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
const fields = {
    search: document.getElementById('dashboardListSearch'), status: document.getElementById('dashboardListStatus'),
    office: document.getElementById('dashboardListOffice'), records: document.getElementById('dashboardListRecords'),
    error: document.getElementById('dashboardListError'), pagination: document.getElementById('dashboardListPagination'),
};
const states = Object.fromEntries(['ready', 'scans', 'expected'].map(kind => [kind, { page: 1, perPage: 10, search: '', status: '', previousOffice: '', lastPage: 1, options: null }]));
let activeKind = null;
let modalRequest = null;
let searchTimer = null;
let polling = false;

async function fetchJson(url, signal) {
    const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store', signal });
    const payload = await response.json();
    if (!response.ok || !payload.success) throw new Error(payload.message || 'Unable to load this list. Please try again.');
    return payload;
}

function setOptions(select, values, selected, allLabel) {
    select.replaceChildren(new Option(allLabel, ''), ...values.map(value => new Option(String(value).replace(/_/g, ' '), value)));
    select.value = selected;
}

function updatePagination(meta) {
    const state = states[activeKind];
    state.page = meta.current_page;
    state.lastPage = meta.last_page;
    fields.pagination.hidden = !meta.total;
    fields.pagination.querySelector('.table-page-size').value = String(state.perPage);
    fields.pagination.querySelector('.table-pagination-range').textContent = `${meta.from} to ${meta.to} of ${meta.total}`;
    fields.pagination.querySelector('.table-pagination-page').textContent = `Page ${meta.current_page} of ${meta.last_page}`;
    const pages = [1, Math.max(1, state.page - 1), Math.min(state.lastPage, state.page + 1), state.lastPage];
    fields.pagination.querySelectorAll('.table-pagination-nav').forEach((link, index) => {
        const disabled = index < 2 ? state.page <= 1 : state.page >= state.lastPage;
        link.dataset.page = pages[index];
        link.href = '#';
        link.classList.toggle('is-disabled', disabled);
        link.setAttribute('aria-disabled', String(disabled));
        link.tabIndex = disabled ? -1 : 0;
    });
}

async function loadList() {
    if (!activeKind) return;
    modalRequest?.abort();
    const request = new AbortController();
    modalRequest = request;
    const kind = activeKind;
    const state = states[kind];
    const params = new URLSearchParams({ dashboard_list: kind, page: state.page, per_page: state.perPage, search: state.search, status: state.status, previous_office: state.previousOffice });
    fields.error.classList.add('d-none');
    fields.pagination.hidden = true;
    fields.records.setAttribute('aria-busy', 'true');
    fields.records.innerHTML = '<div class="dashboard-empty text-center" role="status"><span class="spinner-border spinner-border-sm text-primary me-2" aria-hidden="true"></span>Loading records…</div>';
    try {
        const payload = await fetchJson(`${liveUrl}?${params}`, request.signal);
        if (modalRequest !== request || activeKind !== kind) return;
        state.options = payload.filters;
        setOptions(fields.status, payload.filters.statuses || [], state.status, 'All statuses');
        setOptions(fields.office, payload.filters.previous_offices || [], state.previousOffice, 'All previous offices');
        renderRecords(fields.records, payload.data || [], kind, true);
        updatePagination(payload.meta);
    } catch (error) {
        if (error.name === 'AbortError' || modalRequest !== request) return;
        fields.error.textContent = error.message;
        fields.error.classList.remove('d-none');
        fields.records.innerHTML = '<div class="text-center py-3"><button type="button" class="btn btn-nu-outline" data-list-retry>Try again</button></div>';
    } finally {
        if (modalRequest === request) { fields.records.setAttribute('aria-busy', 'false'); modalRequest = null; }
    }
}

document.querySelectorAll('[data-dashboard-list]').forEach(button => button.addEventListener('click', () => {
    clearTimeout(searchTimer);
    activeKind = button.dataset.dashboardList;
    const state = states[activeKind];
    fields.search.value = state.search;
    setOptions(fields.status, state.options?.statuses || [], state.status, 'All statuses');
    setOptions(fields.office, state.options?.previous_offices || [], state.previousOffice, 'All previous offices');
    document.getElementById('dashboardListOfficeField').hidden = activeKind === 'scans';
    document.getElementById('dashboardListTitle').textContent = { ready: 'Ready to Scan', scans: "Today's Recent Scans", expected: 'Expected Visitors' }[activeKind];
    document.getElementById('dashboardListSubtitle').textContent = activeKind === 'ready' ? 'Only visitors eligible for this office are shown.' : activeKind === 'scans' ? "All of today's scans at this office." : 'Full visit and routing information for this office.';
    modal.show();
    loadList();
}));

function applyFilters() {
    clearTimeout(searchTimer);
    if (!activeKind) return;
    Object.assign(states[activeKind], { page: 1, search: fields.search.value.trim(), status: fields.status.value, previousOffice: fields.office.value });
    loadList();
}
document.getElementById('dashboardListFilters').addEventListener('submit', event => { event.preventDefault(); applyFilters(); });
document.getElementById('dashboardListFilters').addEventListener('reset', event => {
    event.preventDefault();
    fields.search.value = ''; fields.status.value = ''; fields.office.value = '';
    applyFilters();
});
fields.search.addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(applyFilters, 350); });
fields.status.addEventListener('change', applyFilters);
fields.office.addEventListener('change', applyFilters);
fields.pagination.querySelector('.table-page-size').addEventListener('change', event => {
    states[activeKind].perPage = Number(event.target.value); states[activeKind].page = 1; loadList();
});
fields.pagination.addEventListener('click', event => {
    const link = event.target.closest('.table-pagination-nav');
    if (!link) return;
    event.preventDefault();
    if (link.getAttribute('aria-disabled') === 'true') return;
    states[activeKind].page = Number(link.dataset.page); loadList();
});
fields.records.addEventListener('click', event => { if (event.target.closest('[data-list-retry]')) loadList(); });
modalEl.addEventListener('hide.bs.modal', () => {
    clearTimeout(searchTimer); modalRequest?.abort(); modalRequest = null; activeKind = null;
});

async function refreshDashboard() {
    if (document.hidden || polling || activeKind || document.querySelector('#scanResultModal.show, #manualPayloadModal.show')) return;
    polling = true;
    try {
        // The dashboard preview always uses the first five rows, independently of modal pagination.
        const payload = await fetchJson(`${liveUrl}?ready_page=1&ready_per_page=5&scans_page=1&scans_per_page=5&expected_page=1&expected_per_page=5`);
        if (activeKind || document.querySelector('#scanResultModal.show, #manualPayloadModal.show')) return;
        Object.entries(payload.stats || {}).forEach(([key, value]) => {
            const node = document.querySelector(`[data-stat="${key}"]`);
            if (node) node.textContent = Number(value || 0).toLocaleString();
        });
        renderRecords(document.getElementById('liveWaiting'), (payload.live?.waiting || []).slice(0, 5), 'ready');
        renderRecords(document.getElementById('recentActivityWrap'), (payload.recent_activity?.data || []).slice(0, 5), 'scans');
        renderRecords(document.getElementById('expectedVisitorsWrap'), (payload.expected_visitors?.data || []).slice(0, 5), 'expected');
        const latest = payload.live?.latest_scan;
        const latestTime = latest?.scan_time ? new Date(latest.scan_time) : null;
        const latestLabel = latestTime && !Number.isNaN(latestTime.getTime()) ? new Intl.DateTimeFormat('en-US', { timeZone: 'Asia/Manila', hour: 'numeric', minute: '2-digit' }).format(latestTime) : '';
        document.getElementById('latestScanStatus').textContent = latest ? `Latest scan: ${latest.status_name || '—'}${latestLabel ? ' · ' + latestLabel : ''}` : '';
        const pulse = document.getElementById('livePulse');
        pulse.textContent = 'Updated'; setTimeout(() => { pulse.textContent = 'Live'; }, 1200);
    } catch (error) {
        // Keep the last successful preview and the existing scanner usable between polls.
    } finally { polling = false; }
}
setInterval(refreshDashboard, 20000);
