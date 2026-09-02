import React from "react";
import { Field } from "redux-form";
import { renderInlineFileInput } from "../Forms/Elements";
import "./GenericFileInput.css";

export interface IGenericFileInputProps {
  name: string;
  label?: string;
  required?: boolean;
  accept?: string;
  labelTooltip?: string;
  multiple?: boolean;
}

// Form Value => FileList | undefined
function GenericFileInput(props: IGenericFileInputProps) {
  const { name, label, required, accept, labelTooltip, multiple } = props;

  return (
    <div className="generic_file_input__wrapper">
      <Field
        name={name}
        label={label}
        required={required}
        component={renderInlineFileInput}
        showError
        accept={accept}
        labelTooltip={labelTooltip}
        multiple={multiple}
      />
    </div>
  );
}

export default GenericFileInput;
