import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllContactResponse } from "../@type/IGetAllContactResponse";

interface IGetAllContactApiPayload {
  customer?: string;
}

interface IGetAllContactApiParams {
  relatedToCustomer?: string;
}

export const getAllContacts = async (data: IGetAllContactApiPayload) => {
  try {
    const queryParams: IGetAllContactApiParams = {};
    // filter
    if (data.customer) {
      queryParams.relatedToCustomer = data.customer;
    }
    const queryString = qs.stringify(queryParams);
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
