import React from "react";
import { Field } from "redux-form";
import { renderCheckbox } from "../Forms/Elements";
import "./GenericCheckbox.css";

export interface IGenericCheckboxProps {
  name: string;
  label?: string;
  required?: boolean;
  checkboxFirst?: boolean;
  fitContent?: boolean;
  labelTooltip?: string;
  onChange?: (
    event: React.ChangeEvent<HTMLInputElement>,
    value: boolean
  ) => void;
  disabled?: boolean;
  centered?: boolean;
}

// Form Value => boolean | undefined
function GenericCheckbox(props: IGenericCheckboxProps) {
  const {
    name,
    label,
    required,
    checkboxFirst,
    fitContent,
    labelTooltip,
    onChange,
    disabled,
    centered,
  } = props;

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    onChange?.(event, event.target.checked);
  };

  return (
    <div
      className={`generic_checkbox__wrapper  
        ${checkboxFirst && "generic_checkbox__reverse"}
        ${fitContent && "generic_checkbox__fit_content"}
        ${centered && "generic_checkbox__center"}`}
    >
      <Field
        name={name}
        label={label}
        required={required}
        component={renderCheckbox}
        showError
        labelTooltip={labelTooltip}
        props={{
          onChange: onChange ? handleChange : undefined,
        }}
        disabled={disabled}
      />
    </div>
  );
}

export default GenericCheckbox;
