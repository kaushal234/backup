import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllBusinessUnit } from "../../api/getAllBusinessUnit";
import { IFetchAllBusinessUnitParams } from "../../types/IFetchAllBusinessUnitParams";

export const createBusinessUnitDropdownItem = (item: {
  name: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: item.name,
    value: item["@id"],
    data: item,
  };
};

export const fetchBusinessUnit = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllBusinessUnit({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createBusinessUnitDropdownItem(item)
  );
};

export const fetchAllBusinessUnit = async (
  params?: IFetchAllBusinessUnitParams
): Promise<Array<IDropdownItem>> => {
  const response = await getAllBusinessUnit({ ...params });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createBusinessUnitDropdownItem(item)
  );
};
