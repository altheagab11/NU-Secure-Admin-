import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';

function harness() {
    const view = fs.readFileSync(new URL('../../resources/views/admin/guard-duty.blade.php', import.meta.url), 'utf8');
    let script = view.match(/<script nonce="\{\{ \$cspNonce \}\}">([\s\S]*?)<\/script>/)[1];
    script = script.replace(/@json\([^\n]+\);/g, "'/test-api';");
    script = script.replace(/toggleCustomRange\(\);\s*loadDuty\(\);\s*loadSummary\(\);\s*loadFilters\(\);\s*\}\)\(\);/, 'globalThis.duty = { loadDuty, loadSummary, state }; })();');
    const elements = new Map();
    const requests = [];
    const context = {
        URLSearchParams, AbortController, Date, console,
        setInterval() {}, setTimeout, clearTimeout,
        bootstrap: { Offcanvas: class {} },
        document: {
            getElementById(id) {
                if (!elements.has(id)) elements.set(id, {
                    value: '', textContent: '', innerHTML: '',
                    classList: { add() {}, remove() {}, toggle() {} },
                    addEventListener() {}, setAttribute() {},
                });
                return elements.get(id);
            },
        },
        fetch(url, options) {
            // Deliberately allow an aborted response to finish to test the stale-response guard.
            return new Promise(resolve => requests.push({ url, options, resolve }));
        },
    };
    vm.runInNewContext(script, context);
    return { duty: context.duty, requests, elements };
}

const history = page => ({ success: true, history: { data: [], meta: { current_page: page, last_page: 4, total: 20 } } });
const finish = (request, payload) => request.resolve({ ok: true, json: async () => payload });

test('duplicate history requests share one fetch and exclude summary work', async () => {
    const { duty, requests } = harness();
    const first = duty.loadDuty();
    const duplicate = duty.loadDuty();
    assert.equal(requests.length, 1);
    assert.match(requests[0].url, /include_summary=0/);
    finish(requests[0], history(1));
    await Promise.all([first, duplicate]);
    assert.equal(duty.state.historyRequest, null);
});

test('new pagination aborts an old request and ignores its late response', async () => {
    const { duty, requests } = harness();
    const first = duty.loadDuty();
    duty.state.page = 2;
    const second = duty.loadDuty();
    assert.equal(requests[0].options.signal.aborted, true);
    finish(requests[1], history(2));
    await second;
    finish(requests[0], history(1));
    await first;
    assert.equal(duty.state.page, 2);
});

test('a slow summary does not delay history rendering and duplicate summaries are deduplicated', async () => {
    const { duty, requests, elements } = harness();
    const summary = duty.loadSummary();
    duty.loadSummary();
    const list = duty.loadDuty();
    assert.equal(requests.length, 2);
    finish(requests[1], history(3));
    await list;
    assert.equal(duty.state.page, 3);
    assert.match(elements.get('dutyPaginationRange').textContent, /of 20/);
    finish(requests[0], { success: true, current: [], last_completed: null });
    await summary;
    assert.equal(duty.state.summaryRequest, null);
});
