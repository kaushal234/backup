/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { ISortModal } from "../../types/ISortModal";
import { DEFAULT_PAGINATION } from "../../constants/constants";
import { IPagination } from "../../types/IPagination";
import { IGenericFilterFormSubmissionData } from "../../types/IGenericFilterFormSubmissionData";
import { IDataTableSavedSetting } from "../../types/IDataTableSavedSetting";

type DataTableState = {
  isLoading: boolean;
  sortModel: ISortModal | null;
  pagination: IPagination;
  filters: IGenericFilterFormSubmissionData;
  settings: IDataTableSavedSetting | null;
};

const initialState: DataTableState = {
  isLoading: false,
  sortModel: null,
  pagination: DEFAULT_PAGINATION,
  filters: {},
  settings: null,
};

export const dataTableSlice = createSlice({
  name: "dataTableSlice",
  initialState,
  reducers: {
    setIsLoading: (state, action: PayloadAction<boolean>) => {
      state.isLoading = action.payload;
    },
    setSortModal: (state, action: PayloadAction<ISortModal | null>) => {
      state.sortModel = action.payload;
    },
    setPagination: (state, action: PayloadAction<IPagination>) => {
      state.pagination = action.payload;
    },
    setFilters: (
      state,
      action: PayloadAction<IGenericFilterFormSubmissionData>
    ) => {
      state.filters = action.payload;
      state.pagination = DEFAULT_PAGINATION;
    },
    setSetting: (
      state,
      action: PayloadAction<IDataTableSavedSetting | null>
    ) => {
      state.settings = action.payload;
    },
  },
});

export const dataTableActions = dataTableSlice.actions;

export default dataTableSlice.reducer;
