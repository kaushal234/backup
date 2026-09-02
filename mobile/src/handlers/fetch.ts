import { IDB_DATABASE, CACHE_DYNAMIC_NAME } from "../constants/constants";
import { idbGetItem, idbDeleteItem } from "../idb";

export const handleFetch = async (event: FetchEvent) => {
  if (event.request.method !== "GET") {
    return fetch(event.request);
  }

  const urlInfo = await idbGetItem(
    IDB_DATABASE.stores.url_info,
    event.request.url
  );
  const fetchFirst = urlInfo?.fetchFirst;
  const fetchAlways = urlInfo?.fetchAlways;
  await idbDeleteItem(IDB_DATABASE.stores.url_info, event.request.url);

  if (fetchAlways) {
    return fetch(event.request);
  }

  if (fetchFirst) {
    return fetch(event.request)
      .then((res) => {
        if (res.status !== 200) {
          return res;
        }
        return caches.open(CACHE_DYNAMIC_NAME).then((cache) => {
          cache.put(event.request.url, res.clone());
          return res;
        });
      })
      .catch(() => {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        return caches.match(event.request.url) as any;
      });
  }
  return caches.match(event.request.url).then((response) => {
    if (response) {
      return response;
    }
    return fetch(event.request).then((res) => {
      if (res.status !== 200) {
        return res;
      }
      return caches.open(CACHE_DYNAMIC_NAME).then((cache) => {
        cache.put(event.request.url, res.clone());
        return res;
      });
    });
  });
};
