import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IGetTechnicalOnCallFileByIdApiPayload {
  tocId: string;
  fileId: string;
}

export interface IGetTechnicalOnCallFileByIdApiResponse {
  blob?: Blob;
  url?: string;
}

export const getTechnicalOnCallFileById = async (
  data: IGetTechnicalOnCallFileByIdApiPayload
) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls/${data.tocId}/files/${data.fileId}`;
    const response = await http.get(url, { isBlob: true });
    return {
      status: response.status,
      data: {
        blob: response.data,
        url: response.data && URL.createObjectURL(response.data),
      },
    } as IBasicApiResponse<IGetTechnicalOnCallFileByIdApiResponse>;
  } catch (error) {
    return handleApiError<IGetTechnicalOnCallFileByIdApiResponse>(error);
  }
};
