/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

interface NetworkStatusState {
  isOnline: boolean;
}

const initialState: NetworkStatusState = {
  isOnline: navigator.onLine,
};

export const networkStatusSlice = createSlice({
  name: "networkStatus",
  initialState,
  reducers: {
    setIsOnline: (state, action: PayloadAction<boolean>) => {
      state.isOnline = action.payload;
    },
  },
});

export const { setIsOnline } = networkStatusSlice.actions;

export default networkStatusSlice.reducer;
