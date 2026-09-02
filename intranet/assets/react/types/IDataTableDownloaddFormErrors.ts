import { IDataTableDownloadFormData } from "./IDataTableDownloadFormData";

export type IDataTableDownloadFormErrors = {
  [K in keyof IDataTableDownloadFormData]?: string;
};
