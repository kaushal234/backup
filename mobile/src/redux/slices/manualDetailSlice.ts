/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IManual } from "../../@type/IGetManualResponse";

interface ManualDetailState {
  data: IManual | null;
}

const initialState: ManualDetailState = {
  data: null,
};

export const manualDetailSlice = createSlice({
  name: "manualDetailSlice",
  initialState,
  reducers: {
    setManualDetail: (state, action: PayloadAction<IManual | null>) => {
      state.data = action.payload;
    },
  },
});

export const { setManualDetail } = manualDetailSlice.actions;

export default manualDetailSlice.reducer;
