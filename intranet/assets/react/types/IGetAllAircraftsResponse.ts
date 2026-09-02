import { IHydraCollection } from "./IHydraCollection";

export type IGetAllAircraftsResponse = IHydraCollection<IAircraft>;
export type IGetAircraftByIdResponse = IAircraft;

export interface IAircraft {
  "@id": string;
  "@type": string;
  name: string;
  id: number;
  manufacturer: IAircraftManufacturer;
}

export interface IAircraftManufacturer {
  "@id": string;
  "@type": string;
  id: string;
  name: string;
}
