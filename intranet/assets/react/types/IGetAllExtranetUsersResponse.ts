import { IHydraCollection } from "./IHydraCollection";
import { IPhone } from "../api/postExtranetUserWithCrt";

export type IGetAllExtranetUsersResponse = IHydraCollection<IExtranetUser>;

export interface IExtranetUser {
  "@id": string;
  "@type": string;
  username: string;
  email: string;
  disabled: false;
  extranetUserProfile: IExtranetUserProfile | null;
  phones: Array<IPhone>;
  hidden: boolean;
  passwordUpdatedAt: string;
  legacyId: number | null;
  firstname: string;
  lastname: string;
  id: number | null;
  passwordExpirationDate: string;
  asBuyer?: boolean;
  asUser?: boolean;
  asMaintainer?: boolean;
}

interface IExtranetUserProfile {
  "@id": string;
  "@type": string;
  customer: ICustomer;
  erpLocation: ILocation;
  legacyId: number | null;
  department: string | null;
  id: number | null;
}

interface ICustomer {
  "@id": string;
  "@type": string;
  name: string;
  status: string;
  legacyId: number | null;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
  legacyId: number | null;
}
