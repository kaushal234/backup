import { Dayjs } from "dayjs";

export interface IDefaultValidatorParams {
  value: Dayjs | null;
  minDate?: string;
  maxDate?: string;
}
