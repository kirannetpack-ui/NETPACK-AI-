const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

test('every install and shortcut icon is a PNG matching its declared size', () => {
    const manifest = JSON.parse(fs.readFileSync('public/manifest.json', 'utf8'));
    const icons = [...manifest.icons, ...manifest.shortcuts.flatMap(s => s.icons)];
    for (const icon of icons) {
        const bytes = fs.readFileSync('public' + icon.src);
        assert.equal(bytes.subarray(0, 8).toString('hex'), '89504e470d0a1a0a');
        assert.equal(`${bytes.readUInt32BE(16)}x${bytes.readUInt32BE(20)}`, icon.sizes);
    }
    assert.ok(manifest.icons.some(i => i.sizes === '192x192'));
    assert.ok(manifest.icons.some(i => i.sizes === '512x512'));
});

test('PWA starts on an already loaded page and retains an early install prompt', async () => {
    const events = {}, registrations = [], dispatched = [];
    const context = {
        console,
        CustomEvent: class { constructor(type) { this.type = type; } },
        document: {readyState: 'complete'},
        navigator: {serviceWorker: {register: (...args) => { registrations.push(args); return Promise.resolve(); }}},
        window: {isSecureContext: true,
            addEventListener: (name, fn) => {events[name] = fn;},
            dispatchEvent: event => dispatched.push(event.type)},
    };
    vm.runInNewContext(fs.readFileSync('resources/js/pwa.js', 'utf8'), context);
    assert.equal(registrations[0][0], '/service-worker.js');
    assert.equal(registrations[0][1].updateViaCache, 'none');
    let prevented = false, prompted = false;
    events.beforeinstallprompt({preventDefault: () => {prevented = true;},
        prompt: async () => {prompted = true;}, userChoice: Promise.resolve({outcome: 'accepted'})});
    assert.equal(context.window.netpackInstallAvailable, true);
    assert.equal(await context.window.installNetpackApp(), true);
    assert.equal(prevented && prompted, true);
    assert.equal(context.window.netpackInstallAvailable, false);
    assert.equal(await context.window.installNetpackApp(), false);
    assert.ok(dispatched.includes('pwa-installable'));
});
