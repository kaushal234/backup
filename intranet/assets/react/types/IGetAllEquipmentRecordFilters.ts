export interface IGetAllEquipmentRecordFilters {
  serialNumber?: string;
  "product.family.productType"?: Array<string>;
  product?: Array<string>;
  airport?: string;
  buyer?: string;
  endUser?: string;
  maintainer?: string;
}
