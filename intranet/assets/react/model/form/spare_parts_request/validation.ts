import _ from "lodash";
import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};
  if (
    (!values.newAddress || values.newAddress === "false") &&
    !values.deliveryAddress
  ) {
    errors._error = Translator.trans(
      "spare_parts_request.errors.delivery_address"
    );
  }

  if (values.newAddress === "true") {
    if (!values.firstname) {
      errors.firstname = Translator.trans(
        "spare_parts_request.errors.firstname"
      );
    }
    if (!values.lastname) {
      errors.lastname = Translator.trans("spare_parts_request.errors.lastname");
    }
    if (!values.street1) {
      errors.street1 = Translator.trans("spare_parts_request.errors.street1");
    }
    if (!values.postalCode) {
      errors.postalCode = Translator.trans(
        "spare_parts_request.errors.postal_code"
      );
    }
    if (!values.city) {
      errors.city = Translator.trans("spare_parts_request.errors.city");
    }
    if (!values.phone) {
      errors.phone = Translator.trans("spare_parts_request.errors.telephone");
    }
    if (!values.country) {
      errors.country = Translator.trans("spare_parts_request.errors.country");
    }
    if (!values.company) {
      errors.company = Translator.trans("spare_parts_request.errors.company");
    }
  }

  const parts = _.get(values, "parts", []);
  if (parts.length < 1) {
    errors._error = Translator.trans("spare_parts_request.errors.part");
    return errors;
  }
  const partsErrors: Array<any> = [];
  parts.forEach((partItem: any, index: any) => {
    const partsItemError: any = {};

    if (!partItem.quantity || partItem.quantity < 1) {
      partsItemError.quantity = Translator.trans(
        "shipping_quotation_request.errors.quantity"
      );
    }

    if (!partItem.unitOfMeasure) {
      partsItemError.unitOfMeasure = Translator.trans(
        "spare_parts_request.errors.unit_of_measure"
      );
    }

    if (!partItem.partNumber) {
      partsItemError.partNumber = Translator.trans(
        "spare_parts_request.errors.part_number"
      );
    }

    if (partItem.partNumber && partItem.partNumber.error) {
      partsItemError.partNumber = partItem.partNumber.error;
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
