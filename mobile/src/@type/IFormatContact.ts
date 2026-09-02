import { IPhone } from "./IGetTechnicalOnCallsResponse";

export interface IFormatContact {
  "@id": string;
  firstname: string | null;
  lastname: string | null;
  email: string;
  phones: Array<IPhone>;
}
