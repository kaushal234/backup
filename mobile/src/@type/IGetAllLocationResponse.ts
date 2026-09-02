import { IHydraCollection } from "./IHydraCollection";

export type IGetAllLocationResponse = IHydraCollection<ILocation>;

export interface ILocation {
  "@id": string;
  "@type": string;
  id: number | null;
  name: string;
  company: string;
  capability: ILocationCapability;
  contact: ILocationContact;
  state: ILocationState;
  domain: string | null;
  internalNetworkAddress: string | null;
  erp: number | null;
  erpInLN: boolean;
  currency: ICurrency | null;
  timeZone: string | null;
  network: INetwork | null;
  legacyId: number | null;
}

interface ILocationCapability {
  "@type": string;
  "@id": string;
  sso: boolean;
  factory: boolean;
  warehouse: boolean;
  sparePartsHub: boolean;
  serviceHub: boolean;
  headQuarter: boolean;
}

interface ILocationContact {
  "@type": string;
  "@id": string;
  telephone: string | null;
  fax: string | null;
  sparePartsEmail: string | null;
  partsCustomerSupportEmail: string | null;
  sparePartsTelephone: string | null;
  sparePartsFax: string | null;
  serviceHubEmail: string | null;
  serviceHubTelephone: string | null;
}

interface ILocationState {
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
  legacyId: number | null;
}

interface INetwork {
  "@id": string;
  "@type": string;
  name: string;
}
