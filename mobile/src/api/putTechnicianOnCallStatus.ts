import { ITocDefectivePart } from "../@type/ITocDefectivePart";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostTechnicianOnCallApiResponse } from "../@type/IPostTechnicalOnCallApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export type IPutTechnicianOnCallStatusApiPayload = {
  id: string;
  data: Partial<ITechnicianOnCallStatus>;
};

interface ITechnicianOnCallStatus {
  status: string;
  originalSymptoms: string;
  originalRootCause: string;
  originalSolution: string;
  defectiveParts: Array<ITocDefectivePart>;
  thirdPartyJobDescription: string;
  thirdPartyHours: number;
}

export const putTechnicianOnCallStatus = async (
  data: IPutTechnicianOnCallStatusApiPayload
) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls/${data.id}/status`;
    const response = await http.put(URL, data.data, {
      contentHeaderRequired: true,
    });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostTechnicianOnCallApiResponse>;
  } catch (error) {
    return handleApiError<IPostTechnicianOnCallApiResponse>(error);
  }
};
