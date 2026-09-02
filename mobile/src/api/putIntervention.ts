import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPutInterventionApiResponse } from "../@type/IPutInterventionApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export type IPutInterventionApiPayload = {
  id: string;
  data: IIntervention;
};

export type IIntervention = {
  status: string;
  endedAt?: string;
  startedAt?: string;
};

export const putIntervention = async (data: IPutInterventionApiPayload) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/service/interventions/${data.id}`;
    const response = await http.put(URL, data.data);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPutInterventionApiResponse>;
  } catch (error) {
    return handleApiError<IPutInterventionApiResponse>(error);
  }
};
