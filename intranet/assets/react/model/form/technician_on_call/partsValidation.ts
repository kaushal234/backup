import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};

  if (!values.partNumber && !values.vendorPartNumber) {
    errors.partNumber = Translator.trans(
      "toc.messages.errors.parts.part_number"
    );
    errors.vendorPartNumber = Translator.trans(
      "toc.messages.errors.parts.part_number"
    );
  }

  if (!values.description) {
    errors.description = Translator.trans("toc.messages.errors.description");
  }

  if (!values.quantity) {
    errors.quantity = Translator.trans("toc.messages.errors.quantity");
  }

  if (/\s/.test(values.partNumber)) {
    errors.partNumber = Translator.trans(
      "toc.messages.errors.parts.part_number_space"
    );
  }

  return errors;
};

export default validate;
