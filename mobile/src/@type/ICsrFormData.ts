import { IDropdownItem } from "./IDropdownItem";

export interface ICsrFormData {
  airport: IDropdownItem | null;
  title: string;
  description: string;
  serviceTechnician: IDropdownItem | null;
  plannedDate: string | null;
  status: IDropdownItem | null;
  contact: IDropdownItem | null;
}
