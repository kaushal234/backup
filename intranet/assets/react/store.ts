import { configureStore } from "@reduxjs/toolkit";
import axios from "axios";
import createSagaMiddleware from "redux-saga";
import { routerMiddleware } from "react-router-redux";
import { createBrowserHistory } from "history";
import { FormAction } from "redux-form";
import { reducers } from "./reducers";
import saga from "./sagas";

const jwt =
  window.user && Object.hasOwn(window.user, "token") ? window.user.token : null;
const locale = $("html").attr("lang");

const httpClientConfig = {
  baseURL: process.env.WEBPACK_API_URI,
  headers: {
    Accept: "application/ld+json",
    "Content-type": "application/ld+json",
    Authorization: `Bearer ${jwt}`,
    "accept-language": locale,
  },
};
const client = axios.create(httpClientConfig);

export { httpClientConfig, client };

const sagaMiddleware = createSagaMiddleware();
const history = createBrowserHistory();

export { history };
const middlewares: any = [sagaMiddleware, routerMiddleware(history)];

if (process.env.NODE_ENV === "development") {
  client.baseURL = httpClientConfig.baseURL;
}

const store = configureStore({
  reducer: reducers,
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: false,
      immutableCheck: false,
    }).concat(middlewares),
});

sagaMiddleware.run(saga);

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch &
  ((action: FormAction) => void);

export default store;
