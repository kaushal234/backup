import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllContactResponse } from "../@type/IGetAllContactResponse";

export interface IGetAllCsrContactApiPayload {
  buyer?: number;
  endUser?: number;
  maintainer?: number;
}

interface IGetAllCsrContactApiParams {
  "extranetUserAcls.crt.customer"?: Array<number>;
  "extranetUserAcls.extranetUserGroup.name"?: Array<"role_ST" | "fl_NOT_TOC">;
  "extranetUserProfile.archived"?: "0";
  hidden?: "0";
  normalization_groups_override?: Array<"extranet_user_list">;
  pagination?: "0";
}

const DEFAULT_QUERY_PARAMS: IGetAllCsrContactApiParams = {
  "extranetUserAcls.extranetUserGroup.name": ["role_ST", "fl_NOT_TOC"],
  "extranetUserProfile.archived": "0",
  hidden: "0",
  normalization_groups_override: ["extranet_user_list"],
  pagination: "0",
};

export const getAllCsrContacts = async (data: IGetAllCsrContactApiPayload) => {
  try {
    const queryParams: IGetAllCsrContactApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    const customer: Array<number> = [];
    if (data.buyer) {
      customer.push(data.buyer);
    }
    if (data.endUser) {
      customer.push(data.endUser);
    }
    if (data.maintainer) {
      customer.push(data.maintainer);
    }
    if (customer.length) {
      queryParams["extranetUserAcls.crt.customer"] = customer;
    }
    const queryString = qs.stringify(queryParams, {
      arrayFormat: "brackets",
    });

    const url = `${process.env.REACT_APP_API_BASE_URL}/sales/extranet_users?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllContactResponse>;
  } catch (error) {
    return handleApiError<IGetAllContactResponse>(error);
  }
};
