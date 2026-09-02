import { ICategory } from "./IGetAllContractCategoryResponse";

export type IGetContractSubCategoryByIdResponse = ISubCategory;

export interface ISubCategory {
  "@context": string;
  "@id": string;
  "@type": string;
  name: string;
  displayedName: string;
  category?: ICategory;
  id: number;
}
