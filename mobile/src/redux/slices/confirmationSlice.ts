/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

interface ConfirmationState {
  title: string;
  description: string;
  subDescription?: string;
  isOpen: boolean;
}

const initialState: ConfirmationState = {
  title: "",
  description: "",
  subDescription: undefined,
  isOpen: false,
};

export const confirmationSlice = createSlice({
  name: "confirmation",
  initialState,
  reducers: {
    setConfirmationData: (
      state,
      action: PayloadAction<{
        title: string;
        description: string;
        subDescription?: string;
      }>
    ) => {
      state.title = action.payload.title;
      state.description = action.payload.description;
      state.subDescription = action.payload.subDescription;
      state.isOpen = true;
    },
    resetConfirmationData: (state) => {
      state.title = "";
      state.description = "";
      state.subDescription = undefined;
    },
    hideConfirmationPopUp: (state) => {
      state.isOpen = false;
    },
  },
});

export const {
  setConfirmationData,
  resetConfirmationData,
  hideConfirmationPopUp,
} = confirmationSlice.actions;

export default confirmationSlice.reducer;
