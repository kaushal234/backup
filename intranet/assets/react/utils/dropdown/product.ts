import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllProducts } from "../../api/getAllProducts";

export const createProductDropdownItem = (item: {
  "@id": string;
  name: string | null;
}): IDropdownItem => {
  return {
    label: `${item.name}`,
    value: item["@id"],
    data: item,
  };
};

export const fetchProduct = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllProducts({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createProductDropdownItem(item)
  );
};
