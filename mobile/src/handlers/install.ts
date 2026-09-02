import { CACHE_STATIC_NAME } from "../constants/constants";

export const handleInstall = (
  event: ExtendableEvent,
  self: ServiceWorkerGlobalScope
) => {
  event.waitUntil(
    caches
      .open(CACHE_STATIC_NAME)
      .then((cache) => {
        cache.addAll([
          "/",
          "/index.html",
          "/login",
          "/static/js/bundle.js",
          "/manifest.json",
          "/favicon.ico",
          "/logo.svg",
          "/locales/en/translation.json",
          "/locales/fr/translation.json",
          "/locales/zh/translation.json",
        ]);
      })
      .then(() => {
        self.skipWaiting();
      })
  );
};
