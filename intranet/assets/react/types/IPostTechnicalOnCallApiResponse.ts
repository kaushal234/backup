export type IPostTechnicianOnCallApiResponse = ITechnicianOnCall;

export interface ITechnicianOnCall {
  "@context": string;
  "@id": string;
  "@type": string;
  title: string;
  description: string;
  status: string;
  createdAt: string;
  updatedAt: string | null;
  createdBy: IPeople | null;
  equipmentRecord: IEquipmentRecord;
  assignee: IPeople | null;
  errorCodes: string | null;
  unitOperationalStatus: IUnitOperationalStatus | null;
  technicianOnCallType: ITechnicianOnCallType;
  serviceActivity: IServiceActivity;
  indiceFactor: string;
  symptoms: string | null;
  rootCause: string | null;
  solution: string | null;
  airport: IAirport;
  salesOrganisationService: ILocation;
  customerServiceRecord: unknown | null;
  id: number | null;
  tags: Array<ITechnicianOnCallTag>;
  files: Array<unknown> | null;
  mainFile: unknown | null;
  openDays: number;
  openDaysStatus: "ON_TIME" | "OUTDATED";
  "@sub_resources"?: {
    customerServiceRecord: ITechnicianOnCallCustomerServiceRecord;
  };
  mainContact: IExtranetUser | null;
  contacts: Array<IExtranetUser>;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  legacyId: number | null;
  photo: string | null;
  firstname: string | null;
  lastname: string | null;
}

interface IUnitOperationalStatus {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  description: string;
}

interface ITechnicianOnCallType {
  "@id": string;
  "@type": string;
  name: string;
  description: string;
  id: number | null;
}

interface IServiceActivity {
  "@id": string;
  "@type": string;
  name: string;
  description: string;
  id: number | null;
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

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
  legacyId: number | null;
}

interface ITechnicianOnCallTag {
  "@id": string;
  "@type": string;
  name: string;
}

interface IEquipmentRecord {
  "@id": string;
  "@type": string;
  id: number;
  serialNumber: string;
  buyer: ICustomer | null;
  endUser: ICustomer | null;
  maintainer: ICustomer | null;
  model: string | null;
  type: string | null;
  product: IProduct | null;
  airport: IAirport | null;
  location: string | null;
  parentEquipment: unknown | null;
  childrenEquipments: Array<unknown>;
  estimatedGreenTagDate: string | null;
  dateCommissioned: string | null;
  manufacturerLocation: ILocation | null;
  salesOrganisation: ILocation | null;
  salesOrganisationService: ILocation | null;
  greenTagDate: string | null;
  hourMeter: number | null;
  legacyId: number | null;
}

interface ICustomer {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  address: Array<unknown> | null;
  country: unknown | null;
  phone: string | null;
  fax: string | null;
  legacyId: number | null;
}

interface IProduct {
  "@id": string;
  "@type": string;
  id: number;
  family: IProductFamily;
  name: string;
  hidden: boolean;
  erpLocation: ILocation | null;
  financeFamily: IFinanceFamily | null;
  manufacturingFamily: IManufacturingFamily | null;
  legacyId: number | null;
}

interface IProductFamily {
  "@id": string;
  "@type": string;
  name: string;
  productType: IProductType | null;
  legacyId: number | null;
}

interface IFinanceFamily {
  "@id": string;
  "@type": string;
  name: string;
}

interface IManufacturingFamily {
  "@id": string;
  "@type": string;
  name: string;
}

interface IProductType {
  "@id": string;
  "@type": string;
  englishName: string | null;
  legacyId: number | null;
}

interface ITechnicianOnCallCustomerServiceRecord {
  "@context": string;
  "@id": string;
  "@type": string;
  tocLegacyId?: number | null;
  technicianOnCall?: ITechnicianOnCall;
  createdAt: string;
  updatedAt: string | null;
  deletedAt: null;
  completedAt: string | null;
  closedAt: null;
  plannedAt: string | null;
  title: string;
  description: string | null;
  createdBy: IPeople | null;
  equipmentRecord: IEquipmentRecord;
  airport: IAirport | null;
  contact: null;
  interventions: Array<unknown>;
  legacyId: number | null;
  status: string | number;
  id: number;
  files: Array<unknown>;
  hourMeterTransactions: [];
  openIntervention: unknown | null;
  toContinue: boolean;
  close: boolean;
  closed: boolean;
  type: string;
  detail?: string;
}

interface IExtranetUser {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
}
