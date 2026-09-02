export type IGetItemMonologisticResponse = IItemMonologistic;

export interface IItemMonologistic {
  "@context": string;
  "@id": string;
  "@type": string;
  itemCode: string;
  description: string;
  unitOfMeasure: string | null;
}
