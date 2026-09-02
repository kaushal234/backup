export type IGetDetailedUserInfoResponse = IPeople;

export interface IPeople {
  "@context": string;
  "@id": string;
  "@type": string;
  alternateEmail: string | null;
  erpIdentifier: string | null;
  nickname: string | null;
  jobTitle: string | null;
  phones: Array<unknown>;
  address: IAddressWithCountry;
  erpLogin: string | null;
  windowsLogin: string | null;
  businessUnit: IBusinessUnit | null;
  legalEntity: IBusinessUnit | null;
  position: IPosition | null;
  department: unknown | null;
  locale: string | null;
  username: string | null;
  hidden: boolean;
  disabled: boolean;
  passwordUpdatedAt: string | null;
  email: string;
  gender: string | null;
  legacyId: number | null;
  photo: string | null;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
  passwordExpirationDate: string | null;
  erp: number | null;
  locationCapabilities: ILocationCapability;
  timeZone: string | null;
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
  location: ILocation;
}

interface IPosition {
  "@id": string;
  "@type": string;
  code: string;
  description: string;
  level: IPositionLevel | null;
  legacyId: number | null;
}

interface IPositionLevel {
  "@id": string;
  "@type": string;
  label: string;
}

interface ILocationCapability {
  "@id": string;
  "@type": string;
  sso: boolean;
  factory: boolean;
  warehouse: boolean;
  sparePartsHub: boolean;
  serviceHub: boolean;
  headQuarter: boolean;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  capability: ILocationCapability;
  erp: number | null;
  currency: ICurrency | null;
  timeZone: string | null;
  legacyId: number | null;
}

interface ICurrency {
  "@id": string;
  "@type": string;
  name: string;
  legacyId: number | null;
}
