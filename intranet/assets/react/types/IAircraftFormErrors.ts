import { IAircraftFormData } from "./IAircraftFormData";

export type IAircraftFormErrors = {
  [K in keyof IAircraftFormData]?: string;
};
