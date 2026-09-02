import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetSprByTocIdResponse } from "../@type/IGetSprByTocIdResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

interface IGetSporByTocIdApiPayload {
  tocId: string;
}

export const getSprByTocId = async (
  data: IGetSporByTocIdApiPayload
): Promise<IBasicApiResponse<IGetSprByTocIdResponse>> => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/parts/spare_parts_request_from_toc/${data.tocId}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleApiError<IGetSprByTocIdResponse>(error);
  }
};
