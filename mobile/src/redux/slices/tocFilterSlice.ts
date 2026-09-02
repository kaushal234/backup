/* eslint-disable no-param-reassign */
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { IGetAllTechnicalOnCallsFilterRawValues } from "../../@type/IGetAllTechnicalOnCallsFilterRawValues";
import { LS_KEYS, TOC_STATUS_FILTER_DEFAULT } from "../../constants/constants";

interface TocFilterState {
  filters: IGetAllTechnicalOnCallsFilterRawValues;
  isServiceOrganisationPopulated: boolean;
}

export const initialState: TocFilterState = {
  filters: {
    serialNumber: null,
    status: TOC_STATUS_FILTER_DEFAULT,
    assignee: [],
    unitOperationalStatus: [],
    technicianOnCallType: [],
    serviceActivity: [],
    indiceFactor: [],
    tags: [],
    createdBy: [],
    createdAfter: null,
    createdBefore: null,
    solvedAfter: null,
    solvedBefore: null,
    salesOrganisation: [],
    serviceOrganisation: [],
    manufacturerLocation: [],
    equipmentType: [],
    model: [],
    airport: [],
    late: null,
    factoryFlag: null,
    factoryFlagRecentlyClosed: null,
    survey: null,
    buyer: [],
    country: [],
    tocPart: "",
    endUser: [],
    sprPart: "",
    maintainer: [],
    confidential: null,
    title: "",
    technician: [],
    errorCodes: "",
    assigneeOrTechnician: [],
  },
  isServiceOrganisationPopulated: true,
};

export const tocFilterSlice = createSlice({
  name: "tocFilter",
  initialState,
  reducers: {
    setTocFilters: (
      state,
      action: PayloadAction<IGetAllTechnicalOnCallsFilterRawValues>
    ) => {
      state.filters = action.payload;
      localStorage.setItem(LS_KEYS.toc_filters, JSON.stringify(action.payload));
    },
    setIsServiceOrganisationPopulated: (
      state,
      action: PayloadAction<boolean>
    ) => {
      state.isServiceOrganisationPopulated = action.payload;
    },
  },
});

export const { setTocFilters, setIsServiceOrganisationPopulated } =
  tocFilterSlice.actions;

export default tocFilterSlice.reducer;
