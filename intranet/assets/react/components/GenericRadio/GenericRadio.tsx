import React from "react";
import { Field } from "redux-form";
import { renderInlineRadio } from "../Forms/Elements";
import "./GenericRadio.css";
import { IDropdownItem } from "../../types/IDropdownItem";

export interface IGenericRadioProps {
  name: string;
  label?: string;
  required?: boolean;
  horizontalOptions?: boolean;
  list: Array<IDropdownItem>;
  labelTooltip?: string;
}

// Form Value => string | undefined
function GenericRadio(props: IGenericRadioProps) {
  const { name, label, required, horizontalOptions, list, labelTooltip } =
    props;

  return (
    <div
      className={`generic_radio__wrapper 
        ${horizontalOptions && "generic_radio__horizontal"}`}
    >
      <Field
        name={name}
        label={label}
        required={required}
        options={list}
        component={renderInlineRadio}
        horizontalOptions={horizontalOptions}
        labelTooltip={labelTooltip}
      />
    </div>
  );
}

export default GenericRadio;
