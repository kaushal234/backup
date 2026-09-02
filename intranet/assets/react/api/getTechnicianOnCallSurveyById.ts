import { client } from "../store";
import { IBasicApiResponse } from "../types/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import { ITechnicianOnCallSurvey } from "../types/IGetTechnicalOnCallSurveyResponse";

interface IFetchTechnicianOnCallSurveyParams {
  tocSurveyId: string;
}

export const getTechnicianOnCallSurveyById = async (
  params: IFetchTechnicianOnCallSurveyParams
): Promise<IBasicApiResponse<ITechnicianOnCallSurvey>> => {
  try {
    const response = await client.get(
      `/service/technician_on_call_surveys/${params.tocSurveyId}`
    );
    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleApiError<ITechnicianOnCallSurvey>(error);
  }
};
