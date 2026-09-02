import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostTechnicianOnCallApiResponse } from "../@type/IPostTechnicalOnCallApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export type IPutRequestTechnicianApiPayload = {
  id: string;
  data: Partial<IRequestTechnician>;
};

interface IRequestTechnician {
  nestedCustomerServiceRecord?: {
    leader: string | null;
    plannedAt: string | null;
  };
}

export const putRequestTechnician = async (
  data: IPutRequestTechnicianApiPayload
) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls/${data.id}/request-technician`;
    const response = await http.put(URL, data.data);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostTechnicianOnCallApiResponse>;
  } catch (error) {
    return handleApiError<IPostTechnicianOnCallApiResponse>(error);
  }
};
