import qs from "qs";
import { handleError } from "../utils/utils";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllProjectTagResponse } from "../types/IGetAllProjectTagResponse";

interface IGetAllProjectTagApiPayload {
  searchText?: string;
}

interface IGetAllProjectTagApiParams {
  q?: string;
  "order[name]"?: "asc";
  itemsPerPage: string;
  page: string;
}

const DEFAULT_QUERY_PARAMS: IGetAllProjectTagApiParams = {
  "order[name]": "asc",
  itemsPerPage: "10",
  page: "1",
};

export const getAllProjectTag = async (
  data: IGetAllProjectTagApiPayload
): Promise<IApiResponse<IGetAllProjectTagResponse>> => {
  try {
    const queryParams: IGetAllProjectTagApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/mis/project_tags?${queryString}`);

    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
