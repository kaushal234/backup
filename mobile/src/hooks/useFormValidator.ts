interface IFormValue {
  fieldProps: {
    error: boolean;
  };
  touch: () => void;
  helper: {
    valueError: string;
  };
}

export const useFormValidator = (values: Array<IFormValue>) => {
  const fieldsWithValidators = values;

  const isSubmitDisabled = fieldsWithValidators.some(
    (field) => field.fieldProps.error
  );

  const touchAll = () => {
    fieldsWithValidators.forEach((field) => field.touch());
  };

  const isFormErrorFree = fieldsWithValidators.every(
    (field) => !field.helper.valueError
  );

  return {
    isSubmitDisabled,
    touchAll,
    isFormErrorFree,
  };
};
