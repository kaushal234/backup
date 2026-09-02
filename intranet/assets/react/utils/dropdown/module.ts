import { getAllModule } from "../../api/getAllModule";
import { IDropdownItem } from "../../types/IDropdownItem";

export const createModuleDropdownItem = (item: {
  "@id": string;
  name: string | null;
  shortDescription: string | null;
}): IDropdownItem => {
  return {
    value: item["@id"],
    label: `${item.name ?? ""} ${item.shortDescription ?? ""}`,
  };
};

export const fetchModule = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllModule({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createModuleDropdownItem(item)
  );
};
