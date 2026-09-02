import { IHydraCollection } from "./IHydraCollection";

export type IGetAllRegionResponse = IHydraCollection<IRegion>;

export interface IRegion {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  representative: IPeople | null;
  subDivision: ISubDivision | null;
  businessUnits: Array<IBusinessUnit>;
  legacyId: number | null;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
}

interface ISubDivision {
  "@id": string;
  "@type": string;
  name: string;
  legacyId: number | null;
}

interface IBusinessUnit {
  "@id": string;
  "@type": string;
  legacyId: number | null;
  name: string;
}
