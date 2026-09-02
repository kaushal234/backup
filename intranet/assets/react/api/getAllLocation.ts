import qs from "qs";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";
import { IGetAllLocationResponse } from "../types/IGetAllLocationResponse";

interface IGetAllLocationApiPayload {
  searchText?: string;
  factory?: boolean;
  serviceHub?: boolean;
  sso?: boolean;
  itemsPerPage?: number;
}

interface IGetAllLocationApiParams {
  q?: string;
  itemsPerPage: string | number;
  "order[name]": "asc";
  page: string;
  "capability.factory"?: "0" | "1";
  "capability.serviceHub"?: "0" | "1";
  "capability.sso"?: "0" | "1";
}

const DEFAULT_QUERY_PARAMS: IGetAllLocationApiParams = {
  itemsPerPage: "10",
  "order[name]": "asc",
  page: "1",
};

export const getAllLocation = async (
  data: IGetAllLocationApiPayload
): Promise<IApiResponse<IGetAllLocationResponse>> => {
  try {
    const queryParams: IGetAllLocationApiParams = { ...DEFAULT_QUERY_PARAMS };
    queryParams.q = data.searchText;
    queryParams.itemsPerPage =
      data.itemsPerPage ?? DEFAULT_QUERY_PARAMS.itemsPerPage;
    queryParams["capability.factory"] = data.factory ? "1" : undefined;
    queryParams["capability.serviceHub"] = data.serviceHub ? "1" : undefined;
    queryParams["capability.sso"] = data.sso ? "1" : undefined;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/locations?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
