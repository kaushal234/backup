import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllPeopleSearch } from "../../api/getAllPeopleSearch";
import { createPeopleDropdownItem } from "./people";

export const fetchPeopleSearch = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllPeopleSearch({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createPeopleDropdownItem(item)
  );
};
