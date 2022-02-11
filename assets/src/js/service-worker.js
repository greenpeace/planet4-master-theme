const version = 0.05;

const staticCacheName = version + 'staticfiles';

// Cache needed files for offline page
addEventListener('install', event => {
  event.waitUntil(
    caches.open(staticCacheName)
      .then(staticCache => {
        return staticCache.addAll([
          '/offline-page/offline.jpeg',
          '/offline-page/offline.css',
          '/offline-page/gp-logo.svg',
          '/offline-page/offline.html',
        ]);
      })
  );
});

// Delete old caches
addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== staticCacheName) {
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        return clients.claim();
      })
  );
});

// Show offline page if any fetch errors
addEventListener('fetch', event => {
  const { request } = event;
  // This service worker won't touch the admin area and preview pages
  if (request.url.match(/wp-admin/) || request.url.match(/preview=true/)) {
    return;
  }

  event.respondWith(
    caches.match(request)
      .then(responseFromCache => {
        if (responseFromCache) {
          return responseFromCache;
        }
        return fetch(request);
      })
      .catch(error => {
        console.log(error);
        return caches.match('/offline-page/offline.html');
      })
  );
});
