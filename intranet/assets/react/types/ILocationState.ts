export interface ILocationState {
  locations: Array<unknown>;
  factories: Array<IFactory>;
  ssos?: Array<IFactory>;
}

interface IFactory {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  company: string;
  capability: ICapability;
  contact: IContact;
  state: IState;
  domain: string;
  internalNetworkAddress: string;
  erp: number;
  erpInLN: boolean;
  currency: ICurrency;
  timeZone: string;
  network: INetwork;
  legacyId: number;
}

interface ICapability {
  "@type": string;
  "@id": string;
  sso: boolean;
  factory: boolean;
  warehouse: boolean;
  sparePartsHub: boolean;
  serviceHub: boolean;
  headQuarter: boolean;
}

interface IContact {
  "@type": string;
  "@id": string;
  telephone: string;
  fax: string;
  sparePartsEmail: string;
  partsCustomerSupportEmail: string;
  sparePartsTelephone: string;
  sparePartsFax: string;
  serviceHubEmail: string;
  serviceHubTelephone: null;
}

interface IState {
  "@type": string;
  "@id": string;
  public: boolean;
  hidden: boolean;
  disabled: boolean;
}

interface ICurrency {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  legacyId: number;
}

interface INetwork {
  "@id": string;
  "@type": string;
  name: string;
}
