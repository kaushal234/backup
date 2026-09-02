import { IHydraCollection } from "./IHydraCollection";

export type IGetAllLocationResponse = IHydraCollection<ILocation>;

export interface ILocation {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  company: string;
  capability: ICapability;
  contact: IContact;
  state: IState;
  domain: string;
  internalNetworkAddress: null;
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
  headQuarter: false;
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
  serviceHubTelephone: string;
}

interface IState {
  "@type": string;
  "@id": string;
  public: boolean;
  hidden: boolean;
  disabled: true;
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
