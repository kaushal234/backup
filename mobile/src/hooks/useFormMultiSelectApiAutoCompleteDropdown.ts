import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { IDropdownItem } from "../@type/IDropdownItem";

interface IProps {
  defaultValue: Array<IDropdownItem>;
  requiredError?: string;
  validate?: (value: Array<IDropdownItem>) => string;
  fetchData: (searchText: string) => Promise<Array<IDropdownItem>>;
  dependsOn?: Array<unknown>;
}

export const useFormMultiSelectApiAutoCompleteDropdown = (props: IProps) => {
  const { t } = useTranslation();

  const {
    defaultValue,
    requiredError,
    validate = (value) => {
      if (requiredError && !value.length) return requiredError;
      return "";
    },
    fetchData,
    dependsOn = [],
  } = props;

  const [value, setValue] = useState(defaultValue);
  const [valueError, setValueError] = useState("");
  const [valueTouched, setValueTouched] = useState(false);

  useEffect(() => {
    setValueError(validate(value));
  }, [value, valueTouched, dependsOn]);

  const defaultChangeHandler = (newValue: Array<IDropdownItem>) => {
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
    setValue([]);
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
      fetchData,
    },
  };
};
