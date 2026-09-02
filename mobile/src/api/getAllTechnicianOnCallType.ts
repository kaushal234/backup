import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllTechnicalOnCallTypeResponse } from "../@type/IGetAllTechnicalOnCallTypeResponse";

export const getAllTechnicianOnCallType = async () => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_call_types`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllTechnicalOnCallTypeResponse>;
  } catch (error) {
    return handleApiError<IGetAllTechnicalOnCallTypeResponse>(error);
  }
};
