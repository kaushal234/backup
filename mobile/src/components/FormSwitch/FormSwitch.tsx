import React from "react";
import "./FormSwitch.css";
import { FormControl, FormHelperText, Switch } from "@mui/material";
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
  requiredLabel?: boolean;
  labelTooltip?: string;
}

function FormSwitch(props: IProps) {
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
    requiredLabel,
    labelTooltip,
  } = props;

  return (
    <FormControl className="form_switch__wrapper" data-cy={`${dataCy}-field`}>
      <FormLabelWrapper
        label={label}
        dataCy={dataCy}
        labelPlacement="start"
        requiredLabel={requiredLabel}
        labelTooltip={labelTooltip}
      >
        <Switch
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

export default FormSwitch;
