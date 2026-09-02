import type { IApiResponse } from "../types/IApiResponse";
import type { IDocumentTranslation } from "../types/IDocumentTranslation";
import { handleError } from "../utils/utils";
import { getTranslateDocumentsInformation } from "./getTranslateDocumentsInformation";
import { refreshTranslateDocumentStatus } from "./refreshTranslateDocumentStatus";

export const getUpdatedTranslateDocumentsInformation = async (): Promise<
  IApiResponse<Array<IDocumentTranslation>>
> => {
  try {
    const response = await getTranslateDocumentsInformation();
    const pendingDocuments = (response.data ?? [])
      .filter((item) => item.status === "queued")
      .map((item) => item.id);
    if (!pendingDocuments.length) {
      return {
        status: 200,
        data: response.data,
      };
    }
    const promises = pendingDocuments.map((id) =>
      refreshTranslateDocumentStatus({ documentId: id ?? 0 })
    );
    await Promise.all(promises);
    const finalResponse = await getTranslateDocumentsInformation();
    return {
      status: 200,
      data: finalResponse.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
