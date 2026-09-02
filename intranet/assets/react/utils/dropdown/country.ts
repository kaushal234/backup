import { getAllCountry } from "../../api/getAllCountry";
import { ICountry, IDropdownItem } from "../../types/IDropdownItem";

export const createCountryDropdownItem = (item: ICountry): IDropdownItem => {
  return {
    value: item["@id"],
    label: `${item.name ?? ""} ${
      item.phoneCode ? `(Phone code: ${item.phoneCode})` : ""
    }`,
    data: {
      type: "Country" as const,
      data: item,
    },
  };
};

export const fetchCountry = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllCountry({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createCountryDropdownItem(item)
  );
};
