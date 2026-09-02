import React from "react";
import { Field } from "redux-form";
import renderSwitch from "../Forms/Elements";
import "./GenericSwitch.css";

export interface IGenericSwitchProps {
  name: string;
  label?: string;
  required?: boolean;
  switchFirst?: boolean;
  fitContent?: boolean;
  labelTooltip?: string;
  onBlur?: () => void;
  onFocus?: () => void;
  onChange?: (e: React.ChangeEvent<HTMLInputElement>, value: string) => void;
  centered?: boolean;
  isVertical?: boolean;
}

// Form Value => boolean | undefined
function GenericSwitch(props: IGenericSwitchProps) {
  const {
    name,
    label,
    switchFirst,
    fitContent,
    labelTooltip,
    onBlur,
    onFocus,
    required,
    centered,
    isVertical,
    onChange,
  } = props;

  return (
    <div
      className={`generic_switch__wrapper 
        ${switchFirst && "generic_switch__reverse"}
        ${fitContent && "generic_switch__fit_content"}
        ${centered && "generic_switch__centered"}
        ${isVertical && "generic_switch__vertical"}`}
    >
      <Field
        name={name}
        label={label}
        required={required}
        component={renderSwitch}
        showError
        labelTooltip={labelTooltip}
        onBlur={onBlur}
        onFocus={onFocus}
        props={{
          onChange,
        }}
      />
    </div>
  );
}

export default GenericSwitch;
