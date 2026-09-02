import { IHydraCollection } from "./IHydraCollection";

export type IGetAllDivisionResponse = IHydraCollection<IDivision>;

export interface IDivision {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
  legacyId: number | null;
}
