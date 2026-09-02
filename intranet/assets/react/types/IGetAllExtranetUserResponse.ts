import { IHydraCollection } from "./IHydraCollection";

export type IGetAllExtranetUserResponse = IHydraCollection<IExtranetUser>;

interface IExtranetUser {
  "@id": string;
  "@type": string;
  legacyId: number;
  id: number;
  email: string;
}
