import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllProductTypes } from "../../api/getAllProductTypes";

export const createProductTypeDropdownItem = (item: {
  englishName: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: item.englishName,
    value: item["@id"],
    data: item,
  };
};

export const fetchAllProductType = async (): Promise<Array<IDropdownItem>> => {
  const response = await getAllProductTypes();
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createProductTypeDropdownItem(item)
  );
};
