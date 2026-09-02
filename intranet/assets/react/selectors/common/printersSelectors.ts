import { createSelector } from "reselect";
import { RootState } from "../../store";

export const getPrintersList = (state: RootState) => {
  return state.printers.printers || [];
};

export const getPrintersListMapping = createSelector(
  [getPrintersList],
  (printers) =>
    printers.map((printer: any) => ({
      value: printer.reference,
      label: printer.name,
    }))
);
