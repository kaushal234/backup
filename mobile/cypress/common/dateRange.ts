import dayjs from "dayjs";
import { TEXT } from "../constants/strings";
import { formatDate } from "../../src/utils/date";
import { formDatePicker } from "./formDatePicker";

export const checkDateRange = (
  afterSel: string,
  beforeSel: string,
  afterBeforeError: string
) => {
  const today = dayjs().toISOString();
  const tomorrow = dayjs().add(1, "day").toISOString();
  const yesterday = dayjs().subtract(1, "day").toISOString();

  const formattedToday = formatDate(today);
  const formattedTomorrow = formatDate(tomorrow);
  const formattedYesterday = formatDate(yesterday);

  formDatePicker({
    fieldName: afterSel,
    setValue: formattedTomorrow,
    valueError: TEXT.dateRange.errors.future,
  });

  formDatePicker({
    fieldName: beforeSel,
    setValue: formattedTomorrow,
    valueError: TEXT.dateRange.errors.future,
  });

  formDatePicker({
    fieldName: afterSel,
    clear: true,
    setValue: TEXT.dateRange.invalidValue,
    valueError: TEXT.dateRange.errors.invalid,
  });

  formDatePicker({
    fieldName: beforeSel,
    clear: true,
    setValue: TEXT.dateRange.invalidValue,
    valueError: TEXT.dateRange.errors.invalid,
  });

  formDatePicker({
    fieldName: afterSel,
    clear: true,
    setValue: formattedToday,
  });

  formDatePicker({
    fieldName: beforeSel,
    clear: true,
    setValue: formattedYesterday,
  });

  formDatePicker({
    fieldName: afterSel,
    valueError: afterBeforeError,
  });

  formDatePicker({
    fieldName: beforeSel,
    clear: true,
    setValue: formattedToday,
  });
};
