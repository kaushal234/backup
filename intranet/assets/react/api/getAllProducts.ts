import qs from "qs";
import { client } from "../store";
import { IGetAllProductsResponse } from "../types/IGetAllProductsResponse";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";

interface IGetAllProductsApiPayload {
  searchText?: string;
}

interface IGetAllProductsApiParams {
  q?: string;
  "order[name]": "asc";
  normalization_groups_override: Array<"product_list">;
}

const DEFAULT_QUERY_PARAMS: IGetAllProductsApiParams = {
  "order[name]": "asc",
  normalization_groups_override: ["product_list"],
};

export const getAllProducts = async (
  data: IGetAllProductsApiPayload
): Promise<IApiResponse<IGetAllProductsResponse>> => {
  try {
    const queryParams: IGetAllProductsApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/sales/products?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
