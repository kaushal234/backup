import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllRegion } from "../../api/getAllRegion";
import { IFetchAllRegionParams } from "../../types/IFetchAllRegionParams";

export const createRegionDropdownItem = (item: {
  name: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: item.name,
    value: item["@id"],
    data: item,
  };
};

export const fetchRegion = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllRegion({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createRegionDropdownItem(item)
  );
};

export const fetchAllRegion = async (
  params?: IFetchAllRegionParams
): Promise<Array<IDropdownItem>> => {
  const response = await getAllRegion({ ...params });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createRegionDropdownItem(item)
  );
};
