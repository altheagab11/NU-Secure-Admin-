import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const view = readFileSync(new URL('../../resources/views/guard/register.blade.php', import.meta.url), 'utf8');
const helpers = view.slice(view.indexOf('const escapeTicketHtml ='), view.indexOf('const toTitleCase ='));
const renderers = view.slice(view.indexOf('const createKioskOfficeChoice ='), view.indexOf('const resetRegistrationFlowToStepOne ='));

function harness({ kiosk = true, type = 'normal' } = {}) {
    class Element {
        constructor(tag = '') { this.tag = tag; this.dataset = {}; this.children = []; this.checked = false; this.classList = { add() {} }; }
        appendChild(child) { this.children.push(child); child.parentElement = this; }
        set textContent(value) { this.text = value; }
        get textContent() { return (this.text || '') + this.children.map(child => child.textContent).join(''); }
        set innerHTML(value) { this.html = value; this.children = []; }
        get innerHTML() { return this.html || ''; }
        addEventListener() {}
        setAttribute() {}
    }
    const walk = root => root.children.flatMap(child => [child, ...walk(child)]);
    const list = new Element();
    const ticket = new Element();
    const requests = [];
    const pending = [];
    const context = vm.createContext({
        registerType: type, isSelfRegistrationKiosk: kiosk, autoEnrolleeOffices: [],
        selectedOfficeIds: [], selectedOfficeId: null, isOtherDestination: false,
        destinationOffice: list, destinationOfficeText: null, otherDestinationText: null,
        officeListNote: new Element(), ticketRouteList: ticket, officeFetchSequence: 0,
        OTHERS_OFFICE_VALUE: '__others__',
        getOfficeCheckboxes: () => walk(list).filter(child => child.tag === 'input' && child.value !== '__others__'),
        getSelectAllInput: () => null, hideAndClearOtherDestination() {},
        syncDestinationSelection() {}, bindOfficeOptionKeyboard() {},
        document: { createElement: tag => new Element(tag), createTextNode: text => Object.assign(new Element(), { textContent: text }) },
        fetch(url, options) { requests.push({ url, options }); return new Promise(resolve => pending.push(data => resolve({ ok: true, json: async () => ({ success: true, offices: data }) }))); },
    });
    vm.runInContext(`${helpers}\n${renderers}\nglobalThis.locations = { renderOfficeList, fetchOffices, getSelectedDestinationText, renderTicketRouteList };`, context);
    return { api: context.locations, list, ticket, requests, pending, inputs: () => walk(list).filter(child => child.tag === 'input' && child.value !== '__others__') };
}

test('registration uses directory floor and room, with no hardcoded room when it is blank', () => {
    for (const kiosk of [true, false]) {
        const h = harness({ kiosk });
        h.api.renderOfficeList([{ office_id: 1, office_name: 'Health Services Office', floor: '6th Floor', room_number: null }]);
        assert.match(h.list.textContent, /Health Services Office/);
        assert.match(h.list.textContent, /6th Floor/);
        assert.doesNotMatch(h.list.textContent, /Room|651/);
        h.api.renderOfficeList([{ office_id: 1, office_name: 'Health Services Office', floor: 'Ground Floor', room_number: 'G-03' }]);
        assert.match(h.list.textContent, /Ground Floor · Room G-03/);
        assert.doesNotMatch(h.list.textContent, /6th Floor|651/);
    }
});

test('selected destinations and QR tickets preserve the exact office name and saved location', () => {
    for (const kiosk of [true, false]) {
        const h = harness({ kiosk });
        h.api.renderOfficeList([{ office_id: 1, office_name: 'Admissions Office', floor: '2nd Floor', room_number: '201' }]);
        h.inputs()[0].checked = true;
        assert.equal(h.api.getSelectedDestinationText(), 'Admissions Office — 2nd Floor · Room 201');
        h.api.renderTicketRouteList();
        assert.match(h.ticket.innerHTML, /<strong>Admissions Office<\/strong>/);
        assert.match(h.ticket.innerHTML, /2nd Floor · Room 201/);
    }
});

test('enrollee routes retain directory location details and existing route order', () => {
    const h = harness({ type: 'enrollee' });
    h.api.renderOfficeList([
        { office_id: 1, office_name: 'Admissions Office', floor: '2nd Floor', room_number: '201' },
        { office_id: 2, office_name: 'Health Services Office', floor: '6th Floor', room_number: null },
    ]);
    assert.equal(h.api.getSelectedDestinationText(), 'Admissions Office — 2nd Floor · Room 201, Health Services Office — 6th Floor');
    h.api.renderTicketRouteList();
    assert.doesNotMatch(h.ticket.innerHTML, /651/);
});

test('fresh office data wins over an earlier response and requests bypass browser cache', async () => {
    const h = harness();
    const earlier = h.api.fetchOffices();
    const afterScan = h.api.fetchOffices();
    h.pending[1]([{ office_id: 1, office_name: 'Admissions Office', floor: '2nd Floor', room_number: '201' }]);
    await afterScan;
    h.pending[0]([{ office_id: 1, office_name: 'Old Office Name', floor: '5th Floor', room_number: '999' }]);
    await earlier;
    assert.match(h.list.textContent, /Admissions Office/);
    assert.match(h.list.textContent, /Room 201/);
    assert.doesNotMatch(h.list.textContent, /Old Office Name|999/);
    assert.equal(h.requests[1].options.cache, 'no-store');
});
