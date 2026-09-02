import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

interface IProps {
  defaultValue: string;
  requiredError?: string;
  validate?: (value: string) => string;
  dependsOn?: Array<unknown>;
}

export const useFormRichTextField = (props: IProps) => {
  const { t } = useTranslation();
  const {
    defaultValue,
    requiredError,
    validate = (value) => {
      if (requiredError && !value) return requiredError;
      return "";
    },
    dependsOn = [],
  } = props;

  const [value, setValue] = useState(defaultValue);
  const [valueError, setValueError] = useState("");
  const [valueTouched, setValueTouched] = useState(false);

  useEffect(() => {
    setValueError(validate(value));
  }, [value, valueTouched, dependsOn]);

  const defaultChangeHandler = (newValue: string) => {
    setValue(newValue);
  };

  const defaultBlurHandler = () => {
    setValueTouched(true);
  };

  const handleReset = () => {
    setValue(defaultValue);
    setValueError("");
    setValueTouched(false);
  };

  const handleClear = () => {
    setValue("");
    setValueError("");
    setValueTouched(false);
  };

  return {
    value,
    touch: defaultBlurHandler,
    reset: handleReset,
    clear: handleClear,
    helper: {
      valueError,
      valueTouched,
      setValue,
      setValueError,
      setValueTouched,
    },
    fieldProps: {
      value,
      error: !!(valueTouched && valueError),
      ...(valueTouched && { helperText: t(valueError) }),
      onChange: defaultChangeHandler,
      onBlur: defaultBlurHandler,
    },
  };
};
