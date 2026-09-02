import { handleApiError } from "../utils/api";
import { client } from "../store";
import { IBasicApiResponse } from "../types/IBasicApiResponse";
import { IPostTechnicalOnCallDuplicateApiResponse } from "../types/IPostTechnicalOnCallDuplicateApiResponse";

export type IPostTechnicianOnCallDuplicateApiPayload = {
  id: string;
  data: IInputLines;
};

interface IInputLines {
  inputLines: Array<ITechnicianOnCall>;
}

interface ITechnicianOnCall {
  equipmentRecord: string | null;
  airport: string;
  salesOrganisationService: string;
  hourMeter?: number;
}

export const postTechnicianOnCallDuplicate = async (
  data: IPostTechnicianOnCallDuplicateApiPayload
) => {
  try {
    const response = await client.post(
      `/service/technician_on_calls/${data.id}/duplicate`,
      data.data
    );

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostTechnicalOnCallDuplicateApiResponse>;
  } catch (error) {
    return handleApiError<IPostTechnicalOnCallDuplicateApiResponse>(error);
  }
};
