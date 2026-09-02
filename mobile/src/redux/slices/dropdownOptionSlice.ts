/* eslint-disable no-param-reassign */
import { createSlice } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { fetchCountryOptions } from "../../utils/dropdown/country";
import { fetchCsrStatusOptions } from "../../utils/dropdown/csrStatus";
import { fetchManufacturerLocationOptions } from "../../utils/dropdown/manufacturerLocation";
import { fetchSalesOrganisationOptions } from "../../utils/dropdown/salesOrganisation";
import { fetchServiceActivityOptions } from "../../utils/dropdown/serviceActivity";
import { fetchTechnicianOnCallTagOptions } from "../../utils/dropdown/technicianOnCallTag";
import { fetchTechnicianOnCallTypeOptions } from "../../utils/dropdown/technicianOnCallType";
import { fetchTocStatusOptions } from "../../utils/dropdown/tocStatus";
import { fetchUnitOperationalStatusOptions } from "../../utils/dropdown/unitOperationalStatus";
import { fetchCsrPeopleOptions } from "../../utils/dropdown/people";
import { fetchContactOptions } from "../../utils/dropdown/contact";
import { fetchCsrContactOptions } from "../../utils/dropdown/csrContact";
import { fetchErContactOptions } from "../../utils/dropdown/erContact";

interface DropdownOptionState {
  fallback: Array<IDropdownItem>;
  unitOperationalStatus: Array<IDropdownItem>;
  technicianOnCallType: Array<IDropdownItem>;
  serviceActivity: Array<IDropdownItem>;
  tocTags: Array<IDropdownItem>;
  salesOrganisation: Array<IDropdownItem>;
  manufacturerLocation: Array<IDropdownItem>;
  tocStatus: Array<IDropdownItem>;
  csrStatus: Array<IDropdownItem>;
  country: Array<IDropdownItem>;
  csrPeople: Array<IDropdownItem>;
  contact: Array<IDropdownItem>;
  erContact: Array<IDropdownItem>;
  csrContact: Array<IDropdownItem>;
}

const initialState: DropdownOptionState = {
  fallback: [],
  unitOperationalStatus: [],
  technicianOnCallType: [],
  serviceActivity: [],
  tocTags: [],
  salesOrganisation: [],
  manufacturerLocation: [],
  tocStatus: [],
  csrStatus: [],
  country: [],
  csrPeople: [],
  contact: [],
  erContact: [],
  csrContact: [],
};

export const dropdownOptionSlice = createSlice({
  name: "dropdownOption",
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchUnitOperationalStatusOptions.fulfilled, (state, action) => {
        state.unitOperationalStatus = action.payload;
      })
      .addCase(fetchTechnicianOnCallTypeOptions.fulfilled, (state, action) => {
        state.technicianOnCallType = action.payload;
      })
      .addCase(fetchServiceActivityOptions.fulfilled, (state, action) => {
        state.serviceActivity = action.payload;
      })
      .addCase(fetchTechnicianOnCallTagOptions.fulfilled, (state, action) => {
        state.tocTags = action.payload;
      })
      .addCase(fetchSalesOrganisationOptions.fulfilled, (state, action) => {
        state.salesOrganisation = action.payload;
      })
      .addCase(fetchManufacturerLocationOptions.fulfilled, (state, action) => {
        state.manufacturerLocation = action.payload;
      })
      .addCase(fetchTocStatusOptions.fulfilled, (state, action) => {
        state.tocStatus = action.payload;
      })
      .addCase(fetchCsrStatusOptions.fulfilled, (state, action) => {
        state.csrStatus = action.payload;
      })
      .addCase(fetchCountryOptions.fulfilled, (state, action) => {
        state.country = action.payload;
      })
      .addCase(fetchCsrPeopleOptions.fulfilled, (state, action) => {
        state.csrPeople = action.payload;
      })
      .addCase(fetchContactOptions.fulfilled, (state, action) => {
        state.contact = action.payload;
      })
      .addCase(fetchErContactOptions.fulfilled, (state, action) => {
        state.erContact = action.payload;
      })
      .addCase(fetchCsrContactOptions.fulfilled, (state, action) => {
        state.csrContact = action.payload;
      });
  },
});

export default dropdownOptionSlice.reducer;
