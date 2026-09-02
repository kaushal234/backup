import { IExtranetUser } from "./IExtranetUser";

export interface IFetchExtranetUsersResponse {
  items: Array<IExtranetUser>;
  total: number;
}
