import { IDropdownItem } from "../../types/IDropdownItem";
import { getAllProductFamilyTags } from "../../api/getAllProductFamilyTags";

export const createProductFamilyTagDropdownItem = (item: {
  name: string;
  "@id": string;
}): IDropdownItem => {
  return {
    label: item.name,
    value: item["@id"],
    data: item,
  };
};

export const fetchAllProductFamilyTags = async (): Promise<
  Array<IDropdownItem>
> => {
  const response = await getAllProductFamilyTags();
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createProductFamilyTagDropdownItem(item)
  );
};
