import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { IUploadFileType } from "../@type/IUploadFileType";
import { IFileWithDescription } from "../@type/IFileWithDescription";

interface IProps {
  defaultValue: Array<IFileWithDescription> | null;
  validate?: (value: Array<IFileWithDescription> | null) => string;
  maxSize?: number;
  accept: IUploadFileType;
  allowMultipleFiles?: boolean;
  dependsOn?: Array<unknown>;
}

export const useFormFileUpload = (props: IProps) => {
  const { t } = useTranslation();
  const {
    defaultValue,
    validate = () => "",
    maxSize,
    accept,
    allowMultipleFiles,
    dependsOn = [],
  } = props;

  const [value, setValue] = useState(defaultValue);
  const [valueError, setValueError] = useState("");
  const [valueTouched, setValueTouched] = useState(false);

  useEffect(() => {
    setValueError(validate(value));
  }, [value, valueTouched, dependsOn]);

  const defaultChangeHandler = (
    newValue: Array<IFileWithDescription> | null
  ) => {
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
    setValue(null);
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
      maxSize,
      accept,
      allowMultipleFiles,
    },
  };
};
