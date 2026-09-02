const validate = (values: any) => {
  const errors: any = {};

  if (
    !values.all &&
    !values.productType &&
    !values.customer &&
    !values.mimType &&
    !values.competitor &&
    !values.supplier
  ) {
    errors._error = "One field at least is mandatory.";
  }

  return errors;
};

export default validate;
