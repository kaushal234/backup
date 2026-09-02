import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";

export interface IPostAzureLoginApiPayload {
  token: string;
}

export interface IPostAzureLoginApiResponse {
  token: string;
}

export const postAzureLogin = async (data: IPostAzureLoginApiPayload) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/token-alvest`;
    const formData = new FormData();
    formData.append("token", data.token);
    const response = await http.post(URL, formData, { authRequired: false });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostAzureLoginApiResponse>;
  } catch (error) {
    return handleApiError<IPostAzureLoginApiResponse>(error);
  }
};
