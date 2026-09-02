import React from "react";
import { InjectedFormProps, reduxForm } from "redux-form";
import Translator from "bazinga-translator";
import { validate } from "../../model/form/qr_code_plate_form/validation";
import { toastSuccess } from "../../utils/utils";
import { IQrCodePlateFormData } from "../../types/IQrCodePlateFormData";
import GenericFormComponent from "../GenericFormComponent/GenericFormComponent";
import SvgDownloader from "../SvgDownloader/SvgDownloader";
import { ELEMENT_ID } from "../../constants/drawing";

export const QR_CODE_PLATE_FORM_NAME = "qr_code_plate_form";

interface IProps {
  svgString: string;
  onFocusChange: (name: string) => void;
}

type IWrappedProps = IProps & InjectedFormProps<IQrCodePlateFormData, IProps>;

function QrCodePlateForm(props: IWrappedProps) {
  const {
    submitting,
    handleSubmit,
    submitFailed,
    invalid,
    svgString,
    onFocusChange,
  } = props;

  const onSubmit = async () => {
    await toastSuccess(
      Translator.trans("support.qr_code_plate.success.description"),
      Translator.trans("support.qr_code_plate.success.title")
    );
  };

  const onFocusChangeDelayed = (name: string) => {
    setTimeout(() => {
      onFocusChange(name);
    }, 0);
  };

  return (
    <form
      className="qr_code_plate_form__wrapper"
      noValidate
      onSubmit={handleSubmit(onSubmit)}
    >
      <GenericFormComponent
        type="Field"
        label={Translator.trans("support.qr_code_plate.mfg_location.label")}
        placeholder={Translator.trans(
          "support.qr_code_plate.mfg_location.placeholder"
        )}
        name="mfgLocation"
        required
        disabled
      />
      <GenericFormComponent
        type="Field"
        label={Translator.trans("support.qr_code_plate.model.label")}
        placeholder={Translator.trans(
          "support.qr_code_plate.model.placeholder"
        )}
        name="model"
        required
        disabled
      />
      <GenericFormComponent
        type="DatePicker"
        label={Translator.trans("support.qr_code_plate.mfg_date.label")}
        placeholder={Translator.trans(
          "support.qr_code_plate.mfg_date.placeholder"
        )}
        name="mfgDate"
        required
        onFocus={() => onFocusChangeDelayed(ELEMENT_ID.mfgDate)}
        onBlur={() => onFocusChange("")}
        dateformat="MM/YYYY"
        views={["year", "decade"]}
      />
      <GenericFormComponent
        type="Field"
        label={Translator.trans("support.qr_code_plate.serial_number.label")}
        placeholder={Translator.trans(
          "support.qr_code_plate.serial_number.placeholder"
        )}
        name="serialNumber"
        required
        disabled
      />
      <GenericFormComponent
        type="Field"
        label={Translator.trans("support.qr_code_plate.unladen_kg.label")}
        placeholder={Translator.trans(
          "support.qr_code_plate.unladen_kg.placeholder"
        )}
        name="unladenKg"
        allowNumbersOnly
        required
        onFocus={() => onFocusChangeDelayed(ELEMENT_ID.unladenWeightKg)}
        onBlur={() => onFocusChange("")}
        maxCharacters={6}
      />
      <GenericFormComponent
        type="Field"
        label={Translator.trans("support.qr_code_plate.unladen_lbs.label")}
        placeholder={Translator.trans(
          "support.qr_code_plate.unladen_lbs.placeholder"
        )}
        name="unladenLbs"
        allowNumbersOnly
        required
        onFocus={() => onFocusChangeDelayed(ELEMENT_ID.unladenWeightLbs)}
        onBlur={() => onFocusChange("")}
        maxCharacters={6}
      />
      <GenericFormComponent
        type="Field"
        label={Translator.trans("support.qr_code_plate.rated_power_kw.label")}
        placeholder={Translator.trans(
          "support.qr_code_plate.rated_power_kw.placeholder"
        )}
        name="ratedPowerKw"
        allowNumbersOnly
        required
        onFocus={() => onFocusChangeDelayed(ELEMENT_ID.ratedPowerKw)}
        onBlur={() => onFocusChange("")}
        maxCharacters={6}
      />
      <GenericFormComponent
        type="Field"
        label={Translator.trans("support.qr_code_plate.rated_power_hp.label")}
        placeholder={Translator.trans(
          "support.qr_code_plate.rated_power_hp.placeholder"
        )}
        name="ratedPowerHp"
        allowNumbersOnly
        required
        onFocus={() => onFocusChangeDelayed(ELEMENT_ID.ratedPowerHp)}
        onBlur={() => onFocusChange("")}
        maxCharacters={6}
      />
      <GenericFormComponent
        type="Switch"
        label={Translator.trans("support.qr_code_plate.ce_logo.label")}
        name="logo"
        onFocus={() => onFocusChangeDelayed(ELEMENT_ID.ceLogo)}
        onBlur={() => onFocusChange("")}
      />
      <SvgDownloader
        svgString={svgString}
        filename="name-plate"
        isSubmit
        disabled={submitting || (submitFailed && invalid)}
        allowDownload={!invalid}
      />
    </form>
  );
}

export default reduxForm<IQrCodePlateFormData, IProps>({
  form: QR_CODE_PLATE_FORM_NAME,
  enableReinitialize: true,
  validate,
})(QrCodePlateForm);
