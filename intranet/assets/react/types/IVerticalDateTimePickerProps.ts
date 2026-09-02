import { IInlineDateTimePickerProps } from "./IInlineDateTimePickerProps";

export interface IVerticalDateTimePickerProps
  extends IInlineDateTimePickerProps {
  input: any;
  label: any;
  required: any;
  labelTooltip?: string;
  onChange?: (date?: Date | null) => void;
  [key: string]: any;
}
