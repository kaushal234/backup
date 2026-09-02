import { IHydraCollection } from "./IHydraCollection";

export type IGetAllContractCategoryResponse = IHydraCollection<ICategory>;

export interface ICategory {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
}
