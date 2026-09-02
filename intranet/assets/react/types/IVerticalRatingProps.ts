import { IDropdownItem } from "./IDropdownItem";

export interface IVerticalRatingProps {
  label?: string;
  input: any;
  required?: boolean;
  disabled?: boolean;
  labelTooltip?: string;
  meta: IMeta;
  id?: string;
  list: Array<IDropdownItem>;
  size?: "small" | "large" | "medium";
}

interface IMeta {
  touched: boolean;
  error: string;
}
