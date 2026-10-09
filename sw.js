const CACHE_NAME = 'campusgo-v3';

const APP_SHELL = [
    '/campusgo/',
    '/campusgo/index.html',
    '/campusgo/offline.html',
    '/campusgo/css/style.css',
    '/campusgo/js/app.js',
    '/campusgo/js/db.js',
    '/campusgo/js/board.js',
    '/campusgo/img/icon-192.png',
    '/campusgo/img/icon-512.png',
    '/campusgo/manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => cache.addAll(APP_SHELL))
    );

    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((names) =>
                Promise.all(
                    names
                        .filter((name) => name !== CACHE_NAME)
                        .map((name) => caches.delete(name))
                )
            )
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    if (url.pathname.includes('/api/')) {
        event.respondWith(networkFirst(event.request));
    } else {
        event.respondWith(cacheFirst(event.request));
    }
});

async function cacheFirst(request) {
    const cached = await caches.match(request);

    if (cached) {
        return cached;
    }

    try {
        return await fetch(request);
    } catch {
        if (request.mode === 'navigate') {
            return caches.match('/campusgo/offline.html');
        }

        throw new Error('Network unavailable');
        }
}

async function networkFirst(request) {
    const cache = await caches.open(CACHE_NAME);

    try {
        const fresh = await fetch(request);

        cache.put(request, fresh.clone());

        return fresh;
    } catch {
        return cache.match(request);
    }
}
self.addEventListener(
    'notificationclick',
    (event) => {
        event.notification.close();

        event.waitUntil(
            clients
                .matchAll({
                    type: 'window'
                })
                .then((list) => {

                    const url =
                        '/campusgo/todo.php';

                    const open =
                        list.find((client) =>
                            client.url.includes(
                                '/campusgo/todo.php'
                            )
                        );

                    return open
                        ? open.focus()
                        : clients.openWindow(url);
                })
        );
    }
);