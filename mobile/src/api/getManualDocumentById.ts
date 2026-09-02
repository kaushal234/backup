import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetManualDocumentResponse } from "../@type/IGetManualDocumentResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

interface IGetManualDocumentByIdApiPayload {
  id: string;
}

export const getManualDocumentById = async (
  data: IGetManualDocumentByIdApiPayload
) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/support/manual_documents/${data.id}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetManualDocumentResponse>;
  } catch (error) {
    return handleApiError<IGetManualDocumentResponse>(error);
  }
};
