import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IGetTechnicalOnCallMainFileByIdApiPayload {
  tocId: string;
  fileId: string;
}

export interface IGetTechnicalOnCallMainFileByIdApiResponse {
  blob?: Blob;
  url?: string;
}

export const getTechnicalOnCallMainFileById = async (
  data: IGetTechnicalOnCallMainFileByIdApiPayload
) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls/${data.tocId}/main_file/${data.fileId}`;
    const response = await http.get(url, { isBlob: true });
    return {
      status: response.status,
      data: {
        blob: response.data,
        url: response.data && URL.createObjectURL(response.data),
      },
    } as IBasicApiResponse<IGetTechnicalOnCallMainFileByIdApiResponse>;
  } catch (error) {
    return handleApiError<IGetTechnicalOnCallMainFileByIdApiResponse>(error);
  }
};
