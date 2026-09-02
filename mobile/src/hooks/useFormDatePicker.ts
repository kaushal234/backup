import dayjs, { Dayjs } from "dayjs";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { DATE_FORMAT } from "../constants/constants";
import { defaultDateValidation, isDateRangeValid } from "../utils/date";

interface IProps {
  defaultValue: string | null;
  validate?: (value: Dayjs | null) => string;
  maxDate?: string;
  minDate?: string;
  dependsOn?: Array<unknown>;
}

export const useFormDatePicker = (props: IProps) => {
  const { t } = useTranslation();
  const {
    defaultValue,
    validate = () => "",
    maxDate,
    minDate,
    dependsOn = [],
  } = props;

  const [value, setValue] = useState<Dayjs | null>(
    defaultValue !== null ? dayjs(defaultValue) : null
  );

  const [valueError, setValueError] = useState("");
  const [valueTouched, setValueTouched] = useState(false);

  const defaultChangeHandler = (newValue: Dayjs | null) => {
    setValue(newValue);
  };

  const defaultBlurHandler = () => {
    setValueTouched(true);
  };

  const defaultValidator = () => {
    let error = defaultDateValidation({ value, minDate, maxDate });
    if (!error) {
      error = validate(value);
    }
    return error;
  };

  const handleReset = () => {
    setValue(defaultValue !== null ? dayjs(defaultValue) : null);
    setValueError("");
    setValueTouched(false);
  };

  const handleClear = () => {
    setValue(null);
    setValueError("");
    setValueTouched(false);
  };

  useEffect(() => {
    const defaultValidationError = defaultDateValidation({
      value,
      minDate,
      maxDate,
    });
    if (defaultValidationError) {
      setValueError(defaultValidationError);
      return;
    }
    setValueError(validate(value));
  }, [value, valueTouched, dependsOn]);

  useEffect(() => {
    if (value) {
      defaultBlurHandler();
    }
  }, [value]);

  return {
    value,
    formattedValue:
      value && isDateRangeValid(value) ? value.format(DATE_FORMAT) : null,
    touch: defaultBlurHandler,
    reset: handleReset,
    clear: handleClear,
    helper: {
      valueError,
      valueTouched,
      setValue,
      setValueError,
      setValueTouched,
      defaultValidator,
    },
    fieldProps: {
      value,
      error: !!(valueTouched && valueError),
      ...(valueTouched && { helperText: t(valueError) }),
      onChange: defaultChangeHandler,
      onBlur: defaultBlurHandler,
      maxDate,
      minDate,
    },
  };
};
