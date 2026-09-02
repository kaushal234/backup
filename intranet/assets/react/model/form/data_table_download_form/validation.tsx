import { IDataTableDownloadFormErrors } from "../../../types/IDataTableDownloaddFormErrors";
import { IDataTableDownloadFormData } from "../../../types/IDataTableDownloadFormData";

export const validate = (values: IDataTableDownloadFormData) => {
  const errors: IDataTableDownloadFormErrors = {};

  if (!values.filename) {
    errors.filename = "Required";
  }

  if (!values.format) {
    errors.format = "Required";
  }

  if (!values.strategy) {
    errors.strategy = "Required";
  }

  return errors;
};
