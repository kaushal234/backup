import { configureStore, ThunkAction, Action } from "@reduxjs/toolkit";
import authReducer from "./slices/authSlice";
import toastReducer from "./slices/toastSlice";
import networkStatusReducer from "./slices/networkStatus";
import loaderReducer from "./slices/loaderSlice";
import appBarReducer from "./slices/appBarSlice";
import breadcrumbReducer from "./slices/breadcrumbSlice";
import tocFilterReducer from "./slices/tocFilterSlice";
import dropdownOptionReducer from "./slices/dropdownOptionSlice";
import tocDetailReducer from "./slices/tocDetailSlice";
import confirmationReducer from "./slices/confirmationSlice";
import erDetailReducer from "./slices/erDetailSlice";
import erFilterReducer from "./slices/erFilterSlice";
import csrFilterReducer from "./slices/csrFilterSlice";
import csrDetailReducer from "./slices/csrDetailSlice";
import commentDetailReducer from "./slices/commentDetailSlice";
import manualDetailReducer from "./slices/manualDetailSlice";
import manualDocumentDetailReducer from "./slices/manualDocumentDetailSlice";
import profileDetailReducer from "./slices/profileDetailSlice";
import timeReducer from "./slices/timeSlice";
import translationReducer from "./slices/translationSlice";

export const store = configureStore({
  reducer: {
    auth: authReducer,
    toast: toastReducer,
    networkStatus: networkStatusReducer,
    loader: loaderReducer,
    appBar: appBarReducer,
    breadcrumb: breadcrumbReducer,
    tocFilter: tocFilterReducer,
    dropdownOption: dropdownOptionReducer,
    tocDetail: tocDetailReducer,
    confirmation: confirmationReducer,
    erDetail: erDetailReducer,
    erFilter: erFilterReducer,
    csrFilter: csrFilterReducer,
    csrDetail: csrDetailReducer,
    commentDetail: commentDetailReducer,
    manualDetail: manualDetailReducer,
    manualDocumentDetail: manualDocumentDetailReducer,
    profileDetail: profileDetailReducer,
    time: timeReducer,
    translation: translationReducer,
  },
});

export type AppDispatch = typeof store.dispatch;
export type RootState = ReturnType<typeof store.getState>;
export type AppThunk<ReturnType = void> = ThunkAction<
  ReturnType,
  RootState,
  unknown,
  Action<string>
>;
