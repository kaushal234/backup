export interface IInlineDateTimePickerProps {
  input: any;
  meta: any;
  dateformat: any;
  views: any;
  showError?: boolean;
  onBlur?: () => void;
  onFocus?: () => void;
  onChange?: (date?: Date | null) => void;
  [key: string]: any;
}
