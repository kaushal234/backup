import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPostContractFileResponse } from "../types/IPostContractFileResponse";

export interface IPostContractFileApiPayload {
  id: number;
  file: File | null;
}

export const postContractFile = async (
  data: IPostContractFileApiPayload
): Promise<IApiResponse<IPostContractFileResponse>> => {
  const formData = new FormData();
  if (data.file) {
    formData.append("file", data.file);
  }

  try {
    const response = await client.post(`/contracts/${data.id}/files`, formData);
    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
