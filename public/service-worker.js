/**
 * SIMDA — Progressive Web App (PWA) Service Worker
 *
 * Security & Financial Integrity Rules (Section 13):
 * - Caches ONLY static shell assets (CSS, JS, brand logos, icons, and offline fallback page).
 * - NEVER caches authenticated HTML pages, JSON financial APIs, PDF statements, or POST transactions.
 * - Never allows offline financial transactions, approvals, or repayments to be treated as completed.
 */
const CACHE_NAME = 'simda-static-shell-v1';
const STATIC_ASSETS = [
    './?route=offline',
    './assets/css/simda.css',
    './assets/js/simda.js',
    './assets/images/simda-logo.png',
    './assets/images/simda-icon.png',
    './assets/icons/icon-192.png',
    './assets/icons/icon-512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS)).then(() => self.skipWaiting())
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
    // Never intercept POST/PUT/DELETE financial mutations
    if (req.method !== 'GET') {
        return;
    }

    const url = new URL(req.url);

    // Static asset cache-first strategy
    if (
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.ico')
    ) {
        event.respondWith(
            caches.match(req).then((cached) => cached || fetch(req))
        );
        return;
    }

    // Network-only for authenticated/dynamic routes with offline fallback page when disconnected
    event.respondWith(
        fetch(req).catch(() => caches.match('./?route=offline'))
    );
});

