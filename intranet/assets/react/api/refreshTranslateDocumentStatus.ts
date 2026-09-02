import { client } from "../store";
import { IDocumentTranslation } from "../types/IDocumentTranslation";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";

interface IRefreshTranslateDocumentStatusApiPayload {
  documentId: number;
}

export const refreshTranslateDocumentStatus = async (
  data: IRefreshTranslateDocumentStatusApiPayload
): Promise<IApiResponse<IDocumentTranslation>> => {
  try {
    const response = await client.get(
      `/document_translations/${data.documentId}/refresh`
    );
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
