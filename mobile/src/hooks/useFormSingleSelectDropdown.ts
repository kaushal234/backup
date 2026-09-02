import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { AsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../@type/IDropdownItem";
import { useAppDispatch, useAppSelector } from "./hooks";
import { RootState } from "../redux/store";

interface IProps {
  defaultValue: IDropdownItem | null;
  requiredError?: string;
  validate?: (value: IDropdownItem | null) => string;
  warn?: (value: IDropdownItem | null) => string;
  list: Array<IDropdownItem>;
  fetchOptions?: AsyncThunk<
    IDropdownItem[],
    void,
    {
      state: RootState;
    }
  >;
  selector?: (state: RootState) => Array<IDropdownItem>;
  dependsOn?: Array<unknown>;
  excludeItems?: Array<IDropdownItem>;
}

export const useFormSingleSelectDropdown = (props: IProps) => {
  const { t } = useTranslation();

  const dispatch = useAppDispatch();
  const {
    defaultValue,
    requiredError,
    validate = (value) => {
      if (requiredError && !value) return requiredError;
      return "";
    },
    warn = () => "",
    list: parentList,
    fetchOptions,
    selector,
    dependsOn = [],
    excludeItems = [],
  } = props;
  const defaultSelector = (state: RootState) => state.dropdownOption.fallback;
  const fetchedOptions = useAppSelector(selector ?? defaultSelector);
  const [list, setList] = useState(parentList);

  const [value, setValue] = useState<IDropdownItem | null>(null);
  const [valueError, setValueError] = useState("");
  const [valueWarning, setValueWarning] = useState("");
  const [valueTouched, setValueTouched] = useState(false);
  const [isDefaultValueUsed, setIsDefaultValueUsed] = useState(false);

  const setSafeValue = (newValue: IDropdownItem | null) => {
    if (list.length && newValue) {
      const isValid = list.find((item) => item.id === newValue?.id);
      if (isValid) {
        setValue(newValue);
      }
    } else {
      setValue(null);
    }
  };

  useEffect(() => {
    setValueError(validate(value));
    setValueWarning(warn(value));
  }, [value, valueTouched, dependsOn]);

  const defaultChangeHandler = (newValue: IDropdownItem | null) => {
    setSafeValue(newValue);
  };

  const defaultBlurHandler = () => {
    setValueTouched(true);
  };

  const handleReset = () => {
    setSafeValue(defaultValue);
    setValueError("");
    setValueWarning("");
    setValueTouched(false);
  };

  const handleClear = () => {
    setSafeValue(null);
    setValueError("");
    setValueWarning("");
    setValueTouched(false);
  };

  useEffect(() => {
    if (fetchOptions) {
      dispatch(fetchOptions());
    }
  }, []);

  useEffect(() => {
    setList(parentList);
  }, [JSON.stringify(parentList)]);

  useEffect(() => {
    if (fetchedOptions.length) {
      const excludedIds = new Set(excludeItems.map((item) => item.id));
      setList(fetchedOptions.filter((item) => !excludedIds.has(item.id)));
    }
  }, [JSON.stringify(fetchedOptions), JSON.stringify(excludeItems)]);

  useEffect(() => {
    if (!isDefaultValueUsed && list.length) {
      setSafeValue(defaultValue);
      setIsDefaultValueUsed(true);
    }
  }, [defaultValue, list, isDefaultValueUsed]);

  const error = !!(valueTouched && valueError);

  return {
    value,
    touch: defaultBlurHandler,
    reset: handleReset,
    clear: handleClear,
    helper: {
      valueError,
      valueTouched,
      setValue: setSafeValue,
      setValueError,
      setValueWarning,
      setValueTouched,
    },
    fieldProps: {
      value,
      error,
      warning: !error && !!valueWarning,
      helperText: t(error ? valueError : valueWarning),
      onChange: defaultChangeHandler,
      onBlur: defaultBlurHandler,
      list,
    },
  };
};
