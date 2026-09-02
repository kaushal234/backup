import React from "react";
import "./FormCheckbox.css";
import { Checkbox, FormControl, FormHelperText } from "@mui/material";
import FormLabelWrapper from "../FormLabelWrapper/FormLabelWrapper";

interface IProps {
  label?: string;
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
  onBlur?: () => void;
  helperText?: string;
  value: boolean;
  error?: boolean;
  className?: string;
  dataCy?: string;
  disabled?: boolean;
}

function FormCheckbox(props: IProps) {
  const {
    label,
    onChange,
    onBlur,
    helperText,
    value,
    error,
    className,
    dataCy = "",
    disabled,
  } = props;

  return (
    <FormControl className="form_checkbox__wrapper" data-cy={`${dataCy}-field`}>
      <FormLabelWrapper label={label} dataCy={dataCy} labelPlacement="start">
        <Checkbox
          onChange={onChange}
          onBlur={onBlur}
          checked={value}
          className={className}
          disabled={disabled}
        />
      </FormLabelWrapper>
      <FormHelperText error={error}>{helperText}</FormHelperText>
    </FormControl>
  );
}

export default FormCheckbox;
