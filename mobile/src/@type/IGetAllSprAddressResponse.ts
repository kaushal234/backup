import { IHydraCollection } from "./IHydraCollection";

export type IGetAllSprAddressResponse =
  IHydraCollection<ISparePartsRequestDeliveryAddress>;

export interface ISparePartsRequestDeliveryAddress {
  "@id": string;
  "@type": string;
  contact: IExtranetUser | null;
  firstname: string;
  lastname: string;
  company: string | null;
  phone: string;
  address: IAddressWithCountry;
  airport: IAirport | null;
  createdAt: string;
  lastUsedAt: string | null;
  poster: IPeople;
  id: number;
}

interface IExtranetUser {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  disabled: boolean;
  extranetUserProfile: IExtranetUserProfile | null;
  phones: Array<unknown>;
  hidden: boolean;
  passwordUpdatedAt: string | null;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
  passwordExpirationDate: string | null;
}

interface IExtranetUserProfile {
  "@id": string;
  "@type": string;
  customer: ICustomer | null;
  erpLocation: ILocation | null;
  department: string | null;
  id: number;
}

interface ICustomer {
  "@id": string;
  "@type": string;
  name: string;
  status: string;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
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

interface IAirport {
  "@id": string;
  "@type": string;
  code: string;
  latitude: number | null;
  longitude: number | null;
  cityName: string;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  hidden: boolean;
  disabled: boolean;
  passwordUpdatedAt: string | null;
  email: string;
  photo: string | null;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
  passwordExpirationDate: string | null;
}
