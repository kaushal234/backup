import { IDropdownItem } from "./IDropdownItem";
import { ITocStatusPartFormData } from "./ITocStatusPartFormData";

export interface ITechnicianOnCallStatusModalFormData {
  status?: IDropdownItem;
  reason?: string;
  originalSymptoms?: string;
  originalRootCause?: string;
  originalSolution?: string;
  defectiveParts?: ITocStatusPartFormData;
  thirdPartyJobDescription?: string;
  thirdPartyHours?: number;
  thirdPartyName?: string | null;
}
