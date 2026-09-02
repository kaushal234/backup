import { IHydraCollection } from "./IHydraCollection";

export type IGetAllContractSubCategoryResponse = IHydraCollection<ISubCategory>;

export interface ISubCategory {
  "@id": string;
  "@type": string;
  name: string;
  category: string;
  id: number;
}
