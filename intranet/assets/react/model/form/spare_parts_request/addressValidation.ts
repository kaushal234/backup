import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};
  if (
    values.newAddress !== undefined &&
    values.newAddress === "false" &&
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

  return errors;
};

export default validate;
