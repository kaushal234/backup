import { IDataTableHeaderItem } from "./IDataTableHeaderItem";

export type IDataTableSavedHeaderItem = Pick<
  IDataTableHeaderItem,
  "isHidden" | "position" | "value"
>;
