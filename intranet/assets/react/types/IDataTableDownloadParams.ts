import { IDataTableHeaderItem } from "./IDataTableHeaderItem";

export interface IDataTableDownloadParams {
  headers: Array<IDataTableHeaderItem>;
  textRows: Array<Array<string | null>>;
  filename: string;
}
