import { IAircraftFormData } from "../../../types/IAircraftFormData";
import { IAircraftFormErrors } from "../../../types/IAircraftFormErrors";

export const validate = (values: IAircraftFormData) => {
  const errors: IAircraftFormErrors = {};

  if (!values.name) {
    errors.name = "Required";
  }

  if (!values.manufacturer) {
    errors.manufacturer = "Required";
  }

  return errors;
};
