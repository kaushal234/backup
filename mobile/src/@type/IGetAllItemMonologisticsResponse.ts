import { IHydraCollection } from "./IHydraCollection";

export type IGetAllItemMonologisticsResponse =
  IHydraCollection<IItemMonologistic>;

export interface IItemMonologistic {
  "@id": string;
  "@type": string;
  itemCode: string;
  description: string;
}
