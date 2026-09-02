import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostTechnicalOnCallPartsApiResponse } from "../@type/IPostTechnicalOnCallPartsApiResponse";
import { ITocPartsFormData } from "../@type/ITocPartsFormData";
import { handleApiError } from "../utils/api";
import http from "./config";

export type IPutTechnicianOnCallPartsApiPayload = {
  id: string;
  data: Partial<ITocPartsFormData>;
};

export const putTechnicianOnCallParts = async (
  data: IPutTechnicianOnCallPartsApiPayload
) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_call_parts/${data.id}`;
    const response = await http.put(URL, data.data);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostTechnicalOnCallPartsApiResponse>;
  } catch (error) {
    const response =
      handleApiError<IPostTechnicalOnCallPartsApiResponse>(error);
    return response;
  }
};
