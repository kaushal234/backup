import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllTechnicianOnCallStatus } from "../../api/getAllTechnicianOnCallStatus";
import { TOC_FILTER_STATUS_MAP } from "../../constants/constants";
import { RootState } from "../../redux/store";

export const createTocStatusDropdownItem = (item: string): IDropdownItem => {
  return {
    id: item,
    text: TOC_FILTER_STATUS_MAP[item] ?? item,
  };
};

export const fetchTocStatusOptions = createAsyncThunk<
  Array<IDropdownItem>,
  void,
  { state: RootState }
>("fetchTocStatusOptions", async (_, { getState }) => {
  const state = getState();
  if (state.dropdownOption.tocStatus.length) {
    return state.dropdownOption.tocStatus;
  }
  const response = await getAllTechnicianOnCallStatus();
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createTocStatusDropdownItem(item)
  );
});
