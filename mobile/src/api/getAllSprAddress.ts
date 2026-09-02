import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllSprAddressResponse } from "../@type/IGetAllSprAddressResponse";

export interface IGetAllSprAddressApiPayload {
  customers: Array<string>;
}

interface IGetAllSprAddressApiParams {
  "contact.extranetUserProfile.customer"?: Array<string>;
}

export const getAllSprAddress = async (
  data: IGetAllSprAddressApiPayload
): Promise<IBasicApiResponse<IGetAllSprAddressResponse>> => {
  try {
    const queryParams: IGetAllSprAddressApiParams = {};
    // filter
    if (data.customers) {
      queryParams["contact.extranetUserProfile.customer"] = data.customers;
    }
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });

    const url = `${process.env.REACT_APP_API_BASE_URL}/parts/spare_parts_request_delivery_addresses?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleApiError<IGetAllSprAddressResponse>(error);
  }
};
