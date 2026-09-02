import { IFilter } from "../types/IFilter";

export const REPORT_FULLSCREEN_BUTTON_CLASSNAME = "report-fullscreen-button";
export const REPORT_CONTAINER_CLASSNAME = "report-container";
export const POWER_BI_URL = process.env.POWERBI_URL || "https://localhost:8082";

export const REPORT_IN_FILTER: IFilter = {
  $schema: "http://powerbi.com/product/schema#basic",
  target: {
    table: "",
    column: "",
  },
  filterType: 1,
  operator: "In",
  values: [],
  requireSingleSelection: false,
};
