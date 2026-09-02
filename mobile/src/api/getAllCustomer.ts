import qs from "qs";
import { handleApiError, handlePaginationQueryParams } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPagination } from "../@type/IPagination";
import { IGetAllCustomerResponse } from "../@type/IGetAllCustomerResponse";

interface IGetAllCustomerApiPayload extends IPagination {
  searchText?: string;
}

interface IGetAllCustomerApiParams extends IPagination {
  q?: string;
  "order[name]": "asc";
  hidden: "0" | "1";
  normalization_groups_override: Array<"customer_list">;
}

const DEFAULT_QUERY_PARAMS: IGetAllCustomerApiParams = {
  "order[name]": "asc",
  hidden: "0",
  normalization_groups_override: ["customer_list"],
};

export const getAllCustomer = async (data: IGetAllCustomerApiPayload) => {
  try {
    let queryParams: IGetAllCustomerApiParams = DEFAULT_QUERY_PARAMS;
    queryParams = handlePaginationQueryParams(data, queryParams);
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/sales/customers?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllCustomerResponse>;
  } catch (error) {
    return handleApiError<IGetAllCustomerResponse>(error);
  }
};
