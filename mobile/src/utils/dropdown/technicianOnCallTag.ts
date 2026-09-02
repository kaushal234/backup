import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { RootState } from "../../redux/store";
import { getAllTechnicianOnCallTag } from "../../api/getAllTechnicianOnCallTag";
import { TOC_FILTER_TAG_MAP } from "../../constants/constants";

export const createTechnicianOnCallTagDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: TOC_FILTER_TAG_MAP[item.name.replace("toc.tags.", "")] ?? item.name,
  };
};

export const fetchTechnicianOnCallTagOptions = createAsyncThunk<
  Array<IDropdownItem>,
  void,
  { state: RootState }
>("fetchTechnicianOnCallTagOptions", async (_, { getState }) => {
  const state = getState();
  if (state.dropdownOption.tocTags.length) {
    return state.dropdownOption.tocTags;
  }
  const response = await getAllTechnicianOnCallTag();
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createTechnicianOnCallTagDropdownItem(item)
  );
});
