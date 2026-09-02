import { IHydraCollection } from "./IHydraCollection";

export type IGetAllAirportResponse = IHydraCollection<IAirport>;

interface IAirport {
  "@id": string;
  "@type": string;
  code: string;
  latitude: number | null;
  longitude: number | null;
  legacyId: number | null;
  type: string;
  id: number;
  cityCode3: string | null;
  cityName: string;
  state: string | null;
  country: ICountry | null;
  name: string | null;
  source: string;
}

interface ICountry {
  "@id": string;
  "@type": string;
  name: string | null;
  isoCode2: string;
  legacyId: number | null;
}
