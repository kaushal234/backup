import { ISampleFormData } from "./ISampleFormData";

export type ISampleFormErrors = {
  [K in keyof ISampleFormData]?: string;
};
