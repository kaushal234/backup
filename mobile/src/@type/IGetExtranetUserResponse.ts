export type IGetExtranetUserResponse = IExtranetUser;

export interface IExtranetUser {
  "@context": string;
  "@id": string;
  "@type": string;
  address: IAddress;
  username: string | null;
  gender: string | null;
  email: string;
  disabled: boolean;
  extranetUserProfile: IExtranetUserProfile | null;
  phones: Array<IPhone>;
  hidden: boolean;
  passwordUpdatedAt: string | null;
  updatedAt: string | null;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
  createdAt: string;
  disabledAt: null;
  lastLogin: null;
  passwordExpirationDate: string | null;
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

interface IExtranetUserProfile {
  "@id": string;
  "@type": string;
  type: string | null;
  division: string | null;
  shippingAddress: IAddressWithCountry;
  legacyShippingAddress: string;
  customer: ICustomer | null;
  customerCarrierName: string | null;
  shippingAccountNumber: string | null;
  requestorNumber: string | null;
  employeeNumber: string | null;
  erpLocation: ILocation | null;
  sequenceIds: Array<string>;
  archived: boolean;
  airport: unknown | null;
  legacyId: number | null;
  jobTitle: string | null;
  department: string | null;
  language: string | null;
  country: unknown | null;
  note: string | null;
  companyName: string | null;
  counter: number;
  legacyAddress: unknown | null;
  id: number;
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

interface ICustomer {
  "@id": string;
  "@type": string;
  name: string;
  status: string;
  legacyId: number | null;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
  legacyId: number | null;
}

interface IPhone {
  "@id": string;
  "@type": string;
  type: string | null;
  number: string | null;
}
