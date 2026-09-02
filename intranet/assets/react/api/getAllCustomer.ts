import qs from "qs";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";
import { IGetAllCustomerResponse } from "../types/IGetAllCustomerResponse";
import { IPagination } from "../types/IPagination";

interface IGetAllCustomerApiPayload {
  searchText?: string;
}

interface IGetAllCustomerApiParams extends IPagination {
  q?: string;
  "order[name]": "asc";
  hidden: "0" | "1";
  normalization_groups_override: Array<"customer_list">;
}

const DEFAULT_QUERY_PARAMS: IGetAllCustomerApiParams = {
  itemsPerPage: 10,
  hidden: "0",
  "order[name]": "asc",
  page: 1,
  normalization_groups_override: ["customer_list"],
};

export const getAllCustomer = async (
  data: IGetAllCustomerApiPayload
): Promise<IApiResponse<IGetAllCustomerResponse>> => {
  try {
    const queryParams: IGetAllCustomerApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/sales/customers?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
