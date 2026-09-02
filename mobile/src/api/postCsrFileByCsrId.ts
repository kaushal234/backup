import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostCsrFileByCsrIdResponse } from "../@type/IPostCsrFileByCsrIdResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IPostCsrFileByCsrIdApiPayload {
  csrId: string;
  file: File;
  description: string;
}

export const postCsrFileByCsrId = async (
  data: IPostCsrFileByCsrIdApiPayload
) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/service/customer_service_records/${data.csrId}/files`;
    const formData = new FormData();
    formData.append("file", data.file);
    formData.append("description", data.description);

    const response = await http.post(URL, formData);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostCsrFileByCsrIdResponse>;
  } catch (error) {
    return handleApiError<IPostCsrFileByCsrIdResponse>(error);
  }
};
