import { IDropdownItem } from "./IDropdownItem";

export interface IDataTableDownloadFormData {
  filename?: string;
  format?: IDropdownItem;
  strategy?: IDropdownItem;
}
