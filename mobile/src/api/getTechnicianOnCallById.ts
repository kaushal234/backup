import qs from "qs";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetTechnicalOnCallsResponse } from "../@type/IGetTechnicalOnCallsResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

interface IGetTechnicianOnCallByIdApiPayload {
  id: string;
}

interface IGetTechnicianOnCallByIdApiParams {
  normalizationGroups: Array<"workflow">;
}

const DEFAULT_QUERY_PARAMS: IGetTechnicianOnCallByIdApiParams = {
  normalizationGroups: ["workflow"],
};

export const getTechnicianOnCallById = async (
  data: IGetTechnicianOnCallByIdApiPayload
) => {
  try {
    const queryParams: IGetTechnicianOnCallByIdApiParams = DEFAULT_QUERY_PARAMS;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls/${data.id}?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetTechnicalOnCallsResponse>;
  } catch (error) {
    return handleApiError<IGetTechnicalOnCallsResponse>(error);
  }
};
