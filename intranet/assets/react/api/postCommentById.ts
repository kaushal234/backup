import { IMetaData } from "../types/IMetaData";
import { client } from "../store";
import { IBasicApiResponse } from "../types/IBasicApiResponse";
import { handleApiError } from "../utils/api";

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
    const URL = `/comments`;
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
    const response = await client.post(URL, formData);

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<unknown>;
  } catch (error) {
    return handleApiError<unknown>(error);
  }
};
