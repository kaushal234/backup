import qs from "qs";
import { client } from "../store";
import { IGetAllPremiseResponse } from "../types/IGetAllPremiseResponse";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";

interface IGetAllPremiseApiPayload {
  searchText?: string;
}

interface IGetAllPremiseApiParams {
  q?: string;
  itemsPerPage: string;
  "order[name]": "asc";
  page: string;
  archived: boolean;
}

const DEFAULT_QUERY_PARAMS: IGetAllPremiseApiParams = {
  itemsPerPage: "10",
  "order[name]": "asc",
  page: "1",
  archived: false,
};

export const getAllPremise = async (
  data: IGetAllPremiseApiPayload
): Promise<IApiResponse<IGetAllPremiseResponse>> => {
  try {
    const queryParams: IGetAllPremiseApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/premises?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
