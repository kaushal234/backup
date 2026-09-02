import Translator from "bazinga-translator";
import { IAircraftCompatibilityFormData } from "../../../types/IAircraftCompatibilityFormData";
import { IAircraftCompatibilityFormErrors } from "../../../types/IAircraftCompatibilityFormErrors";

export const validate = (values: IAircraftCompatibilityFormData) => {
  const errors: IAircraftCompatibilityFormErrors = {};

  if (values.products && values.products.length < 1) {
    errors.products = Translator.trans(
      "aircraft_compatibility.errors.products"
    );
  }

  if (values.aircrafts && values.aircrafts.length < 1) {
    errors.aircrafts = Translator.trans(
      "aircraft_compatibility.errors.aircrafts"
    );
  }

  return errors;
};
