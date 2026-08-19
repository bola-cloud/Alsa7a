// Service worker for OneSignal web push in the admin panel.
//
// It has to sit at the site root: a service worker can only control pages at
// or below its own path, and the panel lives under /ar/admin and /en/admin.
importScripts('https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.sw.js');
