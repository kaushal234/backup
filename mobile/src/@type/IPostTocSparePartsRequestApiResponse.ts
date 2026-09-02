export type IPostTocSparePartsRequestApiResponse = ITocSparePartsRequest;

interface ITocSparePartsRequest {
  "@context": string;
  "@id": string;
  "@type": string;
  tocId: number | null;
  technicianOnCall: ITechnicianOnCall;
  createdAt: string;
  poster: IPeople;
  activity: string;
  type: string;
  sph: ILocation;
  sso: ILocation;
  factory: ILocation;
  erpLocation: ILocation;
  shippingDate: null;
  estimatedShippingDate: null;
  notes: null;
  salesOrder: null;
  customer: ICustomer;
  airport: IAirport | null;
  deliveryNotes: string | null;
  status: string;
  legacyId: number | null;
  deliveryAddress: ISparePartsRequestDeliveryAddress;
  equipmentRecords: Array<IEquipmentRecord>;
  parts: Array<ISparePartsRequestPart>;
  sparePartsRequestFiles: Array<unknown>;
  id: number;
  deletedParts: Array<unknown>;
  proofOfDelivery: null;
}

interface ITechnicianOnCall {
  "@id": string;
  "@type": string;
  confidential: boolean;
  title: string;
  originalTitle: string | null;
  description: string;
  originalDescription: string | null;
  status: string;
  createdAt: string;
  updatedAt: string | null;
  createdBy: IPeople | null;
  equipmentRecord: IEquipmentRecord | null;
  assignee: IPeople | null;
  unitOperationalStatus: string | null;
  technicianOnCallType: string;
  serviceActivity: string;
  indiceFactor: string;
  airport: IAirport;
  salesOrganisationService: ILocation;
  factoryFlag: boolean;
  customer: ICustomer;
  id: number | null;
  tags: Array<null>;
  mainContact: IExtranetUser;
  legacyId: number | null;
  mainFile: null;
  openDays: number;
  daysWithoutActivity: number;
  daysWithoutActivityStatus: string;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
  legacyId: number | null;
}

interface ICustomer {
  "@id": string;
  "@type": string;
  name: string;
  status: string;
  legacyId: number | null;
}

interface IAirport {
  "@id": string;
  "@type": string;
  code: string;
  latitude: number | null;
  longitude: number | null;
  legacyId: number | null;
  cityName: string;
}

interface ISparePartsRequestDeliveryAddress {
  "@id": string;
  "@type": string;
  contact: IExtranetUser | null;
  firstname: string;
  lastname: string;
  company: string | null;
  phone: string;
  address: IAddressWithCountry;
  airport: IAirport | null;
  id: number;
}

interface IEquipmentRecord {
  "@id": string;
  "@type": string;
  id: number;
  serialNumber: string;
  buyer: ICustomer | null;
  endUser: ICustomer | null;
  maintainer: ICustomer | null;

  customerSerialNumber: "accusamus";
  model: string | null;
  type: string | null;
  product: IProduct | null;
  location: null;
  dateShipped: null;
  dateCommissioned: null;
  manufacturerLocation: ILocation | null;
  salesOrganisation: ILocation | null;
  salesOrganisationService: ILocation | null;
  hourMeter: number | null;
  legacyId: number | null;
}

interface ISparePartsRequestPart {
  "@id": string;
  "@type": string;
  comment: string | null;
  shippingOrigin: unknown | null;
  plannedDeliveryDate: string | null;
  trackings: Array<unknown>;
  createdAt: string;
  createdBy: IPeople | null;
  partNumber: string;
  description: string;
  quantity: number;
  unitOfMeasure: string | null;
  deletedAt: string | null;
  deletedBy: IPeople | null;
  legacyId: number | null;
  id: number;
}

interface IExtranetUser {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
  passwordExpirationDate: string | null;
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
