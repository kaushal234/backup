import Translator from "bazinga-translator";

const validate = (values: any) => {
  const errors: any = {};
  const properties = [
    "customer",
    "erpLocation",
    "partsLocation",
    "serviceLocation",
    "salesRepresentative",
    "partsRepresentative",
    "serviceRepresentative",
  ];
  properties.forEach((value) => {
    if (!values[value] && !values.id) {
      errors[value] = Translator.trans(
        `customer_relationship_team.errors.${value}`
      );
    }
  });

  if (
    values.id &&
    values.formType !== "edition" &&
    (!values.extranetUserGroups ||
      (values.extranetUserGroups && values.extranetUserGroups.length < 1))
  ) {
    errors.extranetUserGroups = Translator.trans(
      "customer_relationship_team.errors.extranet_user_group"
    );
  }

  if (values.id && values.formType !== "edition" && !values.extranetUser) {
    errors.extranetUser = Translator.trans(
      "customer_relationship_team.errors.extranet_user"
    );
  }

  return errors;
};

export default validate;
