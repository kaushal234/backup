import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetManualResponse } from "../@type/IGetManualResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

interface IGetManualByIdApiPayload {
  id: string;
}

export const getManualById = async (data: IGetManualByIdApiPayload) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/support/manuals/${data.id}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetManualResponse>;
  } catch (error) {
    return handleApiError<IGetManualResponse>(error);
  }
};
