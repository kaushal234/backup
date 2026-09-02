import qs from "qs";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";
import { IGetAllAircraftsResponse } from "../types/IGetAllAircraftsResponse";

interface IGetAllAircraftsApiPayload {
  searchText?: string;
}

interface IGetAllAircraftsApiParams {
  q?: string;
  "order[name]": "asc";
}

const DEFAULT_QUERY_PARAMS: IGetAllAircraftsApiParams = {
  "order[name]": "asc",
};

export const getAllAircrafts = async (
  data: IGetAllAircraftsApiPayload
): Promise<IApiResponse<IGetAllAircraftsResponse>> => {
  try {
    const queryParams: IGetAllAircraftsApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/sales/aircrafts?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
