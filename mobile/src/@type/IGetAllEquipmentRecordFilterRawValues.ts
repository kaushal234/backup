import { IDropdownItem } from "./IDropdownItem";

export interface IGetAllEquipmentRecordFilterRawValues {
  serialNumber: IDropdownItem | null;
  equipmentType: Array<IDropdownItem>;
  model: Array<IDropdownItem>;
  airport: IDropdownItem | null;
  buyer: IDropdownItem | null;
  endUser: IDropdownItem | null;
  maintainer: IDropdownItem | null;
}
