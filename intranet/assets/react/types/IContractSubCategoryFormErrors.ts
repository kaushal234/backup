import { IContractSubCategoryFormData } from "./IContractSubCategoryFormData";

export type IContractSubCategoryFormErrors = {
  [K in keyof IContractSubCategoryFormData]?: string;
};
