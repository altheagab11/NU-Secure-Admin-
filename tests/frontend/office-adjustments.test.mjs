import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const markup = readFileSync(new URL('../../resources/views/office/components/scan-result-modal.blade.php', import.meta.url), 'utf8');
const script = readFileSync(new URL('../../resources/views/office/partials/scan-scripts.blade.php', import.meta.url), 'utf8')
    .replace(/^<script[^>]*>/, '').replace(/<\/script>\s*$/, '')
    .replace(/@json\(route\('([^']+)'\)\)/g, (_, route) => JSON.stringify(route));

function harness() {
    class Element {
        constructor(id = '') {
            this.id = id;
            this.value = '';
            this.textContent = '';
            this.checked = id === 'officeAutomaticCheckIn';
            this.disabled = false;
            this.listeners = new Map();
            this.children = [];
            this.attributes = new Map();
            this.style = {};
            this.classes = new Set(['d-none']);
            this.classList = {
                add: value => this.classes.add(value),
                remove: value => this.classes.delete(value),
                contains: value => this.classes.has(value),
                toggle: (value, force) => force ? this.classes.add(value) : this.classes.delete(value),
            };
        }
        set className(value) { this.classes = new Set(value.split(' ')); }
        get className() { return [...this.classes].join(' '); }
        addEventListener(name, listener) { this.listeners.set(name, listener); }
        emit(name, event = {}) {
            return this.listeners.get(name)?.({ target: this, preventDefault() {}, ...event });
        }
        set innerHTML(value) { this.html = value; this.children = []; }
        get innerHTML() { return this.html || ''; }
        appendChild(child) { this.children.push(child); }
        remove() {}
        focus() {}
        scrollIntoView() {}
        setAttribute(name, value) { this.attributes.set(name, value); }
        reset() { elements.get('officeAutomaticCheckIn').checked = true; }
        querySelector() { return elements.get(this.id === 'addOfficeForm' ? 'addOfficeSubmit' : 'wrongOfficeSubmit'); }
        querySelectorAll() { return [...elements.values()]; }
    }
    const elements = new Map([...markup.matchAll(/id="([^"]+)"/g)].map(([, id]) => [id, new Element(id)]));
    elements.set('wrongOfficeSubmit', new Element());
    const cancelButton = new Element();
    const requests = [];
    const responses = [];
    const context = vm.createContext({
        window: {}, setTimeout() {},
        document: {
            getElementById: id => elements.get(id) || null,
            querySelector: () => ({ getAttribute: () => 'test-csrf' }),
            querySelectorAll: selector => selector === '[data-cancel-adjustment]' ? [cancelButton] : [],
            createElement: () => new Element(), body: new Element(),
        },
        bootstrap: { Modal: {
            getOrCreateInstance: element => ({ show() { element.classes.add('show'); } }),
            getInstance: element => ({ hide() { element.classes.delete('show'); } }),
        } },
        fetch: async (url, options) => {
            requests.push({ url, body: JSON.parse(options.body) });
            return await responses.shift()();
        },
    });
    vm.runInContext(script, context);
    context.window.OfficeScan.init({});
    const respond = (data, ok = true) => responses.push(() => ({ ok, json: async () => data }));
    const data = {
        visitor: { full_name: 'Test Visitor' }, visit: { control_number: 'CTRL-1' },
        staff_office: { office_id: 2, office_name: 'Admissions Office' },
        current_office: { office_id: 1, office_name: 'Health Services Office' },
        authorized: false, can_adjust_office: true, wrong_office_scan_id: 77,
    };
    const scan = async (override = {}) => {
        respond({ success: false, code: 'WRONG_OFFICE', message: 'Wrong office', expected_office: 'Health Services Office', data: { ...data, ...override } }, false);
        await context.window.OfficeScan.verify('QR-test', 'hardware');
    };
    return { elements, requests, responses, respond, data, scan, cancelButton, context };
}

test('wrong-office actions display assigned and expected offices, and cancellation restores verification', async () => {
    const h = harness();
    await h.scan();
    assert.equal(h.elements.get('officeVerificationActions').classList.contains('d-none'), false);
    await h.elements.get('showWrongOffice').emit('click');
    assert.equal(h.elements.get('scanResultModalLabel').textContent, 'Log Wrong Office Note');
    assert.equal(h.elements.get('wrongCurrentOffice').value, 'Admissions Office');
    assert.equal(h.elements.get('wrongExpectedOffice').value, 'Health Services Office');
    assert.equal(h.elements.get('wrongOfficePanel').classList.contains('d-none'), false);
    h.cancelButton.emit('click');
    assert.equal(h.elements.get('wrongOfficePanel').classList.contains('d-none'), true);
    assert.equal(h.elements.get('scanResultModalLabel').textContent, 'Visitor Verification');
});

test('authorized and previous-incomplete visitors do not get adjustment actions', async () => {
    for (const override of [{ authorized: true }, { can_adjust_office: false }]) {
        const h = harness();
        await h.scan(override);
        assert.equal(h.elements.get('officeVerificationActions').classList.contains('d-none'), true);
    }
});

test('adding without check-in sends the original scan and checkbox choice, then hides actions', async () => {
    const h = harness();
    await h.scan();
    h.elements.get('showAddOffice').emit('click');
    h.elements.get('officeAdditionReason').value = '  Follow-up appointment  ';
    const checkbox = h.elements.get('officeAutomaticCheckIn');
    checkbox.checked = false;
    checkbox.emit('change');
    assert.equal(h.elements.get('addOfficeSubmit').textContent, 'Add Office');
    h.respond({ success: true, message: 'Office added', data: { ...h.data, authorized: true, can_adjust_office: false, checked_in: false } });
    await h.elements.get('addOfficeForm').emit('submit');
    assert.equal(h.requests[1].url, 'office.scanner.add-office');
    assert.equal(h.requests[1].body.scan_id, 77);
    assert.equal(h.requests[1].body.automatic_check_in, false);
    assert.equal(h.requests[1].body.reason, 'Follow-up appointment');
    assert.equal(h.elements.get('officeVerificationActions').classList.contains('d-none'), true);
    assert.equal(h.elements.get('scanModalAlertText').textContent, 'Office added');
});

test('pending submissions block duplicates and dismissal; validation failures restore controls', async () => {
    const h = harness();
    await h.scan();
    h.elements.get('showWrongOffice').emit('click');
    h.elements.get('wrongOfficeRemarks').value = 'Visitor advised to go to Health Services.';
    let resolve;
    h.responses.push(() => new Promise(done => { resolve = done; }));
    const pending = h.elements.get('wrongOfficeForm').emit('submit');
    await h.elements.get('wrongOfficeForm').emit('submit');
    assert.equal(h.requests.length, 2);
    assert.equal(h.requests[1].body.scan_id, 77);
    assert.equal(h.elements.get('wrongOfficeSubmit').disabled, true);
    let dismissalPrevented = false;
    h.elements.get('scanResultModal').emit('hide.bs.modal', { preventDefault() { dismissalPrevented = true; } });
    assert.equal(dismissalPrevented, true);
    resolve({ ok: false, json: async () => ({ errors: { remarks: ['Please provide valid remarks.'] } }) });
    await pending;
    assert.equal(h.elements.get('wrongOfficeError').textContent, 'Please provide valid remarks.');
    assert.equal(h.elements.get('wrongOfficeSubmit').disabled, false);
});

test('blank reasons show validation feedback without submitting', async () => {
    const h = harness();
    await h.scan();
    h.elements.get('showAddOffice').emit('click');
    h.elements.get('officeAdditionReason').value = '   ';
    await h.elements.get('addOfficeForm').emit('submit');
    assert.equal(h.requests.length, 1);
    assert.equal(h.elements.get('addOfficeError').classList.contains('d-none'), false);
});

test('remaining route highlights the expected office and safely displays office names', async () => {
    const h = harness();
    await h.scan({ remaining_route: [
        { office_id: 3, office_name: '<Registrar>', state: 'current' },
        { office_id: 1, office_name: 'Health Services Office', state: 'current' },
    ] });
    const steps = h.elements.get('scanRemainingRoute').children;
    assert.equal(steps.length, 2);
    assert.equal(steps[0].classList.contains('is-next'), false);
    assert.equal(steps[0].children[1].children[0].textContent, '<Registrar>');
    assert.equal(steps[1].attributes.get('aria-current'), 'step');
    assert.equal(steps[1].children[1].children[1].textContent, 'Next destination');
    assert.equal(h.elements.get('scanStaffOffice').classList.contains('is-mismatch'), true);
    assert.equal(h.elements.get('scanVisitStatus').classList.contains('is-active'), true);
});

test('authorized scans stay successful without the removed Done button, and closing resumes scanning', async () => {
    const h = harness();
    let resumed = false;
    h.context.window.OfficeScan.init({ onResume() { resumed = true; } });
    h.respond({ success: true, message: 'Checked in', data: { ...h.data, authorized: true, checked_in: true, can_adjust_office: false } });
    await h.context.window.OfficeScan.verify('QR-test', 'camera');
    assert.equal(h.elements.has('scanConfirmBtn'), false);
    assert.equal(h.elements.has('scanWrongOfficeGuidance'), false);
    assert.equal(h.elements.get('scanModalAlertText').textContent, 'Checked in');
    assert.equal(h.elements.get('officeVerificationActions').classList.contains('d-none'), true);
    assert.equal(h.elements.get('scanAuthBadge').classList.contains('is-authorized'), true);
    assert.equal(h.elements.get('scanRemainingRoute').children[0].textContent, 'No remaining destinations.');
    h.elements.get('scanResultModal').emit('hidden.bs.modal');
    assert.equal(resumed, true);
});
