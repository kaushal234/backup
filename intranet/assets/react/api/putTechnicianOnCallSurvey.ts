import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { ITechnicianOnCallSurvey } from "../types/IGetTechnicalOnCallSurveyResponse";
import { handleError } from "../utils/utils";

export type IPutTechnicianOnCallSurveyApiPayload = {
  id: string;
  data: Partial<ISurvey>;
};

interface ISurvey {
  execution: number;
  responsiveness: number;
  communication: number;
  attitude: number;
  comment: string;
}

export const putTechnicianOnCallSurvey = async (
  data: IPutTechnicianOnCallSurveyApiPayload
): Promise<IApiResponse<ITechnicianOnCallSurvey>> => {
  try {
    const response = await client.put(
      `/service/technician_on_call_surveys/${data.id}`,
      data.data
    );

    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
