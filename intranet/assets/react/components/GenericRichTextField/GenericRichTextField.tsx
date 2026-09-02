import React from "react";
import { Field } from "redux-form";
import { renderReactRichTextEditor } from "../Forms/Elements";

export interface IGenericRichTextFieldProps {
  placeholder?: string;
  name: string;
  label?: string;
  required?: boolean;
  labelTooltip?: string;
  disabled?: boolean;
  editorHeight?: string;
}

// Form Value => string | undefined
function GenericRichTextField(props: IGenericRichTextFieldProps) {
  const {
    placeholder,
    name,
    label,
    required,
    labelTooltip,
    disabled,
    editorHeight,
  } = props;

  return (
    <div className={`generic_rich_text_field__wrapper ${name}`}>
      <Field
        name={name}
        label={label}
        required={required}
        component={renderReactRichTextEditor}
        placeholder={placeholder}
        showError
        labelTooltip={labelTooltip}
        disabled={disabled}
        editorHeight={editorHeight}
      />
    </div>
  );
}

export default GenericRichTextField;
