/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

interface TranslationState {
  showTranslation: boolean;
  showTranslationIcon: boolean;
}

const initialState: TranslationState = {
  showTranslation: false,
  showTranslationIcon: false,
};

export const translationSlice = createSlice({
  name: "translation",
  initialState,
  reducers: {
    setShowTranslation: (state, action: PayloadAction<boolean>) => {
      state.showTranslation = action.payload;
    },
    setShowTranslationIcon: (state, action: PayloadAction<boolean>) => {
      state.showTranslationIcon = action.payload;
    },
  },
});

export const { setShowTranslation, setShowTranslationIcon } =
  translationSlice.actions;

export default translationSlice.reducer;
