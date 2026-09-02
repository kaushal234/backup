import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};

  if (!values.name) {
    errors.name = Translator.trans("support_team.errors.name");
  }

  return errors;
};

export default validate;
