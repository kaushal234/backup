import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IGetManualDocumentByIdApiPayload {
  manualDocumentId: string;
  fileId: string;
  extension: string;
  documentId: string;
}

export interface IGetManualDocumentByIdApiResponse {
  blob?: Blob;
  url?: string;
}

export const getManualDocumentPdfById = async (
  data: IGetManualDocumentByIdApiPayload
) => {
  try {
    const url =
      data.extension === "pdf"
        ? `${process.env.REACT_APP_API_BASE_URL}/support/manual_documents/${data.manualDocumentId}/files/${data.documentId}`
        : `${process.env.REACT_APP_API_BASE_URL}/support/manual_documents/${data.manualDocumentId}/pdf/document`;
    const response = await http.get(url, { isBlob: true });
    return {
      status: response.status,
      data: {
        blob: response.data,
        url: response.data && URL.createObjectURL(response.data),
      },
    } as IBasicApiResponse<IGetManualDocumentByIdApiResponse>;
  } catch (error) {
    return handleApiError<IGetManualDocumentByIdApiResponse>(error);
  }
};
