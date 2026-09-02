import { TOC_STATUS_MAP } from "../../constants";
import { IDropdownItem } from "../../types/IDropdownItem";

export const createTocStatusDropdownItem = (item: string): IDropdownItem => {
  return {
    data: item,
    label: TOC_STATUS_MAP[item] ?? item,
    value: item,
  };
};
