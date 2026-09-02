import { IHydraCollection } from "./IHydraCollection";

export type IGetAllTechnicalOnCallTagResponse =
  IHydraCollection<ITechnicianOnCallTag>;

interface ITechnicianOnCallTag {
  "@id": string;
  "@type": string;
  name: string;
}
