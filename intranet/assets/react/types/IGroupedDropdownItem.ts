import { IDropdownItem } from "./IDropdownItem";

export interface IGroupedDropdownItem {
  label: string;
  options: Array<IDropdownItem>;
}
