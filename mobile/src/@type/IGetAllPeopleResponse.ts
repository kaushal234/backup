import { IHydraCollection } from "./IHydraCollection";

export type IGetAllPeopleResponse = IHydraCollection<IPeople>;

interface IPeople {
  "@id": string;
  "@type": string;
  nickname: string | null;
  jobTitle: string | null;
  businessUnit: IBusinessUnit | null;
  legalEntity: IBusinessUnit | null;
  username: string | null;
  hidden: boolean;
  disabled: boolean;
  passwordUpdatedAt: string | null;
  email: string;
  legacyId: number | null;
  photo: unknown | null;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
  passwordExpirationDate: string | null;
}

interface IBusinessUnit {
  "@id": string;
  "@type": string;
  legacyId: number | null;
  name: string;
}
