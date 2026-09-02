import { IDocumentTranslatorFormData } from "../../../types/IDocumentTranslatorFormData";
import { IDocumentTranslatorFormErrors } from "../../../types/IDocumentTranslatorFormErrors";

export const validate = (values: IDocumentTranslatorFormData) => {
  const errors: IDocumentTranslatorFormErrors = {};

  if (!values.language) {
    errors.language = "Required";
  }

  if (!values.formality) {
    errors.formality = "Required";
  }

  if (!values.file?.length) {
    errors.file = "Required";
  }

  return errors;
};
