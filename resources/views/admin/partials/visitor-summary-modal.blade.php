<dialog id="visitorSummaryModal" class="summary-modal" aria-labelledby="summaryModalTitle">
    <div class="summary-modal-header">
        <h2 id="summaryModalTitle">All Recent Visitors</h2>
        <button type="button" class="summary-modal-close" aria-label="Close summary modal">Close</button>
    </div>
    <form class="summary-modal-filters">
        <label>Search<input type="search" name="summary_search" placeholder="Visitor, control number, or office"></label>
        <label>Office<select name="summary_office"><option value="">All offices</option>@foreach($officeOptions as $office)<option value="{{ $office }}">{{ $office }}</option>@endforeach</select></label>
        <label data-recent-filter>Status<select name="summary_status"><option value="">All statuses</option>@foreach($statusOptions as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></label>
        <label data-recent-filter>Visit Type<select name="summary_visit_type"><option value="">All visit types</option>@foreach($visitTypeOptions as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach</select></label>
        <button type="submit" class="summary-view-all">Search</button>
    </form>
    <div class="summary-modal-results" aria-live="polite" aria-busy="false"></div>
</dialog>
@foreach(['recent' => $recentVisitors, 'scans' => $correctOfficeScans] as $summaryKind => $previewRecords)
    @php
        $initialRecords = clone $previewRecords;
        $initialRecords->setPageName('summary_page')->appends(['summary' => $summaryKind, 'summary_per_page' => 5]);
    @endphp
    <template id="summaryInitial-{{ $summaryKind }}">
        @include('admin.partials.visitor-summary-records', ['records' => $initialRecords, 'kind' => $summaryKind])
    </template>
@endforeach
<script nonce="{{ $cspNonce }}">
(() => {
    const modal = document.getElementById('visitorSummaryModal');
    const form = modal.querySelector('form');
    const results = modal.querySelector('.summary-modal-results');
    let kind = 'recent', pageSize = '5', trigger = null, controller = null, timer = null;
    async function load(page = 1) {
        if (controller) controller.abort();
        controller = new AbortController();
        const signal = controller.signal;
        const url = new URL(window.location.href);
        // Summary state is sent independently; the browser URL and main table stay intact.
        for (const [key, value] of new FormData(form)) url.searchParams.set(key, value);
        url.searchParams.set('summary', kind);
        url.searchParams.set('summary_page', page);
        url.searchParams.set('summary_per_page', pageSize);
        results.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store', signal });
            if (!response.ok) throw new Error('Unable to load records');
            const payload = await response.json();
            if (!signal.aborted && modal.open) results.innerHTML = payload.html;
        } catch (error) {
            if (error.name !== 'AbortError') results.innerHTML = '<p class="summary-modal-message">Unable to load records. Use Search to try again.</p>';
        } finally {
            if (!signal.aborted) results.setAttribute('aria-busy', 'false');
        }
    }
    document.querySelectorAll('[data-summary]').forEach(button => button.addEventListener('click', () => {
        trigger = button;
        kind = button.dataset.summary;
        pageSize = '5';
        form.reset();
        form.querySelectorAll('[data-recent-filter]').forEach(label => {
            label.hidden = kind !== 'recent';
            label.querySelector('select').disabled = kind !== 'recent';
        });
        document.getElementById('summaryModalTitle').textContent = kind === 'recent' ? 'All Recent Visitors' : 'All Correct Office Scans';
        results.innerHTML = document.getElementById('summaryInitial-' + kind).innerHTML;
        modal.showModal();
        load();
    }));
    modal.querySelector('.summary-modal-close').addEventListener('click', () => modal.close());
    modal.addEventListener('click', event => { if (event.target === modal) modal.close(); });
    modal.addEventListener('close', () => {
        clearTimeout(timer);
        if (controller) controller.abort();
        if (trigger) trigger.focus();
    });
    form.addEventListener('submit', event => { event.preventDefault(); clearTimeout(timer); load(); });
    form.addEventListener('input', event => {
        if (event.target.type !== 'search') return;
        clearTimeout(timer);
        timer = setTimeout(() => load(), 300);
    });
    form.addEventListener('change', () => { clearTimeout(timer); load(); });
    // Capture modal controls so existing page-level pagination handlers cannot navigate.
    modal.addEventListener('change', event => {
        if (!event.target.matches('[data-per-page-param]')) return;
        event.stopImmediatePropagation();
        pageSize = event.target.value;
        load();
    }, true);
    modal.addEventListener('click', event => {
        const link = event.target.closest('.table-pagination-nav');
        if (!link) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        if (link.getAttribute('aria-disabled') !== 'true') load(new URL(link.href).searchParams.get('summary_page') || 1);
    }, true);
})();
</script>
