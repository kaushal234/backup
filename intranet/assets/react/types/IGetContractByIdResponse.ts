export type IGetContractByIdResponse = IContract;

export interface IContract {
  "@context": string;
  "@id": string;
  "@type": string;
  shortDescription: string;
  description: string;
  createdAt: string;
  status: string;
  startDate: string;
  expirationDate: string | null;
  indefinitePeriodType: true;
  confidential: true;
  automaticRenewal: true;
  renewalPeriod: number | null;
  renewalUnit: string | null;
  observationTerm: string | null;
  observationValue: string | null;
  externalParty: string | null;
  internalParty: Array<string>;
  createdBy: IPeople;
  owner: IPeople;
  value: number | null;
  currency: ICurrency | null;
  subCategory: ISubCategory;
  jurisdiction: string | null;
  parentContract: IContract | null;
  otherPartySignatories: Array<string>;
  divisions: Array<IDivision>;
  businessUnits: Array<IBusinessUnit>;
  customers: Array<ICustomer>;
  regions: Array<IRegion>;
  premises: Array<IPremise>;
  alvestSignatories: Array<IPeople>;
  childContracts: Array<IContract>;
  files: Array<unknown>;
  id: number;
  observationStatus: string | null;
  comment: string | null;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  firstname: string | null;
  lastname: string | null;
}

interface ICurrency {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
}

interface ISubCategory {
  "@id": string;
  "@type": string;
  name: string;
  category: ICategory;
  id: number;
}

interface ICategory {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
}

interface IDivision {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
}

interface IBusinessUnit {
  "@type": string;
  "@id": string;
  id: number;
  name: string;
  domain: string | null;
  region: IRegion | null;
}

interface IRegion {
  "@id": string;
  "@type": string;
  name: string;
}

interface IPremise {
  "@type": string;
  "@id": string;
  name: string;
  description: string;
  address: IAddressWithCountry;
  latitude: number | null;
  longitude: number | null;
  supportTeam: string | null;
  id: number;
  tags: Array<string>;
  count: number | null;
}

interface IAddressWithCountry {
  "@type": string;
  "@id": string;
  country: string | null;
  street1: string | null;
  street2: string | null;
  postalCode: string | null;
  city: string | null;
  town: string | null;
  state: string | null;
}

export interface ICustomer {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  address: IAddress;
  country: string | null;
  mainSalesRepresentative: string | null;
  status: string;
  customerTypes: Array<ICustomerType>;
  legacyId: number;
}

interface IAddress {
  "@type": string;
  "@id": string;
  street1: string | null;
  street2: string | null;
  postalCode: string | null;
  city: string | null;
  town: string | null;
  state: string | null;
}

interface ICustomerType {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  legacyId: number;
}
