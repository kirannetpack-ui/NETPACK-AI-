const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function worker(fetch = async () => { throw new Error('offline'); }) {
    const handlers = {}, removed = [], stored = [];
    const cache = {put: async (request) => stored.push(request.url), add: async () => {}};
    const context = {
        URL, Request, Response, console, fetch,
        self: {location: {origin: 'https://netpack.test'}, addEventListener: (name, fn) => handlers[name] = fn,
            clients: {claim: async () => {}}, skipWaiting: async () => {}},
        caches: {open: async () => cache, match: async () => new Response('offline page'),
            keys: async () => ['netpack-ai-v2.2.0', 'unrelated-cache', 'netpack-public-v3', 'netpack-public-v4'],
            delete: async key => removed.push(key)},
    };
    vm.runInNewContext(fs.readFileSync('public/service-worker.js', 'utf8'), context);
    return {handlers, removed, stored, context};
}

test('private data, uploads, APIs and mutations never enter the service-worker cache', () => {
    const {handlers} = worker();
    for (const path of ['/seller/wallet', '/rider-documents/1/selfie_photo_path', '/storage/kyc/private.jpg', '/api/user', '/tracking/123', '/ai/chat']) {
        let intercepted = false;
        handlers.fetch({request: new Request('https://netpack.test'+path), respondWith: () => intercepted = true});
        assert.equal(intercepted, false, path);
    }
    let intercepted = false;
    handlers.fetch({request: new Request('https://netpack.test/build/assets/app.js', {method: 'POST'}), respondWith: () => intercepted = true});
    assert.equal(intercepted, false);
});

test('static requests with authorization, query strings or another origin bypass cache', () => {
    const {handlers} = worker();
    for (const request of [new Request('https://cdn.test/build/assets/app.js'),
        new Request('https://netpack.test/build/assets/app.js?private=1'),
        new Request('https://netpack.test/build/assets/app.js', {headers: {Authorization: 'Bearer private'}})]) {
        let intercepted = false;
        handlers.fetch({request, respondWith: () => intercepted = true});
        assert.equal(intercepted, false);
    }
});

test('offline navigation returns a public offline page', async () => {
    const {handlers} = worker();
    let response;
    handlers.fetch({request: {url: 'https://netpack.test/seller/wallet', method: 'GET', mode: 'navigate'}, respondWith: p => response = p});
    assert.equal(await (await response).text(), 'offline page');
});

test('activation purges old NETPACK caches without deleting another app cache', async () => {
    const {handlers, removed} = worker();
    let result;
    handlers.activate({waitUntil: p => result = p});
    await result;
    assert.deepEqual(removed, ['netpack-ai-v2.2.0', 'netpack-public-v3']);
});

test('notification URLs cannot navigate to an external origin', async () => {
    const {handlers, context} = worker();
    let opened, result;
    context.clients = {matchAll: async () => [], openWindow: async url => opened = url};
    handlers.notificationclick({notification: {data: {url: 'https://attacker.test'}, close() {}}, waitUntil: p => result = p});
    await result;
    assert.equal(opened, 'https://netpack.test/client/dashboard');
});
