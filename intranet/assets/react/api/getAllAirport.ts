import qs from "qs";
import { client } from "../store";
import { IGetAllAirportResponse } from "../types/IGetAllAirportResponse";

interface IGetAllAirportApiPayload {
  searchText?: string;
}

interface IGetAllAirportApiParams {
  q?: string;
  "order[code]": "asc";
  normalization_groups_override: Array<"airport_list">;
}

const DEFAULT_QUERY_PARAMS: IGetAllAirportApiParams = {
  "order[code]": "asc",
  normalization_groups_override: ["airport_list"],
};

export const getAllAirport = async (data: IGetAllAirportApiPayload) => {
  try {
    const queryParams: IGetAllAirportApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/airports?${queryString}`);
    return {
      status: 200,
      data: response.data as IGetAllAirportResponse,
    };
  } catch (error) {
    console.error(error);
    return { status: 500 };
  }
};
