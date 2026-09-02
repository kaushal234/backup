import { IHydraIriTemplateMapping } from "./IHydraIriTemplateMapping";

export interface IHydraIriTemplate {
  "@type": string;
  "hydra:template": string;
  "hydra:variableRepresentation": string;
  "hydra:mapping": Array<IHydraIriTemplateMapping>;
}
