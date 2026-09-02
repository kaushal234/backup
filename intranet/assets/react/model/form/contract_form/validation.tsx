import { FormErrors } from "redux-form";
import { IContractFormProps } from "../../../components/ContractForm/ContractForm";
import { IContractFormData } from "../../../types/IContractFormData";
import { CONTRACT_CUSTOMERS_SUB_CATEGORIES } from "../../../constants/constants";
import { hasValidString } from "../../../utils/utils";

export const validate = (
  values: IContractFormData,
  props: IContractFormProps
) => {
  const errors: FormErrors<IContractFormData> = {};

  if (!values.shortDescription) {
    errors.shortDescription = "Required";
  }

  if (!values.subCategory) {
    errors.subCategory = "Required";
  }

  if (!values.description) {
    errors.description = "Required";
  }

  if (!values.businessUnits) {
    errors.businessUnits = "Required";
  }

  if (!values.startDate) {
    errors.startDate = "Required";
  }

  if (props.isEdit) {
    if (!values.owner) {
      errors.owner = "Required";
    }

    if (!values.status) {
      errors.status = "Required";
    }
  }

  if (
    CONTRACT_CUSTOMERS_SUB_CATEGORIES.includes(values.subCategory?.value ?? "")
  ) {
    if (!values.customers || values.customers.length === 0) {
      errors.customers = "Required";
    }
  }

  if (!values.externalParty) {
    errors.externalParty = "Required";
  }

  if (!values.internalParty || values.internalParty.length === 0) {
    errors.internalParty = { _error: "Required" } as unknown as string;
  }

  if (values.internalParty?.length && !hasValidString(values.internalParty)) {
    errors.internalParty = {
      _error: "Please enter at least one value that isn't blank.",
    } as unknown as string;
  }

  return errors;
};
