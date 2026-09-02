/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IPeople } from "../../@type/IGetDetailedUserInfoResponse";
import { ILoginUserParams } from "../../@type/ILoginUserParams";

interface AuthState {
  token: string | null;
  userInfo: IPeople | null;
  features: Array<string>;
}

const initialState: AuthState = {
  token: null,
  userInfo: null,
  features: [],
};

export const authSlice = createSlice({
  name: "auth",
  initialState,
  reducers: {
    loginUserRedux: (state, action: PayloadAction<ILoginUserParams>) => {
      state.token = action.payload.token;
      state.userInfo = action.payload.userInfo;
      state.features = action.payload.features;
    },
    logoutUserRedux: (state) => {
      state.token = null;
      state.userInfo = null;
      state.features = [];
    },
  },
});

export const { loginUserRedux, logoutUserRedux } = authSlice.actions;

export default authSlice.reducer;
