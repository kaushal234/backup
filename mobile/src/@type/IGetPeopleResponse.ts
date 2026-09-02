export type IGetPeopleResponse = IPeople;

export interface IPeople {
  "@context": string;
  "@id": string;
  "@type": string;
  closestAirport: unknown | null;
  alternateEmail: string | null;
  erpIdentifier: string | null;
  nickname: string | null;
  jobTitle: string | null;
  phones: Array<IPhone>;
  address: IAddressWithCountry;
  erpLogin: string | null;
  windowsLogin: string | null;
  businessUnit: IBusinessUnit | null;
  legalEntity: IBusinessUnit | null;
  position: IPosition | null;
  positionCategory: string | null;
  department: IDepartment | null;
  supervisor: IPeople | null;
  locale: string | null;
  premise: IPremise | null;
  username: string | null;
  hidden: boolean;
  disabled: boolean;
  passwordUpdatedAt: string | null;
  email: string;
  gender: string | null;
  updatedAt: string | null;
  legacyId: number | null;
  photo: IPeopleFile | null;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
  createdAt: string;
  disabledAt: null;
  lastLogin: null;
  passwordExpirationDate: string | null;
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

interface ILocation {
  "@id": string;
  "@type": string;
  address: IAddressWithCountry;
  currency: ICurrency | null;
  legacyId: number | null;
}

interface ICurrency {
  "@id": string;
  "@type": string;
  name: string;
  legacyId: number | null;
}

interface IDepartment {
  "@id": string;
  "@type": string;
  name: string;
  legacyId: number | null;
}

interface IPeopleFile {
  "@id": string;
  "@type": string;
  id: number;
  filePath: string;
  createdAt: string;
  mimeType: string;
}

interface IPhone {
  "@id": string;
  "@type": string;
  type: string | null;
  number: string | null;
}

interface IPremise {
  "@id": string;
  "@type": string;
  name: string;
  description: string;
  archived: boolean;
  archivedAt: string | null;
  address: IAddressWithCountry;
  airport: unknown | null;
  id: string;
  tags: Array<IPremiseTag>;
  count: number | null;
}

interface IPremiseTag {
  "@id": string;
  "@type": string;
  name: string;
}
