import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllDivision } from "../../api/getAllDivision";

export const createDivisionDropdownItem = (item: {
  name: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: item.name,
    value: item["@id"],
    data: item,
  };
};

export const fetchDivision = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllDivision({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createDivisionDropdownItem(item)
  );
};
