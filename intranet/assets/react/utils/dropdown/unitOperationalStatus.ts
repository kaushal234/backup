import { getAllUnitOperationalStatus } from "../../api/getAllUnitOperationalStatus";
import { IDropdownItem } from "../../types/IDropdownItem";

export const createUnitOperationalStatusDropdownItem = (item: {
  "@id": string;
  description: string;
  name: string;
}): IDropdownItem => {
  return {
    value: item["@id"],
    label: `${item.description} - ${item.name}`,
  };
};

export const fetchUnitOperationalStatusOptions = async (): Promise<
  Array<IDropdownItem>
> => {
  const response = await getAllUnitOperationalStatus();
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createUnitOperationalStatusDropdownItem(item)
  );
};
