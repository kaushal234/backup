import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IMetaData } from "../@type/IMetaData";
import { IPostCommentByIdResponse } from "../@type/IPostCommentByIdResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IPostCommentByTocIdApiPayload {
  "@id": string;
  comment: string;
  file?: File;
  fileDescription?: string;
  public: boolean;
  metadata?: IMetaData;
}

export const postCommentById = async (data: IPostCommentByTocIdApiPayload) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/comments`;
    const formData = new FormData();
    if (data.file) {
      formData.append("file", data.file);
    }
    if (data.fileDescription) {
      formData.append("fileDescription", data.fileDescription);
    }
    formData.append("resource", data["@id"]);
    formData.append("message", data.comment);
    formData.append("public", `${data.public}`);
    if (data.metadata) {
      formData.append("metadata", JSON.stringify(data.metadata));
    }
    const response = await http.post(URL, formData);

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostCommentByIdResponse>;
  } catch (error) {
    return handleApiError<IPostCommentByIdResponse>(error);
  }
};
