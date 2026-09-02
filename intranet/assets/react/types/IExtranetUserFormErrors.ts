import { IExtranetUserFormData } from "./IExtranetUserFormData";

export type IExtranetUserFormErrors = {
  [K in keyof IExtranetUserFormData]?: string;
};
