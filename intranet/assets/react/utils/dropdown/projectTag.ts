import { getAllProjectTag } from "../../api/getAllProjectTag";
import { IDropdownItem } from "../../types/IDropdownItem";

export const createProjectTagDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    value: item["@id"],
    label: item.name,
  };
};

export const fetchProjectTag = async (
  value: string
): Promise<Array<IDropdownItem>> => {
  const response = await getAllProjectTag({ searchText: value });
  return (response.data?.["hydra:member"] ?? []).map((item) =>
    createProjectTagDropdownItem(item)
  );
};
