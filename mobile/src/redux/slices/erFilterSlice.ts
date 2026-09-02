/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { LS_KEYS } from "../../constants/constants";
import { IGetAllEquipmentRecordFilterRawValues } from "../../@type/IGetAllEquipmentRecordFilterRawValues";

interface ErFilterState {
  filters: IGetAllEquipmentRecordFilterRawValues;
}

export const initialState: ErFilterState = {
  filters: {
    serialNumber: null,
    equipmentType: [],
    model: [],
    airport: null,
    buyer: null,
    endUser: null,
    maintainer: null,
  },
};

export const erFilterSlice = createSlice({
  name: "erFilter",
  initialState,
  reducers: {
    setErFilters: (
      state,
      action: PayloadAction<IGetAllEquipmentRecordFilterRawValues>
    ) => {
      state.filters = action.payload;
      localStorage.setItem(LS_KEYS.er_filters, JSON.stringify(action.payload));
    },
  },
});

export const { setErFilters } = erFilterSlice.actions;

export default erFilterSlice.reducer;
