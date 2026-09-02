import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllCustomer } from "../../api/getAllCustomer";
import { convertToTitleCase } from "../utils";

export const createCustomerDropdownItem = (item: {
  name: string;
  "@id": string;
  status?: string;
}): IDropdownItem => {
  return {
    label: `${item.name}${
      item.status ? ` (${convertToTitleCase(item.status)})` : ""
    }`,
    value: item["@id"],
    data: item,
  };
};

export const fetchCustomer = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllCustomer({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createCustomerDropdownItem(item)
  );
};
