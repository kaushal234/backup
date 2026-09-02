import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllTechnicalOnCallTagResponse } from "../@type/IGetAllTechnicalOnCallTagResponse";

export const getAllTechnicianOnCallTag = async () => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/technician_on_call_tags`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllTechnicalOnCallTagResponse>;
  } catch (error) {
    return handleApiError<IGetAllTechnicalOnCallTagResponse>(error);
  }
};
