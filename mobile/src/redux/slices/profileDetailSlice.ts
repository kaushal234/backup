/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IPeople } from "../../@type/IGetPeopleResponse";

interface ProfileDetailState {
  data: IPeople | null;
}

const initialState: ProfileDetailState = {
  data: null,
};

export const profileDetailSlice = createSlice({
  name: "profileDetail",
  initialState,
  reducers: {
    setProfileDetail: (state, action: PayloadAction<IPeople | null>) => {
      state.data = action.payload;
    },
  },
});

export const { setProfileDetail } = profileDetailSlice.actions;

export default profileDetailSlice.reducer;
