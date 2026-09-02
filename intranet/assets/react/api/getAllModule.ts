import qs from "qs";
import { handleError } from "../utils/utils";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllModuleResponse } from "../types/IGetAllModuleResponse";

interface IGetAllModuleApiPayload {
  searchText?: string;
}

interface IGetAllModuleApiParams {
  q?: string;
  "order[name]"?: "asc";
  itemsPerPage: string;
  page: string;
}

const DEFAULT_QUERY_PARAMS: IGetAllModuleApiParams = {
  "order[name]": "asc",
  itemsPerPage: "10",
  page: "1",
};

export const getAllModule = async (
  data: IGetAllModuleApiPayload
): Promise<IApiResponse<IGetAllModuleResponse>> => {
  try {
    const queryParams: IGetAllModuleApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/modules?${queryString}`);

    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
