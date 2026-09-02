import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllCustomer } from "../../api/getAllCustomer";
import { convertToTitleCase } from "../utils";

export const createCustomerDropdownItem = (item: {
  "@id": string;
  name: string;
  status?: string;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: `${item.name}${
      item.status ? ` (${convertToTitleCase(item.status)})` : ""
    }`,
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
