import { IHydraCollection } from "./IHydraCollection";

export type IGetAllContractSubCategoriesResponse =
  IHydraCollection<IContractSubCategory>;

export interface IContractSubCategory {
  "@id": string;
  "@type": string;
  name: string;
  category: string;
  id: number;
}
