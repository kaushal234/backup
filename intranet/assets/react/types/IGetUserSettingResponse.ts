export type IGetUserSettingResponse = IUserSetting;

export interface IUserSetting {
  "@context": string;
  "@id": string;
  "@type": string;
  settings: object;
  user: string;
  name: string;
  id: number;
}
