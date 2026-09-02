import { ISupplierCorrectiveActionRequestAction } from "./ISupplierCorrectiveActionRequestAction";

export interface ISupplierCorrectiveActionRequestActionResponse {
  data: ISupplierCorrectiveActionRequestAction;
  status: number;
  statusText: string;
  headers: IHeader;
}

interface IHeader {
  "cache-control": string;
  "content-language": string;
  "content-type": string;
  link: string;
}
