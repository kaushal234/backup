export type IGetLocationByIdResponse = ILocation;

export interface ILocation {
  "@context": string;
  "@id": string;
  "@type": string;
  id: number | null;
  name: string;
  company: string;
  capability: ILocationCapability;
  contact: ILocationContact;
  state: ILocationState;
  address: IAddressWithCountry;
  domain: string | null;
  internalNetworkAddress: string | null;
  erp: number | null;
  erpInLN: boolean;
  businessUnit: IBusinessUnit | null;
  juridicalLocation: IJuridicalLocation | null;
  representative: IPeople | null;
  currency: ICurrency | null;
  timeZone: string | null;
  publicWebsite: null;
  network: INetwork | null;
  erpSoftware: string | null;
  legacyId: number | null;
}

interface ILocationCapability {
  "@type": string;
  "@id": string;
  sso: boolean;
  factory: boolean;
  warehouse: boolean;
  sparePartsHub: boolean;
  serviceHub: boolean;
  headQuarter: boolean;
}

interface ILocationContact {
  "@type": string;
  "@id": string;
  telephone: string | null;
  fax: string | null;
  sparePartsEmail: string | null;
  partsCustomerSupportEmail: string | null;
  sparePartsTelephone: string | null;
  sparePartsFax: string | null;
  serviceHubEmail: string | null;
  serviceHubTelephone: string | null;
}

interface ILocationState {
  "@type": string;
  "@id": string;
  public: boolean;
  hidden: boolean;
  disabled: boolean;
}

interface ICurrency {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  legacyId: number | null;
}

interface INetwork {
  "@id": string;
  "@type": string;
  name: string;
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

interface IBusinessUnit {
  "@id": string;
  "@type": string;
  legacyId: number | null;
  name: string;
  domain: string | null;
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

interface IJuridicalLocation {
  "@id": string;
  "@type": string;
  name: string;
  legacyId: number | null;
}
