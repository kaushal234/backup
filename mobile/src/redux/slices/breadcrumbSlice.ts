/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IBreadcrumb } from "../../@type/IBreadcrumb";

interface BreadcrumbState {
  data: Array<IBreadcrumb>;
}

const initialState: BreadcrumbState = {
  data: [],
};

export const breadcrumbSlice = createSlice({
  name: "breadcrumb",
  initialState,
  reducers: {
    setBreadcrumbs: (state, action: PayloadAction<Array<IBreadcrumb>>) => {
      state.data = action.payload;
    },
    resetBreadcrumbs: (state) => {
      state.data = [];
    },
    setLastBreadcrumbAppendString: (state, action: PayloadAction<string>) => {
      state.data[state.data.length - 1].appendText = action.payload;
    },
  },
});

export const {
  setBreadcrumbs,
  resetBreadcrumbs,
  setLastBreadcrumbAppendString,
} = breadcrumbSlice.actions;

export default breadcrumbSlice.reducer;
