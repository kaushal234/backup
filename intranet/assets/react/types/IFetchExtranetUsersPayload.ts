export interface IFetchExtranetUsersPayload {
  page: number;
  pageSize: number;
  sortModel?: IExtranetUserSortModel;
  searchQuery?: string;
  customers?: Array<string>;
  locations?: Array<string>;
}

export interface IExtranetUserSortModel {
  field:
    | "id"
    | "email"
    | "extranetUserProfile.customer.name"
    | "extranetUserProfile.erpLocation.name";
  sort: "asc" | "desc";
}
