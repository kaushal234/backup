/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";

interface FactoryTimeState {
  factoryTime: string | null;
  nmcTime: string | null;
}

const initialState: FactoryTimeState = {
  factoryTime: null,
  nmcTime: null,
};

export const timeSlice = createSlice({
  name: "time",
  initialState,
  reducers: {
    setFactoryTime: (state, action: PayloadAction<string | null>) => {
      state.factoryTime = action.payload;
    },
    setNmcTime: (state, action: PayloadAction<string | null>) => {
      state.nmcTime = action.payload;
    },
  },
});

export const { setFactoryTime, setNmcTime } = timeSlice.actions;

export default timeSlice.reducer;
