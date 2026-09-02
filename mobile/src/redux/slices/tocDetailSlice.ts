/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { ITechnicianOnCall } from "../../@type/IGetTechnicalOnCallsResponse";

interface TocDetailState {
  data: ITechnicianOnCall | null;
  refreshCounter: number;
  showUpdateTocStatusPopup: boolean;
}

const initialState: TocDetailState = {
  data: null,
  refreshCounter: 0,
  showUpdateTocStatusPopup: false,
};

export const tocDetailSlice = createSlice({
  name: "tocDetail",
  initialState,
  reducers: {
    setTocDetail: (state, action: PayloadAction<ITechnicianOnCall | null>) => {
      state.data = action.payload;
    },
    refreshTocData: (state) => {
      state.refreshCounter += 1;
    },
    setShowUpdateTocStatusPopup: (state, action: PayloadAction<boolean>) => {
      state.showUpdateTocStatusPopup = action.payload;
    },
  },
});

export const { setTocDetail, refreshTocData, setShowUpdateTocStatusPopup } =
  tocDetailSlice.actions;

export default tocDetailSlice.reducer;
