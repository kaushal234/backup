import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllExtranetUser } from "../../api/getAllExtranetUser";

export const createExtranetUserDropdownItem = (item: {
  email: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: `${item.email}`,
    value: item["@id"],
    data: item,
  };
};

export const fetchExtranetUser = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllExtranetUser({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createExtranetUserDropdownItem(item)
  );
};
