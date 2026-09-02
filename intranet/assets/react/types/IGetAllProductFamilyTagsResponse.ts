import { IHydraCollection } from "./IHydraCollection";

export type IGetAllProductFamilyTagsResponse =
  IHydraCollection<IProductFamilyTag>;

export interface IProductFamilyTag {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
}
