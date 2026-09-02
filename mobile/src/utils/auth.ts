import * as jose from "jose";
import { StatusCodes } from "http-status-codes";
import { IDB_DATABASE, LOCAL_STORAGE } from "../constants/constants";
import { idbCreateItem, idbDeleteAllItems, idbGetItem } from "../idb";
import { logoutUserRedux, loginUserRedux } from "../redux/slices/authSlice";
import { store } from "../redux/store";
import { ILoginUserParams } from "../@type/ILoginUserParams";
import { getDetailedUserInfo } from "../api/getDetailedUserInfo";
import { getFeatures } from "../api/getFeatures";
import { setIsServiceOrganisationPopulated } from "../redux/slices/tocFilterSlice";
import { setToastMessage } from "../redux/slices/toastSlice";
import { toastError } from "./api";
import { ROUTES } from "../constants/routes";
import { ISetupUserParams } from "../@type/ISetupUserParams";

export const verifyToken = async (token: string) => {
  return !!token;
};

export const decodeToken = (token: string) => {
  return jose.decodeJwt(token);
};

export const checkTokenExpiry = (token: string) => {
  const decodedToken = decodeToken(token);
  const currentTime = Math.floor(Date.now() / 1000);
  return (decodedToken.exp ?? 0) < currentTime;
};

export const getToken = async () => {
  const state = store.getState();
  let { token } = state.auth;
  if (!token) {
    const savedUser = await idbGetItem(
      IDB_DATABASE.stores.user_info,
      IDB_DATABASE.stores.user_info
    );
    if (savedUser) {
      token = savedUser.data.token;
      store.dispatch(
        loginUserRedux({
          token: savedUser.data.token,
          userInfo: savedUser.data.userInfo,
          features: savedUser.data.features,
        })
      );
    }
  }
  return token;
};

export const logoutUser = async () => {
  localStorage.clear();
  await idbDeleteAllItems(IDB_DATABASE.stores.user_info);
  store.dispatch(logoutUserRedux());
};

const loginUser = async (data: ILoginUserParams) => {
  await idbCreateItem(IDB_DATABASE.stores.user_info, {
    id: IDB_DATABASE.stores.user_info,
    data,
  });
  localStorage.setItem(LOCAL_STORAGE.userInfo, JSON.stringify(data));
  store.dispatch(loginUserRedux(data));
};

export const setupUser = async (data: ISetupUserParams) => {
  const { token, dispatch, navigate } = data;
  const decodedToken = decodeToken(token);
  const peopleUri = decodedToken["@id"] as string;
  const userInfoResponse = await getDetailedUserInfo({ token });
  const featuresResponse = await getFeatures({ token, peopleUri });
  if (userInfoResponse.status === StatusCodes.OK && userInfoResponse.data) {
    const featureStrings = (featuresResponse.data?.["hydra:member"] ?? []).map(
      (feature) => feature.name
    );
    await loginUser({
      token,
      userInfo: userInfoResponse.data,
      features: featureStrings,
    });
    dispatch(setIsServiceOrganisationPopulated(false));
    dispatch(setToastMessage("login.toast.success"));
    navigate(ROUTES.home);
  } else {
    toastError(dispatch, userInfoResponse);
  }
};
