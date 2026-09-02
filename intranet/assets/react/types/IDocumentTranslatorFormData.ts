import { IDropdownItem } from "./IDropdownItem";

export interface IDocumentTranslatorFormData {
  language?: IDropdownItem | null;
  formality?: IDropdownItem | null;
  file?: FileList | undefined;
}
