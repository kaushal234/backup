import qs from "qs";
import { client } from "../store";
import type { IApiResponse } from "../types/IApiResponse";
import type { IDocumentTranslation } from "../types/IDocumentTranslation";
import { handleError } from "../utils/utils";

interface IGetTranslateDocumentsInformationApiParams {
  "order[createdAt]": "desc";
}

const DEFAULT_QUERY_PARAMS: IGetTranslateDocumentsInformationApiParams = {
  "order[createdAt]": "desc",
};

export const getTranslateDocumentsInformation = async (): Promise<
  IApiResponse<Array<IDocumentTranslation>>
> => {
  try {
    const queryParams: IGetTranslateDocumentsInformationApiParams =
      DEFAULT_QUERY_PARAMS;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/document_translations?${queryString}`);
    return {
      status: 200,
      data: response.data["hydra:member"],
    };
  } catch (error) {
    return handleError(error);
  }
};
