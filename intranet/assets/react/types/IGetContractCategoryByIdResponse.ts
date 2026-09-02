export type IGetContractCategoryByIdResponse = ICategory;

export interface ICategory {
  "@context": string;
  "@id": string;
  "@type": string;
  name: string;
  displayedName: string;
  id: number;
}
