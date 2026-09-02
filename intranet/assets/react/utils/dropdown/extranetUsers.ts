import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllExtranetUsersRelatedToCustomer } from "../../api/getAllExtranetUsersRelatedToCustomer";

export const createExtranetUserDropdownItem = (item: {
  "@id": string;
  id: number | null;
  lastname: string | null;
  firstname: string | null;
}): IDropdownItem => {
  return {
    label: `${item.firstname} ${item.lastname} (#${item.id})`,
    value: item["@id"],
    data: item,
  };
};

export const fetchAllExtranetUsersRelatedToCustomer = async (
  customerIri: string,
  searchText?: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllExtranetUsersRelatedToCustomer({
    customerIri,
    searchText,
  });

  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createExtranetUserDropdownItem(item)
  );
};
