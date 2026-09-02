/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { CSR_STATUS_FILTER_DEFAULT, LS_KEYS } from "../../constants/constants";
import { IGetAllCustomerServiceRecordFilterRawValues } from "../../@type/IGetAllCustomerServiceRecordFilterRawValues";

interface CsrFilterState {
  filters: IGetAllCustomerServiceRecordFilterRawValues;
  isServiceOrganisationPopulated: boolean;
}

export const initialState: CsrFilterState = {
  filters: {
    serialNumber: null,
    status: CSR_STATUS_FILTER_DEFAULT,
    createdBy: null,
    createdAfter: null,
    createdBefore: null,
    salesOrganisation: null,
    serviceOrganisation: null,
    manufacturerLocation: null,
    equipmentType: [],
    model: [],
    airport: null,
    serviceTechnician: null,
    endUser: null,
    completedAfter: null,
    completedBefore: null,
    closedAfter: null,
    closedBefore: null,
    country: [],
    discriminator: [],
  },
  isServiceOrganisationPopulated: true,
};

export const csrFilterSlice = createSlice({
  name: "csrFilter",
  initialState,
  reducers: {
    setCsrFilters: (
      state,
      action: PayloadAction<IGetAllCustomerServiceRecordFilterRawValues>
    ) => {
      state.filters = action.payload;
      localStorage.setItem(LS_KEYS.csr_filters, JSON.stringify(action.payload));
    },
    setIsServiceOrganisationPopulated: (
      state,
      action: PayloadAction<boolean>
    ) => {
      state.isServiceOrganisationPopulated = action.payload;
    },
  },
});

export const { setCsrFilters, setIsServiceOrganisationPopulated } =
  csrFilterSlice.actions;

export default csrFilterSlice.reducer;
