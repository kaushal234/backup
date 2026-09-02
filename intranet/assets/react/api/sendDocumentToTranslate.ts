import { client } from "../store";
import { IDocumentTranslation } from "../types/IDocumentTranslation";
import { IApiResponse } from "../types/IApiResponse";
import { handleError } from "../utils/utils";

interface ISendDocumentToTranslateApiPayload {
  file: File;
  targetLang: string;
  formality: string;
}

export const sendDocumentToTranslate = async ({
  file,
  targetLang,
  formality,
}: ISendDocumentToTranslateApiPayload): Promise<
  IApiResponse<IDocumentTranslation>
> => {
  try {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("targetLang", targetLang);
    formData.append("formality", formality);
    const response = await client.post("/document_translation", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
