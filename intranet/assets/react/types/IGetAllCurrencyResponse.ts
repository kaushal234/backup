import { IHydraCollection } from "./IHydraCollection";

export type IGetAllCurrencyResponse = IHydraCollection<ICurrency>;

export interface ICurrency {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  legacyId: number | null;
}
