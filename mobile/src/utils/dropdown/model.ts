import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllProduct } from "../../api/getAllProduct";

export const createModelDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: item.name,
  };
};

export const fetchModelOptions = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllProduct({ searchText: value });
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createModelDropdownItem(item)
  );
};
