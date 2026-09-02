import qs from "qs";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";
import { IGetAllManufacturersResponse } from "../types/IGetAllManufacturersResponse";

interface IGetAllManufacturersApiPayload {
  searchText?: string;
}

interface IGetAllManufacturersApiParams {
  q?: string;
  "order[name]": "asc";
}

const DEFAULT_QUERY_PARAMS: IGetAllManufacturersApiParams = {
  "order[name]": "asc",
};

export const getAllManufacturers = async (
  data: IGetAllManufacturersApiPayload
): Promise<IApiResponse<IGetAllManufacturersResponse>> => {
  try {
    const queryParams: IGetAllManufacturersApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/sales/manufacturers?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
