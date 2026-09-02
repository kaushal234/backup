import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllCurrency } from "../../api/getAllCurrency";

export const createCurrencyDropdownItem = (item: {
  name: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: item.name,
    value: item["@id"],
    data: item,
  };
};

export const fetchAllCurrency = async (): Promise<Array<IDropdownItem>> => {
  const response = await getAllCurrency();
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createCurrencyDropdownItem(item)
  );
};
