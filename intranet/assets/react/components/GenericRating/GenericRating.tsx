import React from "react";
import { Field } from "redux-form";
import { renderVerticalRating } from "../Forms/Elements";
import { IDropdownItem } from "../../types/IDropdownItem";

export interface IGenericRatingProps {
  list: Array<IDropdownItem>;
  name: string;
  label?: string;
  required?: boolean;
  disabled?: boolean;
  labelTooltip?: string;
  id?: string;
  size?: "small" | "large" | "medium";
}

// Form Value => IDropdownItem | null
function GenericRating(props: IGenericRatingProps) {
  const { list, name, label, required, disabled, labelTooltip, id, size } =
    props;

  return (
    <div className="generic_rating__wrapper">
      <Field
        name={name}
        required={required}
        label={label}
        component={renderVerticalRating}
        disabled={disabled}
        labelTooltip={labelTooltip}
        props={{ list, disabled, size }}
        id={id}
      />
    </div>
  );
}

export default GenericRating;
