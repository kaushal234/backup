import * as React from "react";
import dayjs, { Dayjs } from "dayjs";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import { DatePicker } from "@mui/x-date-pickers/DatePicker";
import { useTranslation } from "react-i18next";
import { FormControl, FormLabel } from "@mui/material";
import { parseDate } from "../../utils/date";
import { DATE_FORMAT } from "../../constants/constants";

interface IProps {
  label?: string;
  value: Dayjs | null;
  onChange: (value: Dayjs | null) => void;
  error?: boolean;
  helperText?: string;
  onBlur?: () => void;
  placeholder?: string;
  maxDate?: string;
  minDate?: string;
  dataCy?: string;
  requiredLabel?: boolean;
}

export default function FormDatePicker(props: IProps) {
  const {
    label,
    value,
    onChange,
    error,
    helperText,
    onBlur,
    placeholder,
    maxDate,
    minDate,
    dataCy,
    requiredLabel,
  } = props;
  const { t } = useTranslation();

  const handleChange = (newValue: Dayjs | null) => {
    onChange(newValue);
  };

  return (
    <FormControl className="cui_w-100" data-cy={`${dataCy}-date-picker`}>
      {label && (
        <FormLabel
          className="cui_label"
          htmlFor="created-after"
          data-cy={`${dataCy}-label`}
        >
          {requiredLabel ? `${t(label)} *` : t(label)}
        </FormLabel>
      )}
      <LocalizationProvider dateAdapter={AdapterDayjs}>
        <DatePicker
          className="cui_input_wrapper cui_input_calender"
          value={value !== null ? dayjs(value) : value}
          onChange={handleChange}
          slotProps={{
            field: { clearable: value !== null },
            textField: {
              helperText: t(helperText ?? ""),
              error,
              onBlur,
              id: dataCy,
              placeholder: placeholder ? t(placeholder) : undefined,
            },
          }}
          maxDate={parseDate(maxDate)}
          minDate={parseDate(minDate)}
          format={DATE_FORMAT}
        />
      </LocalizationProvider>
    </FormControl>
  );
}
