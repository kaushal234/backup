import { ISortOption } from "./ISortOption";

export interface IGetAllCustomerServiceRecordApiSortPayload {
  sortByAirport?: ISortOption;
  sortByCreatedAt?: ISortOption;
  sortByUpdatedAt?: ISortOption;
}
