import qs from "qs";
import { client } from "../store";
import { IGetAllExtranetUserResponse } from "../types/IGetAllExtranetUserResponse";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";

interface IGetAllExtranetUserApiPayload {
  searchText?: string;
}

interface IGetAllPeopleApiParams {
  q?: string;
  "order[email]": "asc";
}

const DEFAULT_QUERY_PARAMS: IGetAllPeopleApiParams = {
  "order[email]": "asc",
};

export const getAllExtranetUser = async (
  data: IGetAllExtranetUserApiPayload
): Promise<IApiResponse<IGetAllExtranetUserResponse>> => {
  try {
    const queryParams: IGetAllPeopleApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/sales/extranet_users?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
