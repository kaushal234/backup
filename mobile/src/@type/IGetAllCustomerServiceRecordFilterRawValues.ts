import { IDropdownItem } from "./IDropdownItem";

export interface IGetAllCustomerServiceRecordFilterRawValues {
  serialNumber: IDropdownItem | null;
  status: Array<IDropdownItem>;
  createdBy: IDropdownItem | null;
  createdAfter: string | null;
  createdBefore: string | null;
  salesOrganisation: IDropdownItem | null;
  serviceOrganisation: IDropdownItem | null;
  manufacturerLocation: IDropdownItem | null;
  equipmentType: Array<IDropdownItem>;
  model: Array<IDropdownItem>;
  airport: IDropdownItem | null;
  serviceTechnician: IDropdownItem | null;
  endUser: IDropdownItem | null;
  completedAfter: string | null;
  completedBefore: string | null;
  closedAfter: string | null;
  closedBefore: string | null;
  country: Array<IDropdownItem>;
  discriminator: Array<IDropdownItem>;
}
