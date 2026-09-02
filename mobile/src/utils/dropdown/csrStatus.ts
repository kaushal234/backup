import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllCustomerServiceRecordStatus } from "../../api/getAllCustomerServiceRecordStatus";
import { CSR_FILTER_STATUS_MAP } from "../../constants/constants";
import { RootState } from "../../redux/store";

export const createCsrStatusDropdownItem = (item: string): IDropdownItem => {
  return {
    id: item,
    text: CSR_FILTER_STATUS_MAP[item] ?? item,
  };
};

export const fetchCsrStatusOptions = createAsyncThunk<
  Array<IDropdownItem>,
  void,
  { state: RootState }
>("fetchCsrStatusOptions", async (_, { getState }) => {
  const state = getState();
  if (state.dropdownOption.csrStatus.length) {
    return state.dropdownOption.csrStatus;
  }
  const response = await getAllCustomerServiceRecordStatus();
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createCsrStatusDropdownItem(item)
  );
});
