import { IContractStatusFormData } from "./IContractStatusFormData";

export type IContractStatusFormErrors = {
  [K in keyof IContractStatusFormData]?: string;
};
