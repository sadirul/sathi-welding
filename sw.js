/**
 * Minimal service worker — exists so Chrome/Android treats the app as
 * installable (per manifest.json). Network requests just pass straight
 * through; no offline caching is attempted.
 */

self.addEventListener('install', (event) => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
  event.respondWith(fetch(event.request));
});
