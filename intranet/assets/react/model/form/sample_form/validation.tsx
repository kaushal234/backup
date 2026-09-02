import { ISampleFormData } from "../../../types/ISampleFormData";
import { ISampleFormErrors } from "../../../types/ISampleFormErrors";

export const validate = (values: ISampleFormData) => {
  const errors: ISampleFormErrors = {};

  if (!values.username) {
    errors.username = "Required";
  }

  if (!values.description) {
    errors.description = "Required";
  }

  if (!values.arrival1) {
    errors.arrival1 = "Required";
  }

  if (!values.departure1) {
    errors.departure1 = "Required";
  }

  if (!values.arrival2) {
    errors.arrival2 = "Required";
  }
  if (!values.departure2) {
    errors.departure2 = "Required";
  }

  if (!values.airport) {
    errors.airport = "Required";
  }

  if (!values.travelDetails) {
    errors.travelDetails = "Required";
  }

  if (!values.departureDate) {
    errors.departureDate = "Required";
  }

  if (!values.terms) {
    errors.terms = "Required";
  }

  if (!values.conditions) {
    errors.conditions = "Required";
  }

  if (!values.passportHolder) {
    errors.passportHolder = "Required";
  }

  return errors;
};
