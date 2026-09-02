import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllAircrafts } from "../../api/getAllAircrafts";

export const createAircraftDropdownItem = (item: {
  "@id": string;
  name: string | null;
}): IDropdownItem => {
  return {
    label: `${item.name}`,
    value: item["@id"],
    data: item,
  };
};

export const fetchAircraft = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllAircrafts({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createAircraftDropdownItem(item)
  );
};
