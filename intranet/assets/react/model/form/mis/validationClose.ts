import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};

  if (values.status === "SOLVED" && !values.satisfaction) {
    errors.satisfaction = Translator.trans(
      "trouble_ticket.errors.satisfaction"
    );
  }

  if (!values.comment) {
    errors.comment = Translator.trans("trouble_ticket.errors.comment");
  }

  if (!values.status) {
    errors.status = Translator.trans("trouble_ticket.errors.status");
  }

  return errors;
};

export default validate;
