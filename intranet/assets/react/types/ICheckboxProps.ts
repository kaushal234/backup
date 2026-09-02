import React from "react";

export interface ICheckboxProps {
  input: any;
  label?: any;
  required?: any;
  meta: any;
  labelTooltip?: string;
  disabled?: any;
  showError?: boolean;
  onChange?: (event: React.ChangeEvent<HTMLInputElement>) => void;
}
