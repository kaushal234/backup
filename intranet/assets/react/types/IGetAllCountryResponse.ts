import { IHydraCollection } from "./IHydraCollection";

export type IGetAllCountryResponse = IHydraCollection<ICountry>;

interface ICountry {
  "@id": string;
  "@type": string;
  id: number;
  name: string | null;
  isoCode2: string;
  phoneCode: string | null;
}
