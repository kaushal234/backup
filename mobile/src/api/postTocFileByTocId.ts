import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostTocFileByTocIdResponse } from "../@type/IPostTocFileByTocIdResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IPostTocFileByTocIdApiPayload {
  tocId: string;
  file: File;
  description: string;
}

export const postTocFileByTocId = async (
  data: IPostTocFileByTocIdApiPayload
) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls/${data.tocId}/files`;
    const formData = new FormData();
    formData.append("file", data.file);
    formData.append("description", data.description);

    const response = await http.post(URL, formData);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostTocFileByTocIdResponse>;
  } catch (error) {
    return handleApiError<IPostTocFileByTocIdResponse>(error);
  }
};
