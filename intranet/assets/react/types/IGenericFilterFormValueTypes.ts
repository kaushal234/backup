import { IDropdownItem } from "./IDropdownItem";

export type IGenericFilterFormValueTypes =
  | string
  | IDropdownItem
  | Date
  | null
  | Array<IDropdownItem>;
