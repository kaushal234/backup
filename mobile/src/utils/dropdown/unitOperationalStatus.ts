import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllUnitOperationalStatus } from "../../api/getAllUnitOperationalStatus";
import { RootState } from "../../redux/store";

export const createUnitOperationalStatusDropdownItem = (item: {
  "@id": string;
  description: string;
  name: string;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: `${item.description} - ${item.name}`,
  };
};

export const fetchUnitOperationalStatusOptions = createAsyncThunk<
  Array<IDropdownItem>,
  void,
  { state: RootState }
>("fetchUnitOperationalStatusOptions", async (_, { getState }) => {
  const state = getState();
  if (state.dropdownOption.unitOperationalStatus.length) {
    return state.dropdownOption.unitOperationalStatus;
  }
  const response = await getAllUnitOperationalStatus();
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createUnitOperationalStatusDropdownItem(item)
  );
});
