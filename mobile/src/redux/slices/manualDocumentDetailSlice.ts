/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IManualDocument } from "../../@type/IGetManualDocumentResponse";

interface ManualDetailState {
  data: IManualDocument | null;
}

const initialState: ManualDetailState = {
  data: null,
};

export const manualDocumentDetailSlice = createSlice({
  name: "manualDocumentDetailSlice",
  initialState,
  reducers: {
    setManualDocumentDetail: (
      state,
      action: PayloadAction<IManualDocument | null>
    ) => {
      state.data = action.payload;
    },
  },
});

export const { setManualDocumentDetail } = manualDocumentDetailSlice.actions;

export default manualDocumentDetailSlice.reducer;
