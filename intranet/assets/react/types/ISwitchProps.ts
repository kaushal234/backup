import { ChangeEvent } from "react";

export interface ISwitchProps {
  input: any;
  label?: any;
  required?: any;
  showError?: boolean;
  meta?: any;
  labelTooltip?: string;
  onBlur?: () => void;
  onFocus?: () => void;
  onChange?: (e: ChangeEvent<HTMLInputElement>, value: string) => void;
}
