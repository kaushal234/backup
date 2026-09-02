import _ from "lodash";
import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};
  const sparePartsRequests = _.get(values, "sparePartsRequests", []);
  const sparePartsRequestsErrors: Array<any> = [];

  sparePartsRequests.forEach((sparePartsRequest: any, index: any) => {
    const sparePartsRequestErrors: any = {};
    if (
      (!sparePartsRequest.newAddress ||
        sparePartsRequest.newAddress === "false") &&
      !sparePartsRequest.deliveryAddress
    ) {
      errors._error = Translator.trans(
        "spare_parts_request.errors.delivery_address"
      );
    }

    if (sparePartsRequest.newAddress === "true") {
      if (!sparePartsRequest.firstname) {
        sparePartsRequestErrors.firstname = Translator.trans(
          "spare_parts_request.errors.firstname"
        );
      }
      if (!sparePartsRequest.lastname) {
        sparePartsRequestErrors.lastname = Translator.trans(
          "spare_parts_request.errors.lastname"
        );
      }
      if (!sparePartsRequest.street1) {
        sparePartsRequestErrors.street1 = Translator.trans(
          "spare_parts_request.errors.street1"
        );
      }
      if (!sparePartsRequest.postalCode) {
        sparePartsRequestErrors.postalCode = Translator.trans(
          "spare_parts_request.errors.postal_code"
        );
      }
      if (!sparePartsRequest.city) {
        sparePartsRequestErrors.city = Translator.trans(
          "spare_parts_request.errors.city"
        );
      }
      if (!sparePartsRequest.phone) {
        sparePartsRequestErrors.phone = Translator.trans(
          "spare_parts_request.errors.telephone"
        );
      }
      if (!sparePartsRequest.country) {
        sparePartsRequestErrors.country = Translator.trans(
          "spare_parts_request.errors.country"
        );
      }
      if (!sparePartsRequest.company) {
        sparePartsRequestErrors.company = Translator.trans(
          "spare_parts_request.errors.company"
        );
      }
    }

    if (!_.isEmpty(sparePartsRequestErrors)) {
      sparePartsRequestsErrors[index] = sparePartsRequestErrors;
    }
  });

  if (sparePartsRequestsErrors.length) {
    errors.sparePartsRequests = sparePartsRequestsErrors;
  }
  return errors;
};

export default validate;
