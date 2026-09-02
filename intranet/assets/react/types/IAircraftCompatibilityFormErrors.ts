import { IAircraftCompatibilityFormData } from "./IAircraftCompatibilityFormData";

export type IAircraftCompatibilityFormErrors = {
  [K in keyof IAircraftCompatibilityFormData]?: string;
};
