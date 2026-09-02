import { IProductType } from "./IGetAllProductTypesResponse";

export type IGetProductFamilyByIdResponse = IProductFamily;

export interface IProductFamily {
  "@id": string;
  "@type": string;
  name: string;
  productType: IProductType;
  tags: Array<ITag>;
  manufacturingFactories: Array<IManufacturingFactory>;
  publicForTLD: boolean;
  publicForAerospecialties: boolean;
  publicForSAS: boolean;
  hidden: boolean;
  englishDescription: string | null;
  frenchDescription: string | null;
  spanishDescription: string | null;
  portugueseDescription: string | null;
  chineseDescription: string | null;
  japaneseDescription: string | null;
  germanDescription: string | null;
  russianDescription: string | null;
  id: number;
}

interface ITag {
  "@id": string;
  "@type": string;
  name: string;
}

interface IManufacturingFactory {
  "@id": string;
  "@type": string;
  name: string;
}
