import { ISortOption } from "./ISortOption";

export interface IGetAllTechnicianOnCallApiSortPayload {
  sortByAirport?: ISortOption;
  sortByIFactor?: ISortOption;
  sortByCreatedAt?: ISortOption;
  sortByUpdatedAt?: ISortOption;
}
