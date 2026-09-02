import { IContractCategoryFormData } from "../../../types/IContractCategoryFormData";
import { IContractCategoryFormErrors } from "../../../types/IContractCategoryFormErrors";

export const validate = (values: IContractCategoryFormData) => {
  const errors: IContractCategoryFormErrors = {};

  if (!values.displayedName) {
    errors.displayedName = "Required";
  }

  return errors;
};
