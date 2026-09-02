import qs from "qs";
import { client } from "../store";
import { IGetAllBusinessUnitResponse } from "../types/IGetAllBusinessUnitResponse";
import { IApiResponse } from "../types/IApiResponse";
import { handleError, handlePaginationQueryParams } from "../utils/utils";
import { IPagination } from "../types/IPagination";

interface IGetAllBusinessUnitApiPayload extends IPagination {
  searchText?: string;
  division?: Array<string>;
  region?: Array<string>;
}

interface IGetAllBusinessUnitApiParams extends IPagination {
  q?: string;
  "order[name]": "asc";
  "region.subDivision.division"?: Array<string>;
  region?: Array<string>;
}

const DEFAULT_QUERY_PARAMS: IGetAllBusinessUnitApiParams = {
  itemsPerPage: 10,
  "order[name]": "asc",
  page: 1,
};

export const getAllBusinessUnit = async (
  data: IGetAllBusinessUnitApiPayload
): Promise<IApiResponse<IGetAllBusinessUnitResponse>> => {
  try {
    let queryParams: IGetAllBusinessUnitApiParams = DEFAULT_QUERY_PARAMS;
    queryParams = handlePaginationQueryParams(data, queryParams);
    // filter
    queryParams.q = data.searchText;
    queryParams["region.subDivision.division"] = data.division;
    queryParams.region = data.region;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/business_units?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
