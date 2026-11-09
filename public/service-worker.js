/**
 * SIMDA — Progressive Web App (PWA) Service Worker
 *
 * Security & Financial Integrity Rules (Section 13):
 * - Uses Network-First for static assets so CSS/JS updates always load immediately, falling back to cache only when offline.
 * - NEVER caches authenticated HTML pages, JSON financial APIs, PDF statements, or POST transactions.
 * - Never allows offline financial transactions, approvals, or repayments to be treated as completed.
 */
const CACHE_NAME = 'simda-static-shell-v5';
const STATIC_ASSETS = [
    './?route=offline',
    './assets/css/simda.css',
    './assets/js/simda.js',
    './assets/images/simda-logo.png',
    './assets/images/simda-icon.png',
    './assets/icons/icon-192.png',
    './assets/icons/icon-512.png',
    './assets/icons/icon-maskable-512.png'
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') {
        return;
    }

    const url = new URL(req.url);

    // Network-first strategy for static assets (always fresh online, cached fallback offline)
    if (
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.jpg') ||
        url.pathname.endsWith('.ico')
    ) {
        event.respondWith(
            fetch(req)
                .then((networkResp) => {
                    const clone = networkResp.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(req, clone));
                    return networkResp;
                })
                .catch(() => caches.match(req))
        );
        return;
    }

    // Network-only for authenticated/dynamic routes with offline fallback page when disconnected
    event.respondWith(
        fetch(req).catch(() => caches.match('./?route=offline'))
    );
});
