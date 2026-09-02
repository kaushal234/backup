import _ from "lodash";
import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};
  const parts = _.get(values, "parts", []);
  const partsErrors: Array<any> = [];
  parts.forEach((partItem: any, index: any) => {
    const partsItemError: any = {};

    if (
      (!partItem.quantity || partItem.quantity < 1) &&
      partItem.module !== "NCR" &&
      partItem.module !== "VWC" &&
      partItem.module !== "SCAR"
    ) {
      partsItemError.quantity = Translator.trans(
        "shipping_quotation_request.errors.quantity"
      );
    }

    if (!partItem.unitOfMeasure && !partItem?.partNumber?.unitOfMeasure) {
      partsItemError.unitOfMeasure = Translator.trans(
        "spare_parts_request.errors.unit_of_measure"
      );
    }

    if (!partItem.partNumber) {
      partsItemError.partNumber = Translator.trans(
        "spare_parts_request.errors.part_number"
      );
    }

    if (!_.isEmpty(partsItemError)) {
      partsErrors[index] = partsItemError;
    }
  });

  if (partsErrors.length) {
    errors.parts = partsErrors;
  }
  return errors;
};

export default validate;
