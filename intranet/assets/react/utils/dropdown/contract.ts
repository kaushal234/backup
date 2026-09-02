import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllContract } from "../../api/getAllContract";

export const createContractDropdownItem = (item: {
  shortDescription: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: item.shortDescription,
    value: item["@id"],
    data: item,
  };
};

export const fetchAllContract = async (): Promise<Array<IDropdownItem>> => {
  const response = await getAllContract();
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createContractDropdownItem(item)
  );
};
