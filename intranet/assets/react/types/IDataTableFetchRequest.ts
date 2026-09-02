import { IGenericFilterFormData } from "./IGenericFilterFormData";
import { IPagination } from "./IPagination";
import { ISort } from "./ISort";

export interface IDataTableFetchParams {
  pagination: IPagination;
  sort?: IDataTableSort;
  filters?: IGenericFilterFormData;
}

interface IDataTableSort {
  [field: string]: ISort;
}
