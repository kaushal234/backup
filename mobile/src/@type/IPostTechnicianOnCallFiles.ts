import { IUploadFile } from "./IFile";

export interface IPostTechnicianOnCallFiles {
  mainFile?: IUploadFile | null;
  files?: Array<IUploadFile>;
}
