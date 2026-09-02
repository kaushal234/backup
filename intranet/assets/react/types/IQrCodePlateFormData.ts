export interface IQrCodePlateFormData {
  mfgLocation?: string;
  model?: string;
  mfgDate?: Date | null;
  serialNumber?: string;
  unladenKg?: string;
  unladenLbs?: string;
  ratedPowerKw?: string;
  ratedPowerHp?: string;
  logo?: boolean;
}
