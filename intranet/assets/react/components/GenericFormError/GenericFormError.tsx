import React from "react";
import { renderFormError } from "../Forms/Elements";
import "./GenericFormError.css";

export interface IGenericFormErrorProps {
  error?: string;
  touched?: boolean;
  align?: "start" | "end";
}

function GenericFormError(props: IGenericFormErrorProps) {
  const { error, touched, align } = props;

  return (
    <div
      className={`generic_form_error__wrapper ${
        align === "end" && "generic_form_error__align_end"
      }`}
    >
      {renderFormError(touched, error)}
    </div>
  );
}

export default GenericFormError;
