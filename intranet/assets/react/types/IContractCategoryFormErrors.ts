import { IContractCategoryFormData } from "./IContractCategoryFormData";

export type IContractCategoryFormErrors = {
  [K in keyof IContractCategoryFormData]?: string;
};
