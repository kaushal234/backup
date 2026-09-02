import { IReactInlineSelectProps } from "./IReactInlineSelectProps";

export interface IReactVerticalSelectProps extends IReactInlineSelectProps {
  label: any;
  input: any;
  required: any;
  labelTooltip?: string;
  [key: string]: any;
}
