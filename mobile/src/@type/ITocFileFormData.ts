import { IFileWithDescription } from "./IFileWithDescription";

export interface ITocFileFormData {
  mainFile: Array<IFileWithDescription> | null;
  files: Array<IFileWithDescription> | null;
}
