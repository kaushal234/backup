import { IProductFamilyFormData } from "../../../types/IProductFamilyFormData";
import { IProductFamilyFormErrors } from "../../../types/IProductFamilyFormErrors";

export const validate = (values: IProductFamilyFormData) => {
  const errors: IProductFamilyFormErrors = {};

  if (!values.name) {
    errors.name = "Required";
  }

  if (!values.productType) {
    errors.productType = "Required";
  }

  return errors;
};
