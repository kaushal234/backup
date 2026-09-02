import { IHydraCollection } from "./IHydraCollection";

export type IGetAllProductsResponse = IHydraCollection<IProduct>;

export interface IProduct {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
}
