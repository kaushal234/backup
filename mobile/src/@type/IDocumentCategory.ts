import { IManualDocument } from "./IGetManualResponse";

export interface IDocumentCategory {
  name: string;
  files: Array<IManualDocument>;
}
