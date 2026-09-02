import { IQrCodePlateFormData } from "./IQrCodePlateFormData";

export type IQrCodePlateFormErrors = {
  [K in keyof IQrCodePlateFormData]?: string;
};
