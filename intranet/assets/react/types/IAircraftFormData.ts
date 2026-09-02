import { IDropdownItem } from "./IDropdownItem";

export interface IAircraftFormData {
  id?: string;
  name: string;
  manufacturer: IDropdownItem;
}
