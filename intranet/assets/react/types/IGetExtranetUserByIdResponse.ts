export type IGetExtranetUserByIdResponse = IExtranetUser;

export interface IExtranetUser {
  "@context": string;
  "@id": string;
  "@type": string;
  address: IAddress;
  username: string;
  gender: string | null;
  email: string;
  disabled: boolean;
  extranetUserProfile: IExtranetUserProfile;
  phones: Array<IPhone>;
  hidden: boolean;
  passwordUpdatedAt: string;
  updatedAt: string;
  legacyId: number;
  firstname: string;
  lastname: string;
  id: number;
  createdAt: string;
  disabledAt: string | null;
  lastLogin: string | null;
  passwordExpirationDate: string;
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
  shippingAddress: IAddress;
  legacyShippingAddress: string;
  customer: ICustomer | null;
  customerCarrierName: string | null;
  shippingAccountNumber: string | null;
  requestorNumber: string | null;
  employeeNumber: string | null;
  erpLocation: ILocation | null;
  sequenceIds: Array<number>;
  archived: boolean;
  airport: IAirport | null;
  legacyId: number;
  jobTitle: string | null;
  department: string | null;
  language: string | null;
  country: ICountry;
  note: string | null;
  companyName: string | null;
  counter: number;
  legacyAddress: null;
  id: number;
}

interface ICountry {
  "@id": string;
  "@type": "Country";
  id: number;
  name: string | null;
  isoCode2: string;
  legacyId: number;
}

interface IPhone {
  "@id": string;
  "@type": "Phone";
  type: string;
  number: string;
}

interface ICustomer {
  "@id": string;
  "@type": "Customer";
  name: string;
  status: string;
  legacyId: number;
}

interface ILocation {
  "@id": string;
  "@type": "Location";
  name: string;
  erp: number | null;
  legacyId: number;
}

interface IAirport {
  "@id": string;
  "@type": "Airport";
  code: string;
  latitude: number | null;
  longitude: number | null;
  legacyId: number;
  cityName: string;
}
