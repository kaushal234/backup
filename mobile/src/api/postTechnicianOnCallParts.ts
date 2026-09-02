import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostTechnicalOnCallPartsApiResponse } from "../@type/IPostTechnicalOnCallPartsApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IPostTechnicianOnCallPartsApiPayload {
  partNumber?: string;
  vendorPartNumber?: string;
  description: string;
  quantity?: number;
  comment?: string;
  replacement?: string | null;
  tocId: string;
}

interface IPostTechnicianOnCallPartsApiPayloadFinal
  extends Omit<IPostTechnicianOnCallPartsApiPayload, "tocId"> {
  technicianOnCall: string;
}

export const postTechnicianOnCallParts = async (
  data: IPostTechnicianOnCallPartsApiPayload
) => {
  try {
    const { tocId, ...rest } = data;
    const finalPayload: IPostTechnicianOnCallPartsApiPayloadFinal = {
      ...rest,
      technicianOnCall: `/service/technician_on_calls/${tocId}`,
    };
    const URL = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_call_parts`;
    const response = await http.post(URL, finalPayload);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostTechnicalOnCallPartsApiResponse>;
  } catch (error) {
    return handleApiError<IPostTechnicalOnCallPartsApiResponse>(error);
  }
};
