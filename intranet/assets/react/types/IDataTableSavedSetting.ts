import { IDataTableSavedHeaderItem } from "./IDataTableSavedHeaderItem";
import { IGenericFilterFormSubmissionData } from "./IGenericFilterFormSubmissionData";
import { IPagination } from "./IPagination";
import { ISortModal } from "./ISortModal";

export interface IDataTableSavedSetting {
  headers?: Array<IDataTableSavedHeaderItem>;
  filters?: IGenericFilterFormSubmissionData;
  sortModel?: ISortModal | null;
  pagination?: IPagination;
}
