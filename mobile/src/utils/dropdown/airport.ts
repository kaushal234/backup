import { IDropdownItem } from "../../@type/IDropdownItem";
import { getAllAirport } from "../../api/getAllAirport";

export const createAirportDropdownItem = (item: {
  "@id": string;
  code: string;
  name: string | null;
  cityName: string;
}): IDropdownItem => {
  return {
    id: item["@id"],
    text: `${item.code} ${item.name ?? ""} - ${item.cityName}`,
  };
};

export const fetchAirport = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllAirport({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createAirportDropdownItem(item)
  );
};
