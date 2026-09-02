import { IHydraCollection } from "./IHydraCollection";

export type IGetAllContractResponse = IHydraCollection<IContract>;

export interface IContract {
  "@id": string;
  "@type": string;
  shortDescription: string;
  description: string;
  createdAt: string;
  status: string;
  startDate: string;
  signatureDate: string;
  expirationDate: string | null;
  renewalPeriod: number | null;
  renewalUnit: string | null;
  externalParty: string | null;
  internalParty: Array<string>;
  createdBy: IPeople;
  owner: IPeople;
  value: number | null;
  currency: ICurrency | null;
  subCategory: ISubCategory;
  jurisdiction: string | null;
  otherPartySignatories: Array<unknown>;
  divisions: Array<string>;
  businessUnits: Array<string>;
  regions: Array<string>;
  premises: Array<string>;
  alvestSignatories: Array<IPeople>;
  id: number;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  firstname: string | null;
  lastname: string | null;
}

interface ICurrency {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
}

export interface ISubCategory {
  "@id": string;
  "@type": string;
  name: string;
  category: ICategory;
  id: number;
}

interface ICategory {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
}
