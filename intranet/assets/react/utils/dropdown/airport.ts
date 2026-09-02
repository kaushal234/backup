import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllAirport } from "../../api/getAllAirport";

export const createAirportDropdownItem = (item: {
  "@id": string;
  code: string;
  name: string | null;
  cityName: string;
}): IDropdownItem => {
  return {
    label: `${item.code} ${item.name ?? ""} - ${item.cityName}`,
    value: item["@id"],
    data: item,
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

export const fetchAllAirports = async (): Promise<Array<IDropdownItem>> => {
  const response = await getAllAirport({});
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createAirportDropdownItem(item)
  );
};
