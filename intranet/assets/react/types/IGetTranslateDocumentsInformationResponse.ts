import { IDocumentTranslation } from "./IDocumentTranslation";

export type IGetTranslateDocumentsInformationResponse = {
  "hydra:member": Array<IDocumentTranslation>;
  "hydra:totalItems"?: number;
  "@context"?: string;
  "@id"?: string;
  "@type"?: string;
};
