import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { extractDigits, extractFloat, restrictFilename } from "../utils/utils";

interface IProps {
  defaultValue: string;
  requiredError?: string;
  validate?: (value: string) => string;
  isNumber?: boolean;
  isFileName?: boolean;
  allowDecimal?: boolean;
  dependsOn?: Array<unknown>;
}

export const useFormField = (props: IProps) => {
  const { t } = useTranslation();
  const {
    defaultValue,
    requiredError,
    validate = (value) => {
      if (requiredError && !value) return requiredError;
      return "";
    },
    isNumber,
    isFileName,
    allowDecimal,
    dependsOn = [],
  } = props;

  const [value, setValue] = useState(defaultValue);
  const [valueError, setValueError] = useState("");
  const [valueTouched, setValueTouched] = useState(false);

  useEffect(() => {
    setValueError(validate(value));
  }, [value, valueTouched, dependsOn]);

  const defaultChangeHandler = (
    event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    let newValue = event.target.value;
    if (isNumber) {
      newValue = allowDecimal
        ? extractFloat(newValue)
        : extractDigits(newValue);
    }
    if (isFileName) {
      newValue = restrictFilename(newValue);
    }
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
