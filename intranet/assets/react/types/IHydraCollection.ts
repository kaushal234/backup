import { IHydraIriTemplate } from "./IHydraIriTemplate";
import { IHydraView } from "./IHydraView";

export interface IHydraCollection<T> {
  "@context": string;
  "@id": string;
  "@type": string;
  "hydra:totalItems": number;
  "hydra:member": Array<T>;
  "hydra:search"?: IHydraIriTemplate;
  "hydra:view"?: IHydraView;
}
