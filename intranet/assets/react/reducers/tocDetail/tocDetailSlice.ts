/* eslint-disable no-param-reassign */
import { createSlice } from "@reduxjs/toolkit";
import { RootState } from "../../store";
import { ITechnicianOnCall } from "../../types/IGetTechnicalOnCallsResponse";
import { fetchTechnicianOnCallThunk } from "../../thunk/fetchTechnicianOnCall";

type TocDetailState = {
  loading: boolean;
  data: null | ITechnicianOnCall;
};

const initialState: TocDetailState = {
  loading: false,
  data: null,
};

export const tocDetailSlice = createSlice({
  name: "tocDetailSlice",
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchTechnicianOnCallThunk.pending, (state) => {
        state.loading = true;
      })
      .addCase(fetchTechnicianOnCallThunk.fulfilled, (state, action) => {
        state.loading = false;
        state.data = action.payload;
      })
      .addCase(fetchTechnicianOnCallThunk.rejected, (state) => {
        state.loading = false;
        state.data = null;
      });
  },
});

export const selectData = (state: RootState) => state.data.data;

export default tocDetailSlice.reducer;
