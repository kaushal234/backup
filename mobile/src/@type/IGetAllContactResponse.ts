import { IHydraCollection } from "./IHydraCollection";

export type IGetAllContactResponse = IHydraCollection<IExtranetUser>;

export interface IExtranetUser {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  disabled: boolean;
  extranetUserProfile: IExtranetUserProfile | null;
  phones: Array<unknown>;
  hidden: boolean;
  passwordUpdatedAt: string | null;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
  passwordExpirationDate: string | null;
  asBuyer?: boolean;
  asUser?: boolean;
  asMaintainer?: boolean;
  extranetUserAcls?: Array<IExtranetUserAcl>;
}

interface IExtranetUserProfile {
  "@id": string;
  "@type": string;
  customer: ICustomer | null;
  erpLocation: ILocation | null;
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

interface IExtranetUserAcl {
  "@id": string;
  "@type": string;
  crt: ICustomerRelationshipTeam;
  extranetUserGroup: IExtranetUserGroup;
  legacyId: number | null;
}

interface ICustomerRelationshipTeam {
  "@id": string;
  "@type": string;
  erpLocation: ILocation | null;
  legacyId: number | null;
}

interface IExtranetUserGroup {
  "@id": string;
  "@type": string;
  name: string;
  public: boolean;
}
