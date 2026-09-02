import { IDropdownItem } from "./IDropdownItem";

export interface IRenderInlineRadioProps {
  input: any;
  meta: any;
  options: Array<IDropdownItem>;
  name: string;
  required: boolean;
  label: string;
  labelTooltip?: string;
}
