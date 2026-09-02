import { IHydraCollection } from "./IHydraCollection";

export type IGetAllCustomerRelationshipTeamsResponse =
  IHydraCollection<ICustomerRelationshipTeam>;

interface ICustomerRelationshipTeam {
  "@id": string;
  "@type": string;
  id: number;
  customer: ICustomer;
  salesRepresentative: IPeople;
  partsRepresentative: IPeople;
  serviceRepresentative: IPeople;
  partsLocation: ILocation;
  serviceLocation: ILocation;
  erpLocation: ILocation;
  customerBusinessPartnerCode: string;
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
  username: string;
  email: string;
  legacyId: number | null;
  firstname: string;
  lastname: string;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
  legacyId: number | null;
}
