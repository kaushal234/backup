import React from "react";
import { Field } from "redux-form";
import { renderVerticalDateTimePicker } from "../Forms/Elements";

export interface IGenericDatePickerProps {
  placeholder?: string;
  name: string;
  label?: string;
  required?: boolean;
  disabled?: boolean;
  min?: Date | null;
  max?: Date | null;
  labelTooltip?: string;
  onBlur?: () => void;
  onFocus?: () => void;
  dateformat?: string;
  onChange?: (date?: Date | null, value?: Date | null) => void;
  views?: Array<"month" | "year" | "decade" | "century">;
  className?: string;
}

// Form Value => Date | null | undefined
function GenericDatePicker(props: IGenericDatePickerProps) {
  const {
    placeholder,
    name,
    label,
    required,
    disabled,
    min,
    max,
    labelTooltip,
    onBlur,
    onFocus,
    dateformat,
    onChange,
    views,
    className,
  } = props;

  const handleChange = (value?: Date | null) => {
    onChange?.(value, value);
  };

  return (
    <div className={`generic_date_picker__wrapper ${name}`}>
      <Field
        required={required}
        name={name}
        component={renderVerticalDateTimePicker}
        label={label}
        placeholder={placeholder}
        showError
        outlineToday
        disabled={disabled}
        max={max}
        min={min}
        labelTooltip={labelTooltip}
        onBlur={onBlur}
        onFocus={onFocus}
        dateformat={dateformat}
        views={views}
        props={{
          onChange: onChange ? handleChange : undefined,
        }}
        className={className}
      />
    </div>
  );
}

export default GenericDatePicker;
