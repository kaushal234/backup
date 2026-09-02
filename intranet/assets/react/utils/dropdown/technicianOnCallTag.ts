import { getAllTechnicianOnCallTag } from "../../api/getAllTechnicianOnCallTag";
import { TOC_FILTER_TAG_MAP } from "../../constants/constants";
import { IDropdownItem } from "../../types/IDropdownItem";

export const createTechnicianOnCallTagDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    value: item["@id"],
    label: TOC_FILTER_TAG_MAP[item.name.replace("toc.tags.", "")] ?? item.name,
  };
};

export const fetchTechnicianOnCallTagOptions = async (): Promise<
  Array<IDropdownItem>
> => {
  const response = await getAllTechnicianOnCallTag();
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createTechnicianOnCallTagDropdownItem(item)
  );
};
