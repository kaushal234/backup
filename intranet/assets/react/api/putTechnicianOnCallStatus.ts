import { client } from "../store";
import { IBasicApiResponse } from "../types/IBasicApiResponse";
import { ITocStatusPartUpdate } from "../types/ITocStatusPartUpdate";
import { handleApiError } from "../utils/api";

export type IPutTechnicianOnCallStatusApiPayload = {
  id: string;
  data: Partial<ITechnicianOnCallStatus>;
};

interface ITechnicianOnCallStatus {
  status: string;
  symptoms: string;
  rootCause: string;
  solution: string;
  defectiveParts: Array<ITocStatusPartUpdate>;
  thirdPartyJobDescription: string;
  thirdPartyHours: number;
}

export const putTechnicianOnCallStatus = async (
  data: IPutTechnicianOnCallStatusApiPayload
) => {
  try {
    const URL = `/service/technician_on_calls/${data.id}/status`;
    const response = await client.put(URL, data.data);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<unknown>;
  } catch (error) {
    return handleApiError<unknown>(error);
  }
};
