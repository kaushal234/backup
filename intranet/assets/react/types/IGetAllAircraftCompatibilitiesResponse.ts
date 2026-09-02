import { IHydraCollection } from "./IHydraCollection";
import { IProduct } from "./IGetAllProductsResponse";
import { IAircraft } from "./IGetAllAircraftsResponse";

export type IGetAllAircraftCompatibilitiesResponse =
  IHydraCollection<IAircraftCompatibility>;

export type IGetAircraftCompatibilityByIdResponse = IAircraftCompatibility;

export interface IAircraftCompatibility {
  "@id": string;
  "@type": string;
  id: number;
  products: Array<IProduct>;
  aircrafts: Array<IAircraft>;
  files: Array<IAircraftCompatibilityFile>;
}

export interface IAircraftCompatibilityFile {
  "@id": string;
  "@type": string;
  public: boolean;
  id: number | null;
  filePath: string;
  poster: IPeople;
  createdAt: string;
  description: string | null;
  sha: string;
  mimeType: string;
  extension: string;
  size: number;
  type: string;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string;
  email: string;
  firstname: string;
  lastname: string;
}
