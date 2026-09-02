import { handleApiError } from "../utils/api";
import { client } from "../store";
import { IBasicApiResponse } from "../types/IBasicApiResponse";
import { IGetAllTechnicalOnCallTypeResponse } from "../types/IGetAllTechnicalOnCallTypeResponse";

export const getAllTechnicianOnCallType = async () => {
  try {
    const response = await client.get(`/service/technician_on_call_types`);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllTechnicalOnCallTypeResponse>;
  } catch (error) {
    return handleApiError<IGetAllTechnicalOnCallTypeResponse>(error);
  }
};
