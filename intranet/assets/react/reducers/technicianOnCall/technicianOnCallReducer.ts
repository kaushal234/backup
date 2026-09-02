/* eslint-disable no-param-reassign */
import { createSlice } from "@reduxjs/toolkit";

interface ITechnicianOnCallState {
  technicianOnCalls: any;
  technicianOnCallsListIsLoading: any;
  technicianOnCallIsLoading: any;
  error: any;
  showFiles: any;
  showSuccess: any;
  showError: any;
  errorMessage: any;
  successMessages: any;
}

let initialState: ITechnicianOnCallState = {
  technicianOnCalls: {},
  technicianOnCallsListIsLoading: false,
  technicianOnCallIsLoading: false,
  error: null,
  showFiles: false,
  showSuccess: false,
  showError: false,
  errorMessage: "",
  successMessages: [],
};

if (
  window.SF_INITIAL_STORE_STATE &&
  window.SF_INITIAL_STORE_STATE.technicianOnCall
) {
  initialState = {
    ...initialState,
    ...window.SF_INITIAL_STORE_STATE.technicianOnCall,
  };
}

export const technicianOnCallSlice = createSlice({
  name: "technicianOnCall",
  initialState,
  reducers: {
    tocHideSuccessAlert: (state) => {
      state.showSuccess = false;
      state.showError = false;
      state.showFiles = true;
    },
    tocHideErrorAlert: (state) => {
      state.showError = false;
      state.showSuccess = false;
      state.errorMessage = "";
    },
  },
});

export const { tocHideSuccessAlert, tocHideErrorAlert } =
  technicianOnCallSlice.actions;

export default technicianOnCallSlice.reducer;
