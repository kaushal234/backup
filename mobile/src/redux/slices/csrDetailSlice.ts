/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  ICustomerServiceRecord,
  IGetCustomerServiceRecordResponse,
} from "../../@type/IGetCustomerServiceRecordResponse";

interface TocDetailState {
  data: IGetCustomerServiceRecordResponse | null;
  refreshCounter: number;
}

const initialState: TocDetailState = {
  data: null,
  refreshCounter: 0,
};

export const csrDetailSlice = createSlice({
  name: "csrDetail",
  initialState,
  reducers: {
    setCsrDetail: (
      state,
      action: PayloadAction<ICustomerServiceRecord | null>
    ) => {
      state.data = action.payload;
    },
    refreshCsrData: (state) => {
      state.refreshCounter += 1;
    },
  },
});

export const { setCsrDetail, refreshCsrData } = csrDetailSlice.actions;

export default csrDetailSlice.reducer;
