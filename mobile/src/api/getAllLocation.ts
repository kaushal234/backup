import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllLocationResponse } from "../@type/IGetAllLocationResponse";

interface IGetAllPeopleApiPayload {
  factory?: boolean;
  serviceHub?: boolean;
  sso?: boolean;
  searchText?: string;
}

interface IGetAllPeopleApiParams {
  "capability.factory"?: "0" | "1";
  "capability.serviceHub"?: "0" | "1";
  "capability.sso"?: "0" | "1";
  q?: string;
}

export const getAllLocation = async (data: IGetAllPeopleApiPayload) => {
  try {
    const queryParams: IGetAllPeopleApiParams = {};
    // filter
    queryParams["capability.factory"] = data.factory ? "1" : undefined;
    queryParams["capability.serviceHub"] = data.serviceHub ? "1" : undefined;
    queryParams["capability.sso"] = data.sso ? "1" : undefined;
    if (data.searchText) {
      queryParams.q = data.searchText;
    }
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/locations?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllLocationResponse>;
  } catch (error) {
    return handleApiError<IGetAllLocationResponse>(error);
  }
};
