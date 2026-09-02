import { CACHE_DYNAMIC_NAME, CACHE_STATIC_NAME } from "../constants/constants";

export const handleActivate = (
  event: ExtendableEvent,
  self: ServiceWorkerGlobalScope
) => {
  event.waitUntil(
    caches
      .keys()
      .then((keyList) => {
        return Promise.all(
          keyList.map((key) => {
            if (
              (key.startsWith("dynamic") || key.startsWith("static")) &&
              key !== CACHE_STATIC_NAME &&
              key !== CACHE_DYNAMIC_NAME
            ) {
              return caches.delete(key);
            }
            return undefined;
          })
        );
      })
      .then(() => {
        self.clients.claim();
      })
  );
};
