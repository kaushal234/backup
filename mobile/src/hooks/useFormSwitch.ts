import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

interface IProps {
  defaultValue: boolean;
  validate?: (value: boolean) => string;
  dependsOn?: Array<unknown>;
}

export const useFormSwitch = (props: IProps) => {
  const { t } = useTranslation();
  const { defaultValue, validate = () => "", dependsOn = [] } = props;

  const [value, setValue] = useState(defaultValue);
  const [valueError, setValueError] = useState("");
  const [valueTouched, setValueTouched] = useState(false);

  useEffect(() => {
    setValueError(validate(value));
  }, [value, valueTouched, dependsOn]);

  const defaultChangeHandler = (event: React.ChangeEvent<HTMLInputElement>) => {
    setValue(event.target.checked);
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
    setValue(false);
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
