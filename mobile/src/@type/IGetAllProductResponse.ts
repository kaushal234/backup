import { IHydraCollection } from "./IHydraCollection";

export type IGetAllProductResponse = IHydraCollection<IProduct>;

interface IProduct {
  "@id": string;
  "@type": string;
  id: number;
  family: IProductFamily;
  name: string;
  hidden: boolean;
  erpLocation: ILocation | null;
  financeFamily: IFinanceFamily | null;
  manufacturingFamily: IManufacturingFamily | null;
  legacyId: number | null;
}

interface IProductFamily {
  "@id": string;
  "@type": string;
  name: string;
  productType: IProductType | null;
  legacyId: number | null;
  id: number;
}

interface ILocation {
  "@id": string;
  "@type": string;
  name: string;
  erp: number | null;
  legacyId: number | null;
}

interface IFinanceFamily {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
}

interface IManufacturingFamily {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
}

interface IProductType {
  "@id": string;
  "@type": string;
  englishName: string | null;
  legacyId: number | null;
}
