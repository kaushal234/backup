import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllCountryResponse } from "../@type/IGetAllCountryResponse";

interface IGetAllCountryApiPayload {
  searchText?: string;
}

interface IGetAllCountryApiParams {
  q?: string;
  "order[name]": "asc";
  normalization_groups_override: Array<"country_list" | "country_phone_code">;
}

const DEFAULT_QUERY_PARAMS: IGetAllCountryApiParams = {
  "order[name]": "asc",
  normalization_groups_override: ["country_list", "country_phone_code"],
};

export const getAllCountry = async (
  data: IGetAllCountryApiPayload
): Promise<IBasicApiResponse<IGetAllCountryResponse>> => {
  try {
    const queryParams: IGetAllCountryApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });

    const url = `${process.env.REACT_APP_API_BASE_URL}/countries?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleApiError<IGetAllCountryResponse>(error);
  }
};
