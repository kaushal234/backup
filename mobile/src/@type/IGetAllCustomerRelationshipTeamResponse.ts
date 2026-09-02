import { IHydraCollection } from "./IHydraCollection";

export type IGetAllCustomerRelationshipTeamResponse =
  IHydraCollection<IEquipmentRecord>;

export interface IEquipmentRecord {
  "@id": string;
  "@type": string;
  id: number;
  customer: ICustomer | null;
  salesRepresentative: IPeople | null;
  partsRepresentative: IPeople | null;
  serviceRepresentative: IPeople | null;
  partsLocation: ILocation | null;
  serviceLocation: ILocation | null;
  erpLocation: ILocation | null;
  customerBusinessPartnerCode: null;
  legacyId: number | null;
}

interface ICustomer {
  "@id": string;
  "@type": string;
  name: string;
  legacyId: number | null;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
  legacyId: number | null;
}
