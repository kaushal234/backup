import { IHydraCollection } from "./IHydraCollection";

export type IGetAllEquipmentRecordResponse = IHydraCollection<IEquipmentRecord>;

export interface IEquipmentRecord {
  "@id": string;
  "@type": string;
  id: number;
  serialNumber: string;
  buyer: ICustomer | null;
  endUser: ICustomer | null;
  maintainer: ICustomer | null;
  customerSerialNumber: string | null;
  model: string | null;
  type: string | null;
  product: IProduct | null;
  location: string | null;
  airport: IAirport | null;
  dateShipped: string | null;
  dateCommissioned: string | null;
  manufacturerLocation: ILocation | null;
  salesOrganisation: ILocation | null;
  salesOrganisationService: ILocation | null;
  hourMeter: number | null;
  legacyId: number | null;
}

interface ICustomer {
  "@id": string;
  "@type": string;
  name: string;
  legacyId: number | null;
}

interface IProduct {
  "@id": string;
  "@type": string;
  family: IProductFamily;
  legacyId: number | null;
}

interface IProductFamily {
  "@id": string;
  "@type": string;
  productType: IProductType | null;
  legacyId: number | null;
}

interface IProductType {
  "@id": string;
  "@type": string;
  englishName: string | null;
  legacyId: number | null;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
  legacyId: number | null;
}

interface IAirport {
  "@id": string;
  "@type": string;
  code: string;
  latitude: number | null;
  longitude: number | null;
  legacyId: number | null;
  type: string;
  id: number;
  cityCode3: string | null;
  cityName: string;
  state: string | null;
  country: ICountry | null;
  name: string | null;
  source: string;
}

interface ICountry {
  "@id": string;
  "@type": string;
  name: string | null;
  isoCode2: string;
  legacyId: number | null;
}
