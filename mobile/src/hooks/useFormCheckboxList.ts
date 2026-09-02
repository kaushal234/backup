import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { ICheckboxListItem } from "../@type/ICheckboxListItem";
import { toggleString } from "../utils/utils";

interface IProps {
  defaultValue: Array<string>;
  requiredError?: string;
  validate?: (value: Array<string>) => string;
  list: Array<ICheckboxListItem>;
  dependsOn?: Array<unknown>;
}

export const useFormCheckboxList = (props: IProps) => {
  const { t } = useTranslation();
  const {
    defaultValue,
    requiredError,
    validate = (value) => {
      if (requiredError && !value.length) return requiredError;
      return "";
    },
    list,
    dependsOn = [],
  } = props;

  const [value, setValue] = useState(defaultValue);
  const [valueError, setValueError] = useState("");
  const [valueTouched, setValueTouched] = useState(false);

  useEffect(() => {
    setValueError(validate(value));
  }, [value, valueTouched, dependsOn]);

  const defaultChangeHandler = (
    event: React.ChangeEvent<HTMLInputElement>,
    index: number
  ) => {
    const clickedItemValue = list[index].id;
    setValue(toggleString(value, clickedItemValue));
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
      list,
    },
  };
};
