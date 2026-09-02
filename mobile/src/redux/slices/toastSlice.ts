/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

interface ToastState {
  toastMessage: string;
  params: { [key: string]: string | number } | null;
  counter: number;
}

interface IToastWithParams {
  toastMessage: string;
  params: { [key: string]: string | number } | null;
}

const initialState: ToastState = {
  toastMessage: "",
  params: null,
  counter: 0,
};

export const toastSlice = createSlice({
  name: "toast",
  initialState,
  reducers: {
    setToastMessage: (state, action: PayloadAction<string>) => {
      state.toastMessage = action.payload;
      state.params = null;
      state.counter += 1;
    },
    setToastMessageWithParams: (
      state,
      action: PayloadAction<IToastWithParams>
    ) => {
      state.toastMessage = action.payload.toastMessage;
      state.params = action.payload.params;
      state.counter += 1;
    },
  },
});

export const { setToastMessage, setToastMessageWithParams } =
  toastSlice.actions;

export default toastSlice.reducer;
