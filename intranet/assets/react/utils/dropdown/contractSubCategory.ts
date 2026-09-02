import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllContractSubCategory } from "../../api/getAllContractSubCategory";
import { IFetchAllContractSubCategoryParams } from "../../types/IFetchAllContractSubCategoryParams";

export const createContractSubCategoryDropdownItem = (item: {
  name: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: item.name,
    value: item["@id"],
    data: item,
  };
};

export const fetchAllContractSubCategory = async (
  params?: IFetchAllContractSubCategoryParams
): Promise<Array<IDropdownItem>> => {
  const response = await getAllContractSubCategory({ ...params });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createContractSubCategoryDropdownItem(item)
  );
};
