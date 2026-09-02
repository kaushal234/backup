import React from "react";
import { renderLabel } from "../Forms/Elements";

export interface IGenericLabelProps {
  name?: string;
  label?: string;
  required?: boolean;
  labelTooltip?: string;
}

function GenericLabel(props: IGenericLabelProps) {
  const { label, name, required, labelTooltip } = props;

  return (
    <div className="generic_label__wrapper">
      {renderLabel(label, name, required, true, labelTooltip)}
    </div>
  );
}

export default GenericLabel;
