import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export type IDeleteTechnicianOnCallPartsApiPayload = {
  id: string;
};

export const deleteTechnicianOnCallParts = async (
  data: IDeleteTechnicianOnCallPartsApiPayload
) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_call_parts/${data.id}`;
    const response = await http.delete(URL);
    return {
      status: response.status,
      data: null,
    } as IBasicApiResponse<null>;
  } catch (error) {
    const response = handleApiError<null>(error);
    return response;
  }
};
