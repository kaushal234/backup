import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllAirportResponse } from "../@type/IGetAllAirportResponse";

interface IGetAllAirportApiPayload {
  searchText?: string;
}

interface IGetAllPeopleApiParams {
  q?: string;
  "order[code]": "asc";
  normalization_groups_override: Array<"airport_list">;
}

const DEFAULT_QUERY_PARAMS: IGetAllPeopleApiParams = {
  "order[code]": "asc",
  normalization_groups_override: ["airport_list"],
};

export const getAllAirport = async (data: IGetAllAirportApiPayload) => {
  try {
    const queryParams: IGetAllPeopleApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });

    const url = `${process.env.REACT_APP_API_BASE_URL}/airports?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllAirportResponse>;
  } catch (error) {
    return handleApiError<IGetAllAirportResponse>(error);
  }
};
