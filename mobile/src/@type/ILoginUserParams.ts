import { IPeople } from "./IGetDetailedUserInfoResponse";

export interface ILoginUserParams {
  token: string;
  userInfo: IPeople;
  features: Array<string>;
}
