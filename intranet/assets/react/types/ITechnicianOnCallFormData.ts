import { IDropdownItem } from "./IDropdownItem";

export interface ITechnicianOnCallFormData {
  id?: number;
  originalTitle?: string;
  originalDescription?: string;
  equipmentRecord?: IDropdownItem | null;
  serialNumber?: string;
  assignee?: IDropdownItem | null;
  technician?: IDropdownItem | null;
  airport?: IDropdownItem | null;
  errorCodes?: string;
  unitOperationalStatus?: IDropdownItem | null;
  technicianOnCallType?: IDropdownItem | null;
  serviceActivity?: IDropdownItem | null;
  salesOrganisationService?: IDropdownItem | null;
  indiceFactor?: IDropdownItem | null;
  hourMeter?: string;
  tags?: Array<IDropdownItem>;
  mainContact?: IDropdownItem | null;
  contacts?: Array<IDropdownItem>;
  thirdPartyName?: string;
  thirdPartyRef?: string;
  customer?: IDropdownItem | null;
  confidential?: boolean;
  confidentialReason?: string;
  canCreateCsr?: boolean;
  nestedCustomerServiceRecord?: INestedCustomerServiceRecord;
}

interface INestedCustomerServiceRecord {
  id?: number;
  leader?: IDropdownItem | null;
  plannedAt?: Date | null;
}
