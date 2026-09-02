export type IGetEquipmentRecordResponse = IEquipmentRecord;

export interface IEquipmentRecord {
  "@context": string;
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
  airport: IAirport | null;
  location: string | null;
  parentEquipment: IEquipmentRecord | null;
  childrenEquipments: Array<IEquipmentRecord>;
  dateShipped: string | null;
  estimatedGreenTagDate: string | null;
  odpComment: string | null;
  factoryComment: string | null;
  dateCommissioned: string | null;
  manufacturerLocation: ILocation | null;
  salesOrganisation: ILocation | null;
  salesOrganisationService: ILocation | null;
  publishable: boolean | null;
  serials: Array<IEquipmentSerial>;
  manuals: Array<IManual>;
  greenTagDate: string | null;
  projectNumber: string | null;
  hourMeter: number | null;
  legacyId: number | null;
  length: number | null;
  width: number | null;
  height: number | null;
  weight: number | null;
  optionsDescription: string | null;
  emissionRating: IEmissionRating | null;
  state: string;
  status: string | null;
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

interface IAirport {
  "@id": string;
  "@type": string;
  code: string;
  latitude: number;
  longitude: number;
  legacyId: number | null;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
  legacyId: number | null;
}

interface IEquipmentSerial {
  "@id": string;
  "@type": string;
  component: IEquipmentSerialComponent | null;
  model: string | null;
  serial: string | null;
  brand: string | null;
  createdAt: string;
  createdBy: IPeople | null;
  id: number;
  legacyId: number | null;
}

interface IEquipmentSerialComponent {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
}

export interface IManual {
  "@id": string;
  "@type": string;
  description: string | null;
  features: string | null;
  language: string | null;
  status: string | null;
  createdAt: string | null;
  id: number | null;
  legacyId: number | null;
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

interface IEmissionRating {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  obsolete: boolean;
  legacyId: number | null;
}
