import { IProductFamilyFormData } from "./IProductFamilyFormData";

export type IProductFamilyFormErrors = {
  [K in keyof IProductFamilyFormData]?: string;
};
