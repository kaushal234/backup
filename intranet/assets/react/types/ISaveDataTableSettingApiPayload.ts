import { IDataTableSavedSetting } from "./IDataTableSavedSetting";

export interface ISaveDataTableSettingApiPayload {
  name: string;
  settings: IDataTableSavedSetting;
}
