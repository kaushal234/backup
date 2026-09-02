import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";

export interface ILoginApiPayload {
  username: string;
  password: string;
}

export interface ILoginApiResponse {
  token: string;
}

export const loginApi = async (data: ILoginApiPayload) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/token`;
    const formData = qs.stringify({ ...data, portal: "intranet" });
    const config = { authRequired: false };
    const response = await http.post(URL, formData, config);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<ILoginApiResponse>;
  } catch (error) {
    return handleApiError<ILoginApiResponse>(error);
  }
};
