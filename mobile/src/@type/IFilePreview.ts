import { IFetchFileType } from "./IFetchFileType";

export interface IFilePreview {
  name: string;
  label?: string;
  chipText?: string;
  mimeType: string;
  url?: string;
  description?: string | null;
  createdAt?: string;
  additionalInfo?: {
    type: IFetchFileType;
    dataTypeId: string;
    fileId: string;
  };
}
