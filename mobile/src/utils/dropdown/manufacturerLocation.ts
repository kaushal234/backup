import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllLocation } from "../../api/getAllLocation";
import { RootState } from "../../redux/store";

export const createManufacturerLocationDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: item.name,
  };
};

export const fetchManufacturerLocationOptions = createAsyncThunk<
  Array<IDropdownItem>,
  void,
  { state: RootState }
>("fetchManufacturerLocationOptions", async (_, { getState }) => {
  const state = getState();
  if (state.dropdownOption.manufacturerLocation.length) {
    return state.dropdownOption.manufacturerLocation;
  }
  const response = await getAllLocation({ factory: true });
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createManufacturerLocationDropdownItem(item)
  );
});
