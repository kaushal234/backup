import dayjs from "dayjs";
import { DATE_TIME_FORMAT_LONG } from "../constants/constants";

export const formatDateTimeLong = (dateTime: string): string => {
  return dayjs(dateTime).format(DATE_TIME_FORMAT_LONG);
};
