import { IHydraCollection } from "./IHydraCollection";

export type IGetAllUnitOperationalStatusResponse =
  IHydraCollection<IUnitOperationalStatus>;

export interface IUnitOperationalStatus {
  "@id": string;
  "@type": string;
  id: number;
  name: string;
  description: string;
}
