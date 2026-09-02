import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostTocMainFileByTocIdResponse } from "../@type/IPostTocMainFileByTocIdResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IPostTocMainFileByTocIdApiPayload {
  tocId: string;
  file: File;
  description: string;
}

export const postTocMainFileByTocId = async (
  data: IPostTocMainFileByTocIdApiPayload
) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/service/technician_on_calls/${data.tocId}/main_file`;
    const formData = new FormData();
    formData.append("file", data.file);
    formData.append("description", data.description);

    const response = await http.post(URL, formData);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostTocMainFileByTocIdResponse>;
  } catch (error) {
    return handleApiError<IPostTocMainFileByTocIdResponse>(error);
  }
};
