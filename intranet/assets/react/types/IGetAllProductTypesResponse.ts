import { IHydraCollection } from "./IHydraCollection";

export type IGetAllProductTypesResponse = IHydraCollection<IProductType>;

export interface IProductType {
  "@id": string;
  "@type": string;
  englishName: string;
  id: number;
}
