import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllTechnicalOnCallStatusResponse } from "../@type/IGetAllTechnicalOnCallStatusResponse";

export const getAllTechnicianOnCallStatus = async () => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/places/service/technician_on_calls`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllTechnicalOnCallStatusResponse>;
  } catch (error) {
    return handleApiError<IGetAllTechnicalOnCallStatusResponse>(error);
  }
};
