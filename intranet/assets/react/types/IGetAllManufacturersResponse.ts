import { IHydraCollection } from "./IHydraCollection";

export type IGetAllManufacturersResponse =
  IHydraCollection<IAircraftManufacturer>;

export interface IAircraftManufacturer {
  "@id": string;
  "@type": string;
  id: string;
  name: string;
}
