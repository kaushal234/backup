import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllTechnicianOnCallType } from "../../api/getAllTechnicianOnCallType";
import { RootState } from "../../redux/store";

export const createTechnicianOnCallTypeDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: item.name,
  };
};

export const fetchTechnicianOnCallTypeOptions = createAsyncThunk<
  Array<IDropdownItem>,
  void,
  { state: RootState }
>("fetchTechnicianOnCallTypeOptions", async (_, { getState }) => {
  const state = getState();
  if (state.dropdownOption.technicianOnCallType.length) {
    return state.dropdownOption.technicianOnCallType;
  }
  const response = await getAllTechnicianOnCallType();
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createTechnicianOnCallTypeDropdownItem(item)
  );
});
