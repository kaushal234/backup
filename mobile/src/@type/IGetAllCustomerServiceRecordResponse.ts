import { IHydraCollection } from "./IHydraCollection";

export type IGetAllCustomerServiceRequestResponse =
  IHydraCollection<ICustomerServiceRecord>;

export interface ICustomerServiceRecord {
  "@id": string;
  "@type": string;
  createdAt: string;
  completedAt: string | null;
  plannedAt: string | null;
  title: string;
  description: string | null;
  createdBy: IPeople | null;
  equipmentRecord: IEquipmentRecord;
  airport: IAirport | null;
  interventions: Array<unknown>;
  legacyId: number | null;
  status: string;
  id: number;
  files: Array<unknown>;
  openIntervention: boolean | null;
  toContinue: boolean;
  close: boolean;
  closed: boolean;
  type: string;
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

interface IEquipmentRecord {
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
