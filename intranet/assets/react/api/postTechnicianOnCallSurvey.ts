import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { ITechnicianOnCallSurvey } from "../types/IGetTechnicalOnCallSurveyResponse";
import { handleError } from "../utils/utils";

interface IPostTechnicianOnCallSurveyApiPayload {
  execution: number;
  responsiveness: number;
  communication: number;
  attitude: number;
  comment: string;
}

export const postTechnicianOnCallSurvey = async (
  data: IPostTechnicianOnCallSurveyApiPayload
): Promise<IApiResponse<ITechnicianOnCallSurvey>> => {
  try {
    const response = await client.post(
      "/service/technician_on_call_surveys",
      data
    );
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
