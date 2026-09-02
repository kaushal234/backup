import React from "react";
import TextField from "@mui/material/TextField";
import { useTranslation } from "react-i18next";
import { FormControl, FormLabel, Typography } from "@mui/material";
import "./FormField.css";

interface IProps {
  label?: string;
  subLabel?: string;
  requiredLabel?: boolean;
  onChange: (
    event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => void;
  onBlur?: () => void;
  helperText?: string;
  value: string;
  error?: boolean;
  className?: string;
  id?: string;
  placeholder?: string;
  type?: string;
  autoComplete?: string;
  rows?: number;
  dataCy?: string;
}

function FormField(props: IProps) {
  const {
    label,
    subLabel,
    requiredLabel,
    onChange,
    onBlur,
    helperText,
    value,
    error,
    className,
    id,
    placeholder,
    type,
    autoComplete,
    rows,
    dataCy = "",
  } = props;
  const { t } = useTranslation();

  return (
    <FormControl className="cui_w-100">
      {label && (
        <div className="form_field__label_wrapper">
          <FormLabel
            className="cui_label"
            htmlFor={id}
            data-cy={`${dataCy}-label`}
          >
            {requiredLabel ? `${t(label)} *` : t(label)}
          </FormLabel>
          <Typography
            className="form_field__sub_label"
            data-cy={`${dataCy}-sub-label`}
            variant="caption"
          >
            {t(subLabel ?? "")}
          </Typography>
        </div>
      )}
      <TextField
        onChange={onChange}
        onBlur={onBlur}
        helperText={helperText}
        value={value}
        error={error}
        className={`${className} cui_input_wrapper`}
        id={id}
        placeholder={t(placeholder ?? "")}
        variant="outlined"
        type={type}
        autoComplete={autoComplete}
        {...(rows && { multiline: true })}
        {...(rows && { rows })}
        data-cy={`${dataCy}-field`}
      />
    </FormControl>
  );
}

export default FormField;
