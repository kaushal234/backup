import qs from "qs";
import { client } from "../store";
import { IGetAllDivisionResponse } from "../types/IGetAllDivisionResponse";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";

interface IGetAllDivisionApiPayload {
  searchText?: string;
}

interface IGetAllDivisionApiParams {
  q?: string;
  itemsPerPage: string;
  "order[name]": "asc";
  page: string;
}

const DEFAULT_QUERY_PARAMS: IGetAllDivisionApiParams = {
  itemsPerPage: "10",
  "order[name]": "asc",
  page: "1",
};

export const getAllDivision = async (
  data: IGetAllDivisionApiPayload
): Promise<IApiResponse<IGetAllDivisionResponse>> => {
  try {
    const queryParams: IGetAllDivisionApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/divisions?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
