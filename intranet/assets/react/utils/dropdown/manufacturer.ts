import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllManufacturers } from "../../api/getAllManufacturers";

export const createManufacturerDropdownItem = (item: {
  "@id": string;
  name: string | null;
}): IDropdownItem => {
  return {
    label: `${item.name}`,
    value: item["@id"],
    data: item,
  };
};

export const fetchManufacturer = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllManufacturers({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createManufacturerDropdownItem(item)
  );
};

export const fetchAllManufacturers = async (): Promise<
  Array<IDropdownItem>
> => {
  const response = await getAllManufacturers({});
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createManufacturerDropdownItem(item)
  );
};
