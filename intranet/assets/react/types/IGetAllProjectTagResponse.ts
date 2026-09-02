import { IHydraCollection } from "./IHydraCollection";

export type IGetAllProjectTagResponse = IHydraCollection<IProjectTag>;

interface IProjectTag {
  "@id": string;
  "@type": string;
  name: string;
}
