import axios from "axios";
import { store } from "../redux/store";
import { idbCreateItem } from "../idb";
import { IDB_DATABASE } from "../constants/constants";
import { checkTokenExpiry, getToken, logoutUser } from "../utils/auth";
import { setToastMessage } from "../redux/slices/toastSlice";
import { navigateTo } from "../utils/navigator";

const api = axios.create({
  baseURL: process.env.REACT_APP_API_BASE_URL,
});

api.interceptors.request.use(
  async (config) => {
    if (config.fetchFirst) {
      await idbCreateItem(IDB_DATABASE.stores.url_info, {
        id: config.url,
        fetchFirst: true,
      });
    }

    if (config.fetchAlways) {
      await idbCreateItem(IDB_DATABASE.stores.url_info, {
        id: config.url,
        fetchAlways: true,
      });
    }

    const authRequired = config.authRequired ?? true;

    if (authRequired) {
      const token = await getToken();
      if (token && !checkTokenExpiry(token)) {
        // eslint-disable-next-line no-param-reassign
        config.headers.Authorization = `Bearer ${token}`;
      } else {
        logoutUser();
        store.dispatch(setToastMessage("Token expired. Please login again"));
        setTimeout(() => {
          navigateTo("/login");
        }, 100);
      }
    }

    if (config.isBlob) {
      // eslint-disable-next-line no-param-reassign
      config.responseType = "blob";
    }

    if (config.contentHeaderRequired) {
      // eslint-disable-next-line no-param-reassign
      config.headers.Accept = "application/ld+json";
      // eslint-disable-next-line no-param-reassign
      config.headers["Content-Type"] = "application/ld+json";
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

api.interceptors.response.use(
  async (response) => {
    return response;
  },
  (error) => {
    const authRequired = error.config.authRequired ?? true;
    if (authRequired && error?.response?.status === 401) {
      logoutUser();
      store.dispatch(setToastMessage("Token expired. Please login again"));
      setTimeout(() => {
        navigateTo("/login");
      }, 100);
    }
    return Promise.reject(error);
  }
);

export default api;
