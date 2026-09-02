import { IHydraCollection } from "./IHydraCollection";

export type IGetAllPremiseResponse = IHydraCollection<IPremise>;

export interface IPremise {
  "@id": string;
  "@type": string;
  name: string;
  description: string;
  address: IAddressWithCountry;
  latitude: number | null;
  longitude: number | null;
  supportTeam: unknown | null;
  id: number;
  tags: Array<IPremiseTag>;
  count: number | null;
}

interface IAddressWithCountry {
  "@type": string;
  "@id": string;
  country: string | null;
  street1: string | null;
  street2: string | null;
  postalCode: string | null;
  city: string | null;
  town: string | null;
  state: string | null;
}

interface IPremiseTag {
  "@id": string;
  "@type": string;
  name: string;
}
