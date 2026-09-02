import Translator from "bazinga-translator";
import { ISupplierCorrectiveActionRequestFactoryProps } from "../../../../types/ISupplierCorrectiveActionRequestFactoryProps";

interface IError {
  factory?: string;
  importanceFactor?: string;
  shortDescription?: string;
  description?: string;
  representative?: string;
  supplierNumber?: string;
  [key: string]: string | undefined;
}

const validate = (values: ISupplierCorrectiveActionRequestFactoryProps) => {
  const errors: IError = {};

  if (!values.factory) {
    errors.factory = Translator.trans(
      "supplier_corrective_action_request.errors.factory"
    );
  }
  if (!values.importanceFactor) {
    errors.importanceFactor = Translator.trans(
      "supplier_corrective_action_request.errors.importance_factor"
    );
  }
  if (!values.shortDescription) {
    errors.shortDescription = Translator.trans(
      "supplier_corrective_action_request.errors.short_description"
    );
  }
  if (!values.description) {
    errors.description = Translator.trans(
      "supplier_corrective_action_request.errors.description"
    );
  }
  if (!values.representative) {
    errors.representative = Translator.trans(
      "supplier_corrective_action_request.errors.representative"
    );
  }
  if (!values.supplierNumber) {
    errors.supplierNumber = Translator.trans(
      "supplier_corrective_action_request.errors.supplier_number"
    );
  }

  return errors;
};

export default validate;
