import { IDropdownItem } from "./IDropdownItem";

export interface IAircraftCompatibilityFormData {
  id?: string;
  files: Array<IAircraftCompatibilityFileFormData>;
  products: Array<IDropdownItem>;
  aircrafts: Array<IDropdownItem>;
}

export interface IAircraftCompatibilityFileFormData {
  type: IDropdownItem;
  file: FileList | null;
}
