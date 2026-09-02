import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllContractCategory } from "../../api/getAllContractCategory";

export const createContractCategoryDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    label: item.name,
    value: item["@id"],
    data: item,
  };
};

export const fetchAllContractCategory = async (): Promise<
  Array<IDropdownItem>
> => {
  const response = await getAllContractCategory();
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createContractCategoryDropdownItem(item)
  );
};
