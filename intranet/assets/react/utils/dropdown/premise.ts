import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllPremise } from "../../api/getAllPremise";

export const createPremiseDropdownItem = (item: {
  name: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: item.name,
    value: item["@id"],
    data: item,
  };
};

export const fetchPremise = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllPremise({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createPremiseDropdownItem(item)
  );
};
