import { IDropdownItem } from "./IDropdownItem";

export interface ITocFormData {
  equipmentRecordSerialNo: IDropdownItem | null;
  airport: IDropdownItem | null;
  serviceOrganisation: IDropdownItem | null;
  assignee: IDropdownItem | null;
  technician: IDropdownItem | null;
  ifactor: IDropdownItem | null;
  errorCodes: string;
  payer: IDropdownItem | null;
  serviceActivity: IDropdownItem | null;
  unitOperationalStatus: IDropdownItem | null;
  tags: Array<IDropdownItem>;
  originalTitle: string;
  originalDescription: string;
  isTechnicianRequested: boolean;
  serviceTechnician: IDropdownItem | null;
  plannedDate: string | null;
  mainContact: IDropdownItem | null;
  contacts: Array<IDropdownItem>;
  hourMeter: number;
  customer: IDropdownItem | null;
  thirdPartyName: string;
  thirdPartyRef: string;
  serialNumber: string | null;
  confidential: boolean;
  reason: string;
}
