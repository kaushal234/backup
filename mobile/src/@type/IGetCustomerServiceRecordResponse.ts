export type IGetCustomerServiceRecordResponse = ICustomerServiceRecord &
  (Record<string, never> | ITechnicianOnCallCustomerServiceRecord);

interface ITechnicianOnCallCustomerServiceRecord {
  tocLegacyId?: number | null;
  technicianOnCall?: ITechnicianOnCall;
}

export interface ICustomerServiceRecord {
  "@context": string;
  "@id": string;
  "@type": string;
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
  contact: IExtranetUser | null;
  interventions: Array<unknown>;
  legacyId: number | null;
  status: string;
  id: number;
  files: Array<ICustomerServiceRecordFile>;
  hourMeterTransactions: Array<ICustomerServiceRecordHourMeterTransaction>;
  openIntervention: IIntervention | null;
  toContinue: boolean;
  close: boolean;
  closed: boolean;
  type: string;
  availableStatus?: Array<string>;
  detail?: string;
  answerSurveyCustomerServiceRecords?: Array<IAnswerSurveyCustomerServiceRecord>;
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

interface IIntervention {
  "@id": string;
  "@type": string;
  plannedBy: unknown | null;
  plannedAt: string;
  leader: IPeople;
  operators: Array<IPeople>;
  status: string;
}

export interface ICustomerServiceRecordFile {
  "@context": string;
  "@id": string;
  "@type": string;
  public: boolean;
  id: number;
  filePath: string;
  poster: IPeople | null;
  createdAt: string;
  description: string | null;
  sha: string;
  mimeType: string;
  extension: string;
  size: number;
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
  equipmentRecord: IEquipmentRecord | null;
  deletedAt: string | null;
  createdBy: IPeople | null;
  serialNumber: string | null;
  assignee: IPeople | null;
  errorCodes: string | null;
  unitOperationalStatus: string | null;
  technicianOnCallType: string;
  serviceActivity: string;
  indiceFactor: string;
  symptoms: string | null;
  rootCause: string | null;
  solution: string | null;
  airport: IAirport;
  salesOrganisationService: ILocation;
  customerServiceRecords: Array<string>;
  customer: ICustomer;
  thirdPartyName: string | null;
  thirdPartyRef: string | null;
  hourMeter: unknown;
  id: number | null;
  tags: Array<string>;
  mainContact: IExtranetUser | null;
  contacts: Array<IExtranetUser>;
  files: Array<unknown> | null;
  parts: Array<ITechnicianOnCallPart>;
  sparePartsRequests: Array<ITocSparePartsRequest>;
  hourMeterTransactions: Array<unknown>;
  legacyId: number | null;
  mainFile: unknown | null;
  openDays: number;
  openDaysStatus: "ON_TIME" | "OUTDATED";
  factoryFlag: boolean;
  isOpen: boolean;
  openCustomerServiceRecords: boolean;
  currentCustomerServiceRecord: string;
  numberOfOpenCustomerServiceRecords: number;
  thirdPartyHours?: number | null;
  thirdPartyJobDescription?: string | null;
}

interface ICustomerServiceRecordHourMeterTransaction {
  "@id": string;
  "@type": string;
  hourMeter: number;
  legacyId: number | null;
}

export interface IExtranetUser {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  disabled: boolean;
  extranetUserProfile: IExtranetUserProfile | null;
  phones: Array<IPhone>;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
}

interface IPhone {
  "@id": string;
  "@type": string;
  type: string;
  number: string;
}

interface IExtranetUserProfile {
  "@id": string;
  "@type": string;
  legacyId: number | null;
}

export interface IAnswerSurveyCustomerServiceRecord {
  "@id": string;
  "@type": string;
  commissioningCustomerServiceRecord: string;
  comment: string;
  questionSurveyCustomerServiceRecord: IQuestionSurveyCustomerServiceRecord;
  createdAt: string;
  answer: string | null;
  legacyId: number | null;
}

interface IQuestionSurveyCustomerServiceRecord {
  "@id": string;
  "@type": string;
  name: string;
  type: string;
}

export interface ITechnicianOnCallPart {
  "@id": string;
  "@type": string;
  partNumber: string | null;
  vendorPartNumber: string | null;
  description: string;
  quantity: number;
  createdAt: string;
  deletedAt: string | null;
  createdBy: IPeople;
  defective: boolean;
  replacement: string | null;
  comment: string | null;
  id: number | null;
}

export interface ITocSparePartsRequest {
  "@id": string;
  "@type": string;
  poster: IPeople;
  notes: string | null;
  deliveryNotes: string | null;
  deliveryAddress: unknown;
  equipmentRecords: unknown;
  parts: Array<ISparePartsRequestPart>;
  sparePartsRequestFiles: unknown;
  id: number;
  deletedParts: Array<unknown>;
  proofOfDelivery: null;
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
