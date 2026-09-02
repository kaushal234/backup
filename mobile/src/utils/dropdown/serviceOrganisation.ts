import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllLocation } from "../../api/getAllLocation";

export const createServiceOrganisationDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: item.name,
  };
};

export const fetchServiceOrganisationOptions = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllLocation({ searchText: value });
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createServiceOrganisationDropdownItem(item)
  );
};
