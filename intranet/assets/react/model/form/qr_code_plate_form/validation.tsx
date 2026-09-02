import Translator from "bazinga-translator";
import { IQrCodePlateFormData } from "../../../types/IQrCodePlateFormData";
import { IQrCodePlateFormErrors } from "../../../types/IQrCodePlateFormErrors";

export const validate = (values: IQrCodePlateFormData) => {
  const errors: IQrCodePlateFormErrors = {};

  if (!values.mfgLocation) {
    errors.mfgLocation = Translator.trans(
      "support.qr_code_plate.mfg_location.error"
    );
  }

  if (!values.model) {
    errors.model = Translator.trans("support.qr_code_plate.model.error");
  }

  if (!values.mfgDate) {
    errors.mfgDate = Translator.trans("support.qr_code_plate.mfg_date.error");
  }

  if (!values.serialNumber) {
    errors.serialNumber = Translator.trans(
      "support.qr_code_plate.serial_number.error"
    );
  }

  if (!values.unladenKg) {
    errors.unladenKg = Translator.trans(
      "support.qr_code_plate.unladen_kg.error.required"
    );
  }

  if (values.unladenKg && +values.unladenKg === 0) {
    errors.unladenKg = Translator.trans(
      "support.qr_code_plate.unladen_kg.error.zero"
    );
  }

  if ((values.unladenKg?.length ?? 0) + (values.unladenLbs?.length ?? 0) > 14) {
    errors.unladenKg = Translator.trans(
      "support.qr_code_plate.unladen_kg.error.char_limit"
    );
  }

  if (!values.unladenLbs) {
    errors.unladenLbs = Translator.trans(
      "support.qr_code_plate.unladen_lbs.error.required"
    );
  }

  if (values.unladenLbs && +values.unladenLbs === 0) {
    errors.unladenLbs = Translator.trans(
      "support.qr_code_plate.unladen_lbs.error.zero"
    );
  }

  if (!values.ratedPowerKw) {
    errors.ratedPowerKw = Translator.trans(
      "support.qr_code_plate.rated_power_kw.error.required"
    );
  }

  if (values.ratedPowerKw && +values.ratedPowerKw === 0) {
    errors.ratedPowerKw = Translator.trans(
      "support.qr_code_plate.rated_power_kw.error.zero"
    );
  }

  if (
    (values.ratedPowerKw?.length ?? 0) + (values.ratedPowerHp?.length ?? 0) >
    14
  ) {
    errors.ratedPowerKw = Translator.trans(
      "support.qr_code_plate.rated_power_kw.error.char_limit"
    );
  }

  if (!values.ratedPowerHp) {
    errors.ratedPowerHp = Translator.trans(
      "support.qr_code_plate.rated_power_hp.error.required"
    );
  }

  if (values.ratedPowerHp && +values.ratedPowerHp === 0) {
    errors.ratedPowerHp = Translator.trans(
      "support.qr_code_plate.rated_power_hp.error.zero"
    );
  }

  return errors;
};
