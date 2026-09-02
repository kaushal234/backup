import { IContractStatusFormData } from "../../../types/IContractStatusFormData";
import { IContractStatusFormErrors } from "../../../types/IContractStatusFormErrors";

export const validate = (values: IContractStatusFormData) => {
  const errors: IContractStatusFormErrors = {};

  if (!values.observationStatus) {
    errors.observationStatus = "Required";
  }

  return errors;
};
