import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { IDropdownItem } from "../@type/IDropdownItem";

interface IProps {
  defaultValue: IDropdownItem | null;
  requiredError?: string;
  validate?: (value: IDropdownItem | null) => string;
  fetchData: (searchText: string) => Promise<Array<IDropdownItem>>;
  dependsOn?: Array<unknown>;
  warn?: (value: IDropdownItem | null) => string;
}

export const useFormApiAutoCompleteDropdown = (props: IProps) => {
  const { t } = useTranslation();

  const {
    defaultValue,
    requiredError,
    validate = (value) => {
      if (requiredError && !value) return requiredError;
      return "";
    },
    fetchData,
    dependsOn = [],
    warn = () => "",
  } = props;

  const [value, setValue] = useState(defaultValue);
  const [valueError, setValueError] = useState("");
  const [valueWarning, setValueWarning] = useState("");
  const [valueTouched, setValueTouched] = useState(false);

  useEffect(() => {
    setValueError(validate(value));
    setValueWarning(warn(value));
  }, [value, valueTouched, dependsOn]);

  const defaultChangeHandler = (newValue: IDropdownItem | null) => {
    setValue(newValue);
  };

  const defaultBlurHandler = () => {
    setValueTouched(true);
  };

  const handleReset = () => {
    setValue(defaultValue);
    setValueError("");
    setValueWarning("");
    setValueTouched(false);
  };

  const handleClear = () => {
    setValue(null);
    setValueError("");
    setValueWarning("");
    setValueTouched(false);
  };

  const error = !!(valueTouched && valueError);

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
      setValueWarning,
      setValueTouched,
    },
    fieldProps: {
      value,
      warning: !error && !!valueWarning,
      helperText: t(error ? valueError : valueWarning),
      error: !!(valueTouched && valueError),
      onChange: defaultChangeHandler,
      onBlur: defaultBlurHandler,
      fetchData,
    },
  };
};
