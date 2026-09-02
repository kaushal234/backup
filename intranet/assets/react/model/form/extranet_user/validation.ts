import Translator from "bazinga-translator";
import { IExtranetUserFormData } from "../../../types/IExtranetUserFormData";
import { IExtranetUserFormErrors } from "../../../types/IExtranetUserFormErrors";

const validate = (values: IExtranetUserFormData) => {
  const errors: IExtranetUserFormErrors = {};
  const phoneFormat = /^\+\d{1,3}[1-9]\d{6,14}$/;

  if (!values.crt) {
    errors.crt = Translator.trans(
      "directory.extranet_user.errors.required_fields.crt"
    );
  }
  if (!values.email) {
    errors.email = Translator.trans(
      "directory.extranet_user.errors.required_fields.email"
    );
  }
  if (!values.lastname) {
    errors.lastname = Translator.trans(
      "directory.extranet_user.errors.required_fields.lastname"
    );
  }
  if (!values.firstname) {
    errors.firstname = Translator.trans(
      "directory.extranet_user.errors.required_fields.firstname"
    );
  }
  if (!values.division) {
    errors.division = Translator.trans(
      "directory.extranet_user.errors.required_fields.division"
    );
  }
  if (!values.department) {
    errors.department = Translator.trans(
      "directory.extranet_user.errors.required_fields.department"
    );
  }
  if (!values.jobTitle) {
    errors.jobTitle = Translator.trans(
      "directory.extranet_user.errors.required_fields.job_title"
    );
  }
  if (!values.phone) {
    errors.phone = Translator.trans(
      "directory.extranet_user.errors.required_fields.phone"
    );
  } else if (!phoneFormat.test(values.phone)) {
    errors.phone = Translator.trans(
      "directory.extranet_user.errors.phone_invalid_format"
    );
  }
  if (!values.country) {
    errors.country = Translator.trans(
      "directory.extranet_user.errors.required_fields.country"
    );
  }

  return errors;
};

export { validate };
