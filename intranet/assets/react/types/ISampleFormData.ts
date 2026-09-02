import { IDropdownItem } from "./IDropdownItem";

export interface ISampleFormData {
  username?: string;
  description?: string;
  arrival1?: IDropdownItem | null;
  departure1?: IDropdownItem | null;
  arrival2?: Array<IDropdownItem>;
  departure2?: Array<IDropdownItem>;
  airport?: IDropdownItem | null;
  airports?: Array<IDropdownItem>;
  travelDetails?: string;
  departureDate?: Date | null;
  terms?: boolean;
  conditions?: boolean;
  passportHolder?: string;
  passportFile?: FileList;
}
