import { IDropdownItem } from "../../@type/IDropdownItem";
import { IItemMonologistic } from "../../@type/IGetAllItemMonologisticsResponse";
import { getAllItemMonologistics } from "../../api/getAllItemMonologistics";

export const createItemMonologisticsDropdownItem = (
  item: IItemMonologistic
): IDropdownItem => {
  return {
    id: item.itemCode,
    text: `${item.itemCode} - ${item.description}`,
    additionalInfo: { type: "ItemMonologistic", data: item },
  };
};

export const fetchItemMonologistics = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllItemMonologistics({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createItemMonologisticsDropdownItem(item)
  );
};
