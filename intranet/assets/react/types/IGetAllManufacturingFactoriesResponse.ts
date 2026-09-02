import { IHydraCollection } from "./IHydraCollection";

export type IGetAllManufacturingFactoriesResponse =
  IHydraCollection<IManufacturingFactory>;

export interface IManufacturingFactory {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
}
