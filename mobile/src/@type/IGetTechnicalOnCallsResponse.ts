export type IGetTechnicalOnCallsResponse = ITechnicianOnCall;

export interface ITechnicianOnCall {
  "@context": string;
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
  serialNumber: string | null;
  assignee: IPeople | null;
  technician?: IPeople | null;
  errorCodes: string | null;
  unitOperationalStatus: IUnitOperationalStatus | null;
  technicianOnCallType: ITechnicianOnCallType;
  serviceActivity: IServiceActivity;
  indiceFactor: string;
  symptoms: string | null;
  originalSymptoms: string | null;
  rootCause: string | null;
  originalRootCause: string | null;
  solution: string | null;
  originalSolution: string | null;
  airport: IAirport;
  salesOrganisationService: ILocation;
  customerServiceRecords: Array<ITechnicianOnCallCustomerServiceRecord>;
  factoryFlag: boolean;
  customer: ICustomer;
  thirdPartyName: string | null;
  thirdPartyRef: string | null;
  hourMeter: unknown;
  id: number | null;
  tags: Array<ITechnicianOnCallTag>;
  mainContact: IExtranetUser | null;
  contacts: Array<IExtranetUser>;
  files: Array<ITechnicianOnCallFile> | null;
  parts: Array<ITechnicianOnCallPart>;
  sparePartsRequests: Array<ITocSparePartsRequest>;
  hourMeterTransactions: Array<unknown>;
  legacyId: number | null;
  isOpen: boolean;
  openCustomerServiceRecords: boolean;
  currentCustomerServiceRecord: ITechnicianOnCallCustomerServiceRecord;
  numberOfOpenCustomerServiceRecords: number;
  mainFile: ITechnicianOnCallMainFile | null;
  openDays: number;
  daysWithoutActivity: number;
  daysWithoutActivityStatus: "ON_TIME" | "OUTDATED";
  availableStatus?: Array<string>;
  serviceBulletins: Array<IServiceBulletin>;
  warrantyInformation: string | null;
  warrantyLegacyId: number | null;
  statusBlockerMessage?: Array<IStatusBlockerMessage>;
  thirdPartyHours?: number | null;
  thirdPartyJobDescription?: string | null;
  links?: ITechnicianOnCallLinks;
  defectiveParts: Array<ITechnicianOnCallDefectivePart>;
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
  parentEquipment: IEquipmentRecord | null;
  childrenEquipments: Array<IEquipmentRecord>;
  estimatedGreenTagDate: string | null;
  dateCommissioned: string | null;
  manufacturerLocation: ILocation | null;
  salesOrganisation: ILocation | null;
  salesOrganisationService: ILocation | null;
  greenTagDate: string | null;
  hourMeter: number | null;
  legacyId: number | null;
  emissionRating: IEmissionRating | null;
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

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
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

export interface ITechnicianOnCallFile {
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

export interface ITechnicianOnCallMainFile {
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

interface IUnitOperationalStatus {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  description: string;
}

interface ITechnicianOnCallTag {
  "@id": string;
  "@type": string;
  name: string;
}

interface ITechnicianOnCallCustomerServiceRecord {
  "@id": string;
  "@type": string;
  tocLegacyId: number | null;
  technicianOnCall: string;
  createdAt: string;
  completedAt: string | null;
  plannedAt: string | null;
  title: string;
  description: string | null;
  createdBy: IPeople | null;
  equipmentRecord: IEquipmentRecord;
  interventions: Array<unknown>;
  legacyId: number | null;
  airport: IAirport;
  status: string;
  id: number;
  files: Array<unknown>;
  hourMeterTransactions: Array<unknown>;
  openIntervention: unknown | null;
  toContinue: boolean;
  close: boolean;
  closed: boolean;
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
  tocId: number | null;
  technicianOnCall: string;
  createdAt: string;
  activity: string;
  type: string;
  sph: ILocation;
  sso: ILocation;
  factory: ILocation;
  erpLocation: ILocation;
  shippingDate: string | null;
  estimatedShippingDate: string | null;
  salesOrder: string | null;
  airport: IAirport | null;
  status: string;
  legacyId: number | null;
  parts: Array<ISparePartsRequestPart>;
  id: number;
  deletedParts: Array<ISparePartsRequestPart>;
}

interface ISparePartsRequestPart {
  "@id": string;
  "@type": string;
  comment: string | null;
  shippingOrigin: unknown | null;
  plannedDeliveryDate: string | null;
  trackings: Array<IPartTracking>;
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

export interface IExtranetUser {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  disabled: false;
  extranetUserProfile: IExtranetUserProfile | null;
  phones: Array<IPhone>;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
  asBuyer?: boolean;
  asMaintainer?: boolean;
  asUser?: boolean;
}

interface IExtranetUserProfile {
  "@id": string;
  "@type": string;
  legacyId: number | null;
}

export interface IPhone {
  "@id": string;
  "@type": string;
  type: string;
  number: string;
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

interface IServiceBulletin {
  status: string | null;
  title: string;
  type: string | null;
  description: string | null;
  confidential: string;
  createdAt: string;
  id: number;
}

interface IEmissionRating {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  legacyId: number | null;
}

interface IStatusBlockerMessage {
  from: string;
  to: string;
  message: string;
}

export interface ITechnicianOnCallLinks {
  WC?: Array<IWarrantyClaimLegacy>;
  PDC?: Array<IProductDemeritClaimLegacy>;
}

export interface IWarrantyClaimLegacy {
  id?: number | null;
  equipmentRecord?: unknown;
  description?: string | null;
  unitOperationalStatus?: string | null;
  status?: string | null;
  createdBy?: string | null;
  createdAt?: string | null;
  details?: string | null;
  customerName?: string | null;
  location?: string | null;
  type?: string | null;
  model?: string | null;
  factory?: string | null;
  salesOrganisation?: string | null;
  serialNumber?: string | null;
  hours?: number | null;
}

export interface IProductDemeritClaimLegacy {
  id?: number | null;
  factory?: null;
  productType?: string | null;
  productModel?: string | null;
  lastStatus?: string | null;
  status?: string | null;
  openingDate?: string | null;
  closingDate?: null;
  suspensionDate?: null;
  daysSuspended?: 0;
  shortDescription?: string | null;
  description?: string | null;
  containmentAction?: string | null;
  rootCause?: string | null;
  correctiveAction?: string | null;
  preventiveAction?: string | null;
  resolution?: string | null;
  rejectionReason?: string | null;
  importanceFactor?: number | null;
  finalFocusWeight?: number | null;
  poster?: null;
  initiator?: null;
  assignee?: null;
  verificationDescription?: null;
  statusUpdatedAt?: string | null;
  involvesIbs?: boolean;
  involvesIhs?: boolean;
  involvesLink?: boolean;
  readyToClose?: boolean;
}

interface ITechnicianOnCallDefectivePart {
  "@id": string;
  "@type": string;
  partNumber: string;
  description: string;
  quantity: number;
  createdAt: string;
  deletedAt: string | null;
  createdBy: IPeople;
  id: number | null;
}

export interface IPartTracking {
  "@type": string;
  "@id": string;
  trackingNumber: string;
  carrier: string;
  shippedQuantity: number;
  unitOfMeasure: string;
  trackingLink: string | null;
}
