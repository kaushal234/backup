import { IHydraCollection } from "./IHydraCollection";

export type IGetAllUserSettingResponse = IHydraCollection<IUserSetting>;

export interface IUserSetting {
  "@id": string;
  "@type": string;
  settings: object;
  user: string;
  name: string;
  id: number;
}
