import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllLocation } from "../../api/getAllLocation";

export const createLocationDropdownItem = (item: {
  name: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: item.name,
    value: item["@id"],
    data: item,
  };
};

export const fetchLocation = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllLocation({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createLocationDropdownItem(item)
  );
};
