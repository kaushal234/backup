import qs from "qs";
import { StatusCodes } from "http-status-codes";
import http from "./config";
import { handleApiError } from "../utils/api";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllTechnicianOnCallBySearchTextResponse } from "../@type/IGetAllTechnicianOnCallBySearchTextResponse";

interface IGetAllTechnicianOnCallBySearchTextApiPayload {
  question: string;
}

interface IGetAllTechnicianOnCallBySearchTextApiParams {
  query?: string;
}

export const getAllTechnicianOnCallBySearchText = async (
  data: IGetAllTechnicianOnCallBySearchTextApiPayload
): Promise<IBasicApiResponse<IGetAllTechnicianOnCallBySearchTextResponse>> => {
  try {
    const queryParams: IGetAllTechnicianOnCallBySearchTextApiParams = {};
    queryParams.query = data.question;

    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/ai/search/toc?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: StatusCodes.OK,
      data: response.data,
    };
  } catch (e) {
    return handleApiError<IGetAllTechnicianOnCallBySearchTextResponse>(e);
  }
};
