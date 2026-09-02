import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllServiceActivities } from "../../api/getAllServiceActivities";
import { RootState } from "../../redux/store";

export const createServiceActivityDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: item.name,
  };
};

export const fetchServiceActivityOptions = createAsyncThunk<
  Array<IDropdownItem>,
  void,
  { state: RootState }
>("fetchServiceActivityOptions", async (_, { getState }) => {
  const state = getState();
  if (state.dropdownOption.serviceActivity.length) {
    return state.dropdownOption.serviceActivity;
  }
  const response = await getAllServiceActivities();
  return (response?.data?.["hydra:member"] ?? []).map((item) => ({
    id: item["@id"],
    text: item.name,
  }));
});
