import Translator from "bazinga-translator";
import { getAllTechnicianOnCallType } from "../../api/getAllTechnicianOnCallType";
import { IDropdownItem } from "../../types/IDropdownItem";

export const createTechnicianOnCallTypeDropdownItem = (item: {
  "@id": string;
  name: string;
}): IDropdownItem => {
  return {
    value: item["@id"],
    label: Translator.trans(item.name),
  };
};

export const fetchTechnicianOnCallTypeOptions = async (): Promise<
  Array<IDropdownItem>
> => {
  const response = await getAllTechnicianOnCallType();
  return (response?.data?.["hydra:member"] ?? []).map((item) =>
    createTechnicianOnCallTypeDropdownItem(item)
  );
};

export const fetchDefaultTechnicianOnCallType = async (): Promise<
  IDropdownItem | undefined
> => {
  const response = await getAllTechnicianOnCallType();
  const defaultItem = (response?.data?.["hydra:member"] ?? []).find(
    (item) => item.name === "toc.type.not_define_yet"
  );
  return defaultItem
    ? createTechnicianOnCallTypeDropdownItem(defaultItem)
    : undefined;
};
