/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

type LoaderState = {
  isLoading: boolean;
};

const initialState: LoaderState = {
  isLoading: false,
};

export const loaderSlice = createSlice({
  name: "loaderSlice",
  initialState,
  reducers: {
    showGlobalLoader: (state, action: PayloadAction<boolean>) => {
      state.isLoading = action.payload;
    },
  },
});

export const { showGlobalLoader } = loaderSlice.actions;

export default loaderSlice.reducer;
