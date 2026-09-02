import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { ICsrSurveyFormData } from "../@type/ICsrSurveyFormData";
import { IGetCustomerServiceRecordResponse } from "../@type/IGetCustomerServiceRecordResponse";
import { ISurveyResponse } from "../@type/ISurveyResponse";
import { handleApiError } from "../utils/api";
import { formatSurveyFormData } from "../utils/utils";
import http from "./config";

export type IPutCustomerServiceRecordApiPayload = {
  "@id": string;
  data: Partial<ICustomerServiceRecord>;
};

interface ICustomerServiceRecord {
  title: string;
  description: string;
  airport: string;
  leader: string | null;
  plannedAt: string | null;
  status: string;
  contact: string | null;
  surveyFormData: ICsrSurveyFormData;
}

type IPutCustomerServiceRecordApiPayloadFinal =
  Partial<ICustomerServiceRecordFinal>;

interface ICustomerServiceRecordFinal
  extends Omit<ICustomerServiceRecord, "surveyFormData"> {
  answerSurveyCustomerServiceRecords: Array<ISurveyResponse>;
}

export const putCustomerServiceRecord = async (
  data: IPutCustomerServiceRecordApiPayload
) => {
  try {
    const { surveyFormData, ...rest } = data.data;
    const payload: IPutCustomerServiceRecordApiPayloadFinal = { ...rest };
    if (surveyFormData) {
      payload.answerSurveyCustomerServiceRecords =
        formatSurveyFormData(surveyFormData);
    }
    const URL = `${process.env.REACT_APP_API_BASE_URL}${data["@id"]}`;
    const response = await http.put(URL, payload, {
      contentHeaderRequired: true,
    });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetCustomerServiceRecordResponse>;
  } catch (error) {
    return handleApiError<IGetCustomerServiceRecordResponse>(error);
  }
};
