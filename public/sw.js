const CACHE_NAME = 'inventory-erp-v1';
const PRECACHE_ASSETS = [
  '/offline.html',
  '/manifest.json',
  '/assets/css/bootstrap.min.css',
  '/assets/css/icons.min.css',
  '/assets/css/app.min.css',
  '/assets/css/preloader.min.css',
  '/assets/libs/jquery/jquery.min.js',
  '/assets/libs/bootstrap/js/bootstrap.bundle.min.js',
  '/assets/libs/metismenu/metisMenu.min.js',
  '/assets/libs/simplebar/simplebar.min.js',
  '/assets/libs/node-waves/waves.min.js',
  '/assets/libs/feather-icons/feather.min.js',
  '/assets/js/app.js',
  '/assets/images/icons/icon-192x192.png',
  '/assets/images/icons/icon-512x512.png',
  '/favicon.ico'
];

// Service Worker Installation
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(PRECACHE_ASSETS).catch((err) => {
        console.warn('PWA Precache warning:', err);
      });
    }).then(() => self.skipWaiting())
  );
});

// Service Worker Activation & Cache Cleanup
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Request Interception & Caching Strategy
self.addEventListener('fetch', (event) => {
  const request = event.request;

  // Ignore non-GET requests (POST, PUT, DELETE for ERP transactions & Livewire updates)
  if (request.method !== 'GET') {
    return;
  }

  // Handle HTML navigations with Network-First strategy
  const isHtmlNavigation = request.mode === 'navigate' || 
    (request.headers.get('accept') && request.headers.get('accept').includes('text/html'));

  if (isHtmlNavigation) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          if (response && response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          return caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }
            return caches.match('/offline.html');
          });
        })
    );
    return;
  }

  // Handle static assets with Cache-First / Stale-While-Revalidate
  const url = new URL(request.url);
  const isStaticAsset = url.pathname.startsWith('/assets/') || 
                        url.pathname.endsWith('.css') || 
                        url.pathname.endsWith('.js') || 
                        url.pathname.endsWith('.woff2') || 
                        url.pathname.endsWith('.png') || 
                        url.pathname.endsWith('.jpg') || 
                        url.pathname.endsWith('.ico');

  if (isStaticAsset) {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        const fetchPromise = fetch(request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
          }
          return networkResponse;
        }).catch(() => cachedResponse);

        return cachedResponse || fetchPromise;
      })
    );
    return;
  }

  // Default fallback for remaining GET requests: Network first with Cache fallback
  event.respondWith(
    fetch(request).catch(() => caches.match(request))
  );
});
