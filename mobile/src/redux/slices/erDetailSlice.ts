/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IEquipmentRecord } from "../../@type/IGetEquipmentRecordResponse";

interface ErDetailState {
  data: IEquipmentRecord | null;
  refreshCounter: number;
}

const initialState: ErDetailState = {
  data: null,
  refreshCounter: 0,
};

export const erDetailSlice = createSlice({
  name: "erDetail",
  initialState,
  reducers: {
    setErDetail: (state, action: PayloadAction<IEquipmentRecord | null>) => {
      state.data = action.payload;
    },
    refreshErData: (state) => {
      state.refreshCounter += 1;
    },
  },
});

export const { setErDetail, refreshErData } = erDetailSlice.actions;

export default erDetailSlice.reducer;
