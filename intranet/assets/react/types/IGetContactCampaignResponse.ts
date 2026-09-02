import { IExtranetUser } from "./IExtranetUser";

export type IGetContactCampaignResponse = IContactCampaign;

export interface IContactCampaign {
  "@context": string;
  "@id": string;
  "@type": string;
  name: string;
  startedAt: string;
  endedAt: string;
  status: string;
  description: string;
  owner: IOwner;
  createdAt: string;
  contacts: Array<IExtranetUser>;
  businessUnits: Array<IBusinessUnits>;
  id: number;
}

interface IOwner {
  "@id": string;
  "@type": string;
  username: string;
  email: string;
  photo?: string | null;
  firstname: string;
  lastname: string;
}

interface IBusinessUnits {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  domain: string;
  representative: IRepresentative;
  location: ILocation;
  region: string;
}

interface IRepresentative {
  "@id": string;
  "@type": string;
  username: string;
  email: string;
  photo: null;
  firstname: string;
  lastname: string;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  company: string;
  erp: number;
}
