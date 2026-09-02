import { IFileWithDescription } from "./IFileWithDescription";

export interface IFileUploadPopUpSubmitParams {
  mainFile: IFileWithDescription | null;
  files: Array<IFileWithDescription>;
}
