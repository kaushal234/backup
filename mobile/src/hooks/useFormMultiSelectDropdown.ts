import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { AsyncThunk } from "@reduxjs/toolkit";
import { IDropdownItem } from "../@type/IDropdownItem";
import { useAppDispatch, useAppSelector } from "./hooks";
import { RootState } from "../redux/store";

interface IProps {
  defaultValue: Array<IDropdownItem>;
  validate?: (value: Array<IDropdownItem>) => string;
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

export const useFormMultiSelectDropdown = (props: IProps) => {
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const {
    defaultValue,
    validate = () => "",
    list: parentList,
    fetchOptions,
    selector,
    dependsOn = [],
    excludeItems = [],
  } = props;
  const defaultSelector = (state: RootState) => state.dropdownOption.fallback;
  const fetchedOptions = useAppSelector(selector ?? defaultSelector);
  const [list, setList] = useState(parentList);

  const [value, setValue] = useState<Array<IDropdownItem>>([]);
  const [valueError, setValueError] = useState("");
  const [valueTouched, setValueTouched] = useState(false);
  const [isDefaultValueUsed, setIsDefaultValueUsed] = useState(false);

  const setSafeValue = (newValue: Array<IDropdownItem>) => {
    const safeNewValue = newValue.filter((item) => {
      return list.find((current) => current.id === item.id);
    });
    setValue(safeNewValue);
  };

  useEffect(() => {
    setValueError(validate(value));
  }, [value, valueTouched, dependsOn]);

  const defaultChangeHandler = (newValue: Array<IDropdownItem>) => {
    setSafeValue(newValue);
  };

  const defaultBlurHandler = () => {
    setValueTouched(true);
  };

  const handleReset = () => {
    setSafeValue(defaultValue);
    setValueError("");
    setValueTouched(false);
  };

  const handleClear = () => {
    setSafeValue([]);
    setValueError("");
    setValueTouched(false);
  };

  useEffect(() => {
    if (fetchOptions) {
      dispatch(fetchOptions());
    }
  }, []);

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
