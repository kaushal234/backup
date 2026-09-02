import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllProductTypes } from "../../api/getAllProductTypes";

export const createEquipmentTypeDropdownItem = (item: {
  "@id": string;
  englishName: string | null;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: item.englishName ?? "",
  };
};

export const fetchEquipmentTypeOptions = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllProductTypes({ searchText: value });
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createEquipmentTypeDropdownItem(item)
  );
};
