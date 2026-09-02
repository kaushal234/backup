import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IDeleteTechnicianOnCallApiPayload {
  id: string;
}

export const deleteTechnicianOnCall = async (
  data: IDeleteTechnicianOnCallApiPayload
): Promise<IBasicApiResponse<null>> => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls/${data.id}`;
    const response = await http.delete(url);
    return {
      status: response.status,
      data: null,
    };
  } catch (error) {
    return handleApiError<null>(error);
  }
};
