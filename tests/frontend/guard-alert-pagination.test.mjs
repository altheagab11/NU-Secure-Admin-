import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';

test('Active Alerts pagination keeps lists independent and resets the page for filters and page size', async () => {
    const node = () => ({
        dataset: {}, attrs: {}, handlers: {}, classList: { contains: () => false, toggle() {} },
        addEventListener(type, handler) { this.handlers[type] = handler; },
        setAttribute(name, value) { this.attrs[name] = value; },
        getAttribute(name) { return this.attrs[name]; },
    });
    const roots = {}, forms = [], calls = [];
    for (const kind of ['completed', 'alerts']) {
        const size = node(), range = node(), label = node(), input = node(), select = node();
        const links = Array.from({ length: 4 }, (_, index) => ({ ...node(), href: `http://localhost/guard/alert?page=${[1, 1, 2, 6][index]}` }));
        const pagination = node();
        pagination.querySelector = selector => selector === '.table-page-size' ? size : selector === '.table-pagination-range' ? range : label;
        pagination.querySelectorAll = () => links;
        Object.assign(pagination, { size, range, links });
        roots[kind + 'Pagination'] = pagination;
        roots[kind + 'Preview'] = node();
        const form = node(); form.dataset.previewFilters = kind; form.filters = {};
        form.querySelector = () => input; form.querySelectorAll = () => [select]; form.select = select;
        forms.push(form);
    }
    const source = fs.readFileSync('resources/views/guard/partials/active-alert-script.blade.php', 'utf8').replace("@json(url('/guard/alert'))", "'http://localhost/guard/alert'");
    vm.runInNewContext(source, {
        document: { addEventListener() {}, querySelectorAll: () => forms, getElementById: id => roots[id] },
        ALERTS: [], URLSearchParams, URL, AbortController, clearTimeout, setTimeout, location: { href: 'http://localhost/guard/alert' },
        FormData: class { constructor(form) { this.form = form; } *[Symbol.iterator]() { yield* Object.entries(this.form.filters); } },
        fetch: async url => {
            const params = new URL(url).searchParams; calls.push(params);
            const page = Number(params.get('page')), size = Number(params.get('per_page'));
            return { ok: true, json: async () => ({ success: true, html: 'records-' + page, alerts: [], meta: { current_page: page, last_page: Math.ceil(28 / size), total: 28, from: (page - 1) * size + 1, to: Math.min(page * size, 28) } }) };
        },
    });
    const flush = () => new Promise(resolve => setImmediate(resolve));
    const pagination = roots.completedPagination;
    pagination.handlers.click({ target: { closest: () => pagination.links[3] }, preventDefault() {} });
    await flush();
    assert.equal(pagination.range.textContent, '26 to 28 of 28');
    assert.equal(roots.alertsPreview.innerHTML, undefined);
    pagination.size.value = '10'; pagination.size.handlers.change(); await flush();
    assert.equal(pagination.range.textContent, '1 to 10 of 28');
    forms[0].filters.status = 'Completed'; forms[0].select.handlers.change(); await flush();
    assert.equal(calls.at(-1).get('page'), '1');
    assert.equal(calls.at(-1).get('per_page'), '10');
    assert.equal(calls.at(-1).get('status'), 'Completed');
});
