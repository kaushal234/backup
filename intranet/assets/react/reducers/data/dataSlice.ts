/* eslint-disable no-param-reassign */
import { createSlice } from "@reduxjs/toolkit";
import { RootState } from "../../store";
import { updateData } from "../../thunk/updateData";

type DataState = {
  loading: boolean;
  data: number;
  status?: number;
};

const initialState: DataState = {
  loading: false,
  data: -1,
};

export const dataSlice = createSlice({
  name: "dataSlice",
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(updateData.pending, (state) => {
        state.loading = false;
      })
      .addCase(updateData.fulfilled, (state, action) => {
        state.status = 200;
        state.data = action.payload.data;
      })
      .addCase(updateData.rejected, (state) => {
        state.status = 400;
      });
  },
});

export const selectData = (state: RootState) => state.data.data;

export default dataSlice.reducer;
