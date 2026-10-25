/** Public static caching only. Operational actions require a live connection. */
const CACHE_NAME = 'netpack-public-v6';
const OFFLINE_URL = '/offline.html';
const STATIC_ASSETS = ['/offline.html', '/images/app-icon-180.png', '/images/app-icon-192.png', '/images/app-icon-512.png', '/images/logo.svg', '/images/logo-white.svg'];
self.addEventListener('install', event => {
    event.waitUntil((async () => {
        const cache = await caches.open(CACHE_NAME);
        // One missing optional icon must not prevent the offline page being cached.
        await Promise.allSettled(STATIC_ASSETS.map(path => cache.add(new Request(path, {cache: 'reload'}))));
        await self.skipWaiting();
    })());
});
self.addEventListener('activate', event => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter(key => key.startsWith('netpack-') && key !== CACHE_NAME).map(key => caches.delete(key)));
        await self.clients.claim();
    })());
});
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    if (request.method !== 'GET' || url.origin !== self.location.origin) return;
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(async () => (await caches.match(OFFLINE_URL)) || new Response('You are offline. Reconnect to continue.', {status: 503, headers: {'Content-Type': 'text/plain'}})));
        return;
    }
    const publicAsset = !url.search && (STATIC_ASSETS.includes(url.pathname) || /^\/build\/assets\/[a-zA-Z0-9_.-]+\.(css|js|png|jpg|svg|woff2?)$/.test(url.pathname));
    if (!publicAsset || request.headers.has('Authorization')) return;
    event.respondWith((async () => {
        const cached = await caches.match(request);
        if (cached) return cached;
        const response = await fetch(request);
        if (response.ok && !response.redirected && response.type === 'basic' && !/private|no-store/i.test(response.headers.get('Cache-Control') || '') && !response.headers.get('Content-Type')?.includes('text/html')) {
            const cache = await caches.open(CACHE_NAME);
            await cache.put(request, response.clone());
        }
        return response;
    })());
});

// Push Notifications: Real-Time Shipment Status Intelligence
self.addEventListener('push', (event) => {
    let payload = {
        title: 'COURIER with NETPACK Status Alert',
        body: 'Your shipment status has just been updated.',
        icon: '/images/app-icon-192.png',
        badge: '/images/app-icon-192.png',
        data: { url: '/client/dashboard' },
        vibrate: [100, 50, 100],
        actions: [
            { action: 'track', title: 'Radar Tracking' },
            { action: 'copilot', title: 'Ask Chanda' }
        ]
    };

    if (event.data) {
        try {
            const data = event.data.json();
            payload = { ...payload, ...data };
        } catch (e) {
            payload.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(payload.title, {
            body: payload.body,
            icon: payload.icon || '/images/app-icon-192.png',
            badge: payload.badge || '/images/app-icon-192.png',
            data: payload.data,
            vibrate: payload.vibrate || [100, 50, 100],
            actions: payload.actions || []
        })
    );
});

// Notification Click: Smart Deep-Linking
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    let targetUrl = new URL('/client/dashboard', self.location.origin).href;
    try {
        const requested = new URL(event.notification.data?.url || '/client/dashboard', self.location.origin);
        if (requested.origin === self.location.origin) targetUrl = requested.href;
    } catch (_) { /* Keep the safe default. */ }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
