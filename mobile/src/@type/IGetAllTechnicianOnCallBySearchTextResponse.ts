import { IHydraCollection } from "./IHydraCollection";

export type IGetAllTechnicianOnCallBySearchTextResponse = {
  "@context": IContext;
  "@type": string;
  "@id": string;
  logIri: string;
  results: IHydraCollection<ISearchOutput>;
};

export interface IContext {
  "@vocab": string;
  hydra: string;
  logIri: string;
  results: string;
}

export interface ISearchOutput {
  "@type": string;
  "@id": string;
  id: number;
  description: string | null;
  link: string | null;
  module: string | null;
}
