import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetExtranetUserResponse } from "../@type/IGetExtranetUserResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

interface IGetExtranetUserByIdApiPayload {
  id: string;
}

export const getExtranetUserById = async (
  data: IGetExtranetUserByIdApiPayload
) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/sales/extranet_users/${data.id}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetExtranetUserResponse>;
  } catch (error) {
    return handleApiError<IGetExtranetUserResponse>(error);
  }
};
