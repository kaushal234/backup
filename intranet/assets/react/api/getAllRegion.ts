import qs from "qs";
import { client } from "../store";
import { IGetAllRegionResponse } from "../types/IGetAllRegionResponse";
import { IApiResponse } from "../types/IApiResponse";
import { handleError, handlePaginationQueryParams } from "../utils/utils";
import { IPagination } from "../types/IPagination";

interface IGetAllRegionApiPayload extends IPagination {
  searchText?: string;
  division?: Array<string>;
}

interface IGetAllRegionApiParams extends IPagination {
  q?: string;
  "order[name]": "asc";
  "subDivision.division"?: Array<string>;
}

const DEFAULT_QUERY_PARAMS: IGetAllRegionApiParams = {
  itemsPerPage: 10,
  "order[name]": "asc",
  page: 1,
};

export const getAllRegion = async (
  data: IGetAllRegionApiPayload
): Promise<IApiResponse<IGetAllRegionResponse>> => {
  try {
    let queryParams: IGetAllRegionApiParams = DEFAULT_QUERY_PARAMS;
    queryParams = handlePaginationQueryParams(data, queryParams);
    // filter
    queryParams.q = data.searchText;
    queryParams["subDivision.division"] = data.division;
    queryParams.pagination = data.pagination;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/regions?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
