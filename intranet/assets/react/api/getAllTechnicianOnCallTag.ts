import { handleApiError } from "../utils/api";
import { client } from "../store";
import { IBasicApiResponse } from "../types/IBasicApiResponse";
import { IGetAllTechnicalOnCallTagResponse } from "../types/IGetAllTechnicalOnCallTagResponse";

export const getAllTechnicianOnCallTag = async () => {
  try {
    const response = await client.get(`/technician_on_call_tags`);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllTechnicalOnCallTagResponse>;
  } catch (error) {
    return handleApiError<IGetAllTechnicalOnCallTagResponse>(error);
  }
};
