import { createAsyncThunk } from "@reduxjs/toolkit";
import { ICountry, IDropdownItem } from "../../@type/IDropdownItem";
import { getAllCountry } from "../../api/getAllCountry";
import { RootState } from "../../redux/store";

export const createCountryDropdownItem = (item: ICountry): IDropdownItem => {
  return {
    id: item["@id"],
    text: `${item.name ?? ""} (Phone code: ${item.phoneCode ?? ""})`,
    additionalInfo: {
      type: "Country",
      data: item,
    },
  };
};

export const fetchCountryOptions = createAsyncThunk<
  Array<IDropdownItem>,
  void,
  { state: RootState }
>("fetchCountryOptions", async (_, { getState }) => {
  const state = getState();
  if (state.dropdownOption.country.length) {
    return state.dropdownOption.country;
  }
  const response = await getAllCountry({});
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createCountryDropdownItem(item)
  );
});

export const fetchCountry = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllCountry({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createCountryDropdownItem(item)
  );
};
