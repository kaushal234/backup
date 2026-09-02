import React, { FocusEventHandler } from "react";
import { Field, Normalizer } from "redux-form";
import { renderInlineTextarea, renderVerticalInput } from "../Forms/Elements";
import "./GenericField.css";

export interface IGenericFieldProps {
  placeholder?: string;
  name: string;
  label?: string;
  required?: boolean;
  isTextArea?: boolean;
  allowNumbersOnly?: boolean;
  allowFloatsOnly?: boolean;
  disabled?: boolean;
  labelTooltip?: string;
  maxCharacters?: number;
  onChange?: (e: React.ChangeEvent<HTMLInputElement>, value: string) => void;
  size?: "sm" | "lg";
  labelClassName?: string;
  onFocus?: FocusEventHandler<HTMLInputElement | HTMLTextAreaElement>;
  onBlur?: FocusEventHandler<HTMLInputElement | HTMLTextAreaElement>;
  normalize?: Normalizer;
  maxLength?: number;
  rows?: number;
  addon?: string;
  id?: string;
}

// Form Value => string | undefined
function GenericField(props: IGenericFieldProps) {
  const {
    placeholder,
    name,
    label,
    required,
    isTextArea,
    allowNumbersOnly,
    allowFloatsOnly,
    disabled,
    labelTooltip,
    onBlur,
    onFocus,
    maxCharacters,
    onChange,
    size,
    labelClassName,
    normalize,
    maxLength,
    rows,
    addon,
    id,
  } = props;

  return (
    <div className="generic_field__wrapper">
      <Field
        name={name}
        required={required}
        label={label}
        placeholder={placeholder}
        component={isTextArea ? renderInlineTextarea : renderVerticalInput}
        {...(!isTextArea && { allowNumbersOnly })}
        {...(!isTextArea && { allowFloatsOnly })}
        {...(!isTextArea && { maxCharacters })}
        disabled={disabled}
        labelTooltip={labelTooltip}
        onBlur={onBlur}
        onFocus={onFocus}
        props={{
          ...(!isTextArea && { onChange }),
        }}
        size={size}
        {...(!isTextArea && { labelClassName })}
        normalize={normalize}
        maxLength={maxLength}
        rows={rows}
        addon={addon}
        id={id}
      />
    </div>
  );
}

export default GenericField;
