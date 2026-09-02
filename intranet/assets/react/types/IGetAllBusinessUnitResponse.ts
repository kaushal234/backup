import { IHydraCollection } from "./IHydraCollection";

export type IGetAllBusinessUnitResponse = IHydraCollection<IBusinessUnit>;

export interface IBusinessUnit {
  "@id": string;
  "@type": string;
  legacyId: number | null;
  id: number;
  name: string;
  domain: string | null;
  region: IRegion | null;
}

interface IRegion {
  "@id": string;
  "@type": string;
  name: string;
  legacyId: number | null;
}
