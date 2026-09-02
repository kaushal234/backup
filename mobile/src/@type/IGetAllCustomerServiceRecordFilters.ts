export interface IGetAllCustomerServiceRecordFilters {
  equipmentRecord?: string;
  status?: Array<string>;
  createdBy?: string;
  "createdAt[after]"?: string;
  "createdAt[before]"?: string;
  "equipmentRecord.salesOrganisation"?: string;
  "equipmentRecord.salesOrganisationService"?: string;
  "equipmentRecord.manufacturerLocation"?: string;
  "equipmentRecord.product.family.productType"?: Array<string>;
  "equipmentRecord.product"?: Array<string>;
  airport?: string;
  "interventions.leader"?: string;
  "equipmentRecord.endUser"?: string;
  "completedAt[after]"?: string;
  "completedAt[before]"?: string;
  "closedAt[after]"?: string;
  "closedAt[before]"?: string;
  "airport.country"?: Array<string>;
  discriminator?: Array<string>;
}
