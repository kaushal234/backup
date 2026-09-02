import dayjs, { Dayjs } from "dayjs";
import {
  DATE_FORMAT,
  DATE_TIME_FORMAT,
  DATE_TIME_FORMAT_LONG,
  DATE_TODAY,
} from "../constants/constants";
import { IDefaultValidatorParams } from "../@type/IDefaultValidatorParams";

export const isDateRangeValid = (value: Dayjs) => {
  if (!value.isValid()) return false;
  if (value.isBefore("01/01/1900")) return false;
  if (value.isAfter("12/31/2099")) return false;
  return true;
};

export const parseDate = (value?: string) => {
  if (value === undefined) return undefined;
  if (value.toUpperCase() === DATE_TODAY) return dayjs();
  return dayjs(value);
};

export const defaultDateValidation = (params: IDefaultValidatorParams) => {
  const { value, minDate, maxDate } = params;
  if (value && !isDateRangeValid(value)) {
    return "common.invalid_date";
  }
  if (value && maxDate && value.isAfter(parseDate(maxDate))) {
    return "common.invalid_date_future";
  }
  if (value && minDate && value.isBefore(parseDate(minDate))) {
    return "common.invalid_date_past";
  }
  return "";
};

export const calculateDateDifference = (dateString: string): number => {
  const givenDate = new Date(dateString);
  const today = new Date();

  const timeDifferenceInSeconds =
    Math.abs(today.getTime() - givenDate.getTime()) / 1000;

  const dayDifference = timeDifferenceInSeconds / (3600 * 24);

  return Math.floor(dayDifference);
};

export const formatDateTimeLong = (dateTime: string): string => {
  return dayjs(dateTime).format(DATE_TIME_FORMAT_LONG);
};

export const formatDateTime = (dateTime: string): string => {
  return dayjs(dateTime).format(DATE_TIME_FORMAT);
};

export const formatDate = (dateTime: string): string => {
  return dayjs(dateTime).format(DATE_FORMAT);
};

export const getSecondsFromDays = (days: number) => {
  return days * 24 * 60 * 60;
};

export const getFormattedTodayDate = () => {
  return dayjs().format(DATE_FORMAT);
};
