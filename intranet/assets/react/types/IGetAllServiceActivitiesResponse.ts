import { IHydraCollection } from "./IHydraCollection";

export type IGetAllServiceActivitiesResponse =
  IHydraCollection<IServiceActivity>;

interface IServiceActivity {
  "@id": string;
  "@type": string;
  name: string;
  description: string;
  id: number | null;
}
