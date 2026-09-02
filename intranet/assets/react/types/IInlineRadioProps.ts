import { IDropdownItem } from "./IDropdownItem";

export interface IInlineRadioProps {
  name: string;
  options: Array<IDropdownItem>;
  [key: string]: any;
}
