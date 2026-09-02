import { createAsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllPeople } from "../../api/getAllPeople";
import { RootState } from "../../redux/store";

export const createPeopleDropdownItem = (item: {
  "@id": string;
  lastname: string | null;
  firstname: string | null;
  email: string;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: `${item.lastname} ${item.firstname} - ${item.email}`,
  };
};

export const fetchPeople = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllPeople({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createPeopleDropdownItem(item)
  );
};

export const fetchCsrPeopleOptions = createAsyncThunk<
  Array<IDropdownItem>,
  void,
  { state: RootState }
>("fetchCsrPeopleOptions", async (_, { getState }) => {
  const state = getState();
  if (state.dropdownOption.csrPeople.length) {
    return state.dropdownOption.csrPeople;
  }
  const response = await getAllPeople({ isCsr: true });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createPeopleDropdownItem(item)
  );
});

export const fetchTechnician = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllPeople({
    searchText: value,
    hasGroups: ["GG_SERVICE", "GG_SERVICE_AGENTS"],
  });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createPeopleDropdownItem(item)
  );
};
