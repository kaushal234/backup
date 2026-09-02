import { IDocumentCategory } from "./IDocumentCategory";

export interface IDocumentGroup {
  manual: Array<IDocumentCategory>;
  parts: Array<IDocumentCategory>;
}
