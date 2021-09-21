const version = 0.05;

const staticCacheName = version + 'staticfiles';

const themePath = 'wp-content/themes/planet4-master-theme/';

// Cache needed files for offline page
addEventListener('install', event => {
  event.waitUntil(
    caches.open(staticCacheName)
      .then(staticCache => {
        return staticCache.addAll([
          `${themePath}/offline-page/offline.jpeg`,
          `${themePath}/offline-page/offline.css`,
          `${themePath}/offline-page/gp-logo.svg`,
          `${themePath}/offline-page/offline.html`,
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
        return caches.match('./offline-page/offline.html');
      })
  )
})
