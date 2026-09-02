import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllItemMonologisticsResponse } from "../@type/IGetAllItemMonologisticsResponse";

interface IGetAllItemMonologisticsApiPayload {
  searchText?: string;
}

interface IGetAllItemMonologisticsApiParams {
  "itemCode[like]"?: string;
  selection: Array<"description" | "itemCode">;
}

const DEFAULT_QUERY_PARAMS: IGetAllItemMonologisticsApiParams = {
  selection: ["description", "itemCode"],
};

export const getAllItemMonologistics = async (
  data: IGetAllItemMonologisticsApiPayload
): Promise<IBasicApiResponse<IGetAllItemMonologisticsResponse>> => {
  try {
    const queryParams: IGetAllItemMonologisticsApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams["itemCode[like]"] = `%${data.searchText}%`;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });

    const url = `${process.env.REACT_APP_API_BASE_URL}/ion/item_monologistics?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleApiError<IGetAllItemMonologisticsResponse>(error);
  }
};
