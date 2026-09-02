import { IDropdownItem } from "./IDropdownItem";

export interface IPaginatedFetchResult {
  items: IDropdownItem[];
  nextUrl?: string;
}

export type IPaginatedFetchFunction = (
  nextUrl?: string
) => Promise<IPaginatedFetchResult>;
