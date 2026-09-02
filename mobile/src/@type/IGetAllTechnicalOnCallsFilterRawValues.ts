import { IDropdownItem } from "./IDropdownItem";

export interface IGetAllTechnicalOnCallsFilterRawValues {
  serialNumber: IDropdownItem | null;
  status: Array<IDropdownItem>;
  assignee: Array<IDropdownItem>;
  unitOperationalStatus: Array<IDropdownItem>;
  technicianOnCallType: Array<IDropdownItem>;
  serviceActivity: Array<IDropdownItem>;
  indiceFactor: Array<IDropdownItem>;
  tags: Array<IDropdownItem>;
  createdBy: Array<IDropdownItem>;
  createdAfter: string | null;
  createdBefore: string | null;
  solvedAfter: string | null;
  solvedBefore: string | null;
  salesOrganisation: Array<IDropdownItem>;
  serviceOrganisation: Array<IDropdownItem>;
  manufacturerLocation: Array<IDropdownItem>;
  equipmentType: Array<IDropdownItem>;
  model: Array<IDropdownItem>;
  airport: Array<IDropdownItem>;
  late: IDropdownItem | null;
  factoryFlag: IDropdownItem | null;
  factoryFlagRecentlyClosed: IDropdownItem | null;
  survey: IDropdownItem | null;
  buyer: Array<IDropdownItem>;
  country: Array<IDropdownItem>;
  tocPart: string;
  endUser: Array<IDropdownItem>;
  sprPart: string;
  maintainer: Array<IDropdownItem>;
  confidential: IDropdownItem | null;
  title: string;
  technician: Array<IDropdownItem>;
  errorCodes: string;
  assigneeOrTechnician: Array<IDropdownItem>;
}
