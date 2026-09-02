import { IHydraCollection } from "./IHydraCollection";

export type IGetAllPeopleSearchResponse = IHydraCollection<IPeople>;

interface IPeople {
  "@id": string;
  "@type": string;
  jobTitle: string | null;
  email: string;
  firstname: string | null;
  lastname: string | null;
  id: number | null;
}
