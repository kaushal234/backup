import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllLocation } from "../../api/getAllLocation";
import { RootState } from "../../redux/store";

export const createSalesOrganisationDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: item.name,
  };
};

export const fetchSalesOrganisationOptions = createAsyncThunk<
  Array<IDropdownItem>,
  void,
  { state: RootState }
>("fetchSalesOrganisationOptions", async (_, { getState }) => {
  const state = getState();
  if (state.dropdownOption.salesOrganisation.length) {
    return state.dropdownOption.salesOrganisation;
  }
  const response = await getAllLocation({ sso: true });
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createSalesOrganisationDropdownItem(item)
  );
});
