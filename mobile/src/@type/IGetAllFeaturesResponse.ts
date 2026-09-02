import { IHydraCollection } from "./IHydraCollection";

export type IGetAllFeaturesResponse = IHydraCollection<IFeature>;

export interface IFeature {
  "@id": string;
  "@type": string;
  name: string;
}
