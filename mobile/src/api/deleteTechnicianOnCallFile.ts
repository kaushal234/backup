import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IDeleteTechnicianOnCallFileApiPayload {
  tocId: string;
  fileId: string;
}

export const deleteTechnicianOnCallFile = async (
  data: IDeleteTechnicianOnCallFileApiPayload
): Promise<IBasicApiResponse<null>> => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls/${data.tocId}/files/${data.fileId}`;
    const response = await http.delete(url);
    return {
      status: response.status,
      data: null,
    };
  } catch (error) {
    return handleApiError<null>(error);
  }
};
