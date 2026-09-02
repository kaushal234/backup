import { IDropdownItem } from "./IDropdownItem";

export interface IExtranetUserFormData {
  email?: string;
  lastname?: string;
  firstname?: string;
  division?: string;
  department?: string;
  jobTitle?: string;
  phone?: string;
  language?: IDropdownItem;
  crt?: IDropdownItem;
  country?: IDropdownItem;
}
