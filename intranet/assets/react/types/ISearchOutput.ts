import { IHydraCollection } from "./IHydraCollection";

export interface ISearchOutput<T> {
  "@context": IContext;
  "@type": string;
  "@id": string;
  logIri: string | null;
  results: IHydraCollection<T>;
}

interface IContext {
  "@vocab": string;
  hydra: string;
  logIri: string;
  results: string;
}
