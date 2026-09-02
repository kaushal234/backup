import { IDB_DATABASE, IDB_DATABASE_VERSION } from "../constants/constants";
import { DatabaseStores } from "./@types";
import * as idb from "./idb-promisify";

const dbPromise = idb.open(
  IDB_DATABASE.name,
  IDB_DATABASE_VERSION,
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  (db: any) => {
    Object.keys(IDB_DATABASE.stores).forEach((store) => {
      if (!db.objectStoreNames.contains(store)) {
        db.createObjectStore(store, { keyPath: "id" });
      }
    });
  }
);

export const idbCreateItem = (
  st: DatabaseStores,
  data: object
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
): Promise<any> => {
  return dbPromise.then((db) => {
    const tx = db.transaction(st, "readwrite");
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const store: any = tx.objectStore(st);
    store.put(data);
    return tx.complete;
  });
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export const idbGetAllItems = (st: DatabaseStores): Promise<any> => {
  return dbPromise.then((db) => {
    const tx = db.transaction(st, "readonly");
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const store: any = tx.objectStore(st);
    return store.getAll();
  });
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export const idbGetItem = (st: DatabaseStores, id: string): Promise<any> => {
  return dbPromise
    .then((db) => {
      const tx = db.transaction(st, "readonly");
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const store: any = tx.objectStore(st);
      return store.get(id);
    })
    .catch((e) => {
      console.error(e);
    });
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export const idbDeleteAllItems = (st: DatabaseStores): Promise<any> => {
  return dbPromise.then((db) => {
    const tx = db.transaction(st, "readwrite");
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const store: any = tx.objectStore(st);
    store.clear();
    return tx.complete;
  });
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export const idbDeleteItem = (st: DatabaseStores, id: string): Promise<any> => {
  return dbPromise.then((db) => {
    const tx = db.transaction(st, "readwrite");
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const store: any = tx.objectStore(st);
    store.delete(id);
    return tx.complete;
  });
};

const deleteIndexedDB = async (dbName: string) => {
  return new Promise((resolve, reject) => {
    const request = indexedDB.deleteDatabase(dbName);
    request.onsuccess = () => {
      resolve(null);
    };
    request.onerror = (event) => {
      reject(event);
    };
  });
};

export const idbResetDatabase = async () => {
  const databases = await indexedDB.databases();
  return databases.map((db) => deleteIndexedDB(db.name ?? ""));
};
