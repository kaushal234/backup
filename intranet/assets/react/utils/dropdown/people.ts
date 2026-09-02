import { getAllPeople } from "../../api/getAllPeople";
import { IDropdownItem } from "../../types/IDropdownItem";

interface IFetchFilteredPeopleParams {
  value: string;
  groups?: Array<string>;
}

export const createPeopleDropdownItem = (item: {
  "@id": string;
  lastname: string | null;
  firstname: string | null;
  email: string;
}): IDropdownItem => {
  return {
    value: item["@id"],
    label: `${item.lastname} ${item.firstname} - ${item.email}`,
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

export const fetchFilteredPeople = async (
  data: IFetchFilteredPeopleParams
): Promise<Array<IDropdownItem>> => {
  const response = await getAllPeople({
    searchText: data.value,
    hasGroups: data.groups,
  });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createPeopleDropdownItem(item)
  );
};
