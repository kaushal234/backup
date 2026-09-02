import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetDetailedUserInfoResponse } from "../@type/IGetDetailedUserInfoResponse";

interface IGetDetailedUserInfoApiPayload {
  token: string;
}

export const getDetailedUserInfo = async (
  data: IGetDetailedUserInfoApiPayload
) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/me`;
    const response = await http.get(url, {
      fetchAlways: true,
      authRequired: false,
      headers: {
        Authorization: `Bearer ${data.token}`,
      },
    });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetDetailedUserInfoResponse>;
  } catch (error) {
    return handleApiError<IGetDetailedUserInfoResponse>(error);
  }
};
