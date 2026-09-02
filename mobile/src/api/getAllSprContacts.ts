import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllContactResponse } from "../@type/IGetAllContactResponse";

export interface IGetAllSprContactApiPayload {
  customers: Array<string>;
}

interface IGetAllSprContactApiParams {
  "extranetUserProfile.archived"?: "0";
  hidden: "0";
  "extranetUserProfile.customer"?: Array<string>;
}

const DEFAULT_QUERY_PARAMS: IGetAllSprContactApiParams = {
  "extranetUserProfile.archived": "0",
  hidden: "0",
};

export const getAllSprContacts = async (
  data: IGetAllSprContactApiPayload
): Promise<IBasicApiResponse<IGetAllContactResponse>> => {
  try {
    const queryParams: IGetAllSprContactApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    if (data.customers) {
      queryParams["extranetUserProfile.customer"] = data.customers;
    }
    const queryString = qs.stringify(queryParams, {
      arrayFormat: "brackets",
    });

    const url = `${process.env.REACT_APP_API_BASE_URL}/sales/extranet_users?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleApiError<IGetAllContactResponse>(error);
  }
};
