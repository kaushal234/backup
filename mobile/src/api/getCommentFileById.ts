import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IGetCommentFileByIdApiPayload {
  commentId: string;
  fileId: string;
}

export interface IGetCommentFileByIdApiResponse {
  blob?: Blob;
  url?: string;
}

export const getCommentFileById = async (
  data: IGetCommentFileByIdApiPayload
) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/comments/${data.commentId}/files/${data.fileId}`;
    const response = await http.get(url, { isBlob: true });
    return {
      status: response.status,
      data: {
        blob: response.data,
        url: response.data && URL.createObjectURL(response.data),
      },
    } as IBasicApiResponse<IGetCommentFileByIdApiResponse>;
  } catch (error) {
    return handleApiError<IGetCommentFileByIdApiResponse>(error);
  }
};
