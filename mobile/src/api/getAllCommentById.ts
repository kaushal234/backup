import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import {
  IGetAllCommentRawResponse,
  IGetAllCommentResponse,
} from "../@type/IGetAllCommentResponse";

interface IGetCommentByTocIdApiPayload {
  "@id": string;
}

interface IGetCommentByTocIdApiParams {
  resource?: string;
  normalizationGroups?: Array<"activity_position" | "file:light">;
  pagination?: boolean;
}

const DEFAULT_QUERY_PARAMS: IGetCommentByTocIdApiParams = {
  normalizationGroups: ["activity_position", "file:light"],
  pagination: false,
};

export const fixMetadata = (
  data: IGetAllCommentRawResponse
): IGetAllCommentResponse => {
  return {
    ...data,
    "hydra:member": data["hydra:member"].map((comment) => ({
      ...comment,
      metadata: Array.isArray(comment.metadata) ? null : comment.metadata,
    })),
  };
};

export const getAllComment = async (data: IGetCommentByTocIdApiPayload) => {
  try {
    const queryParams: IGetCommentByTocIdApiParams = DEFAULT_QUERY_PARAMS;
    queryParams.resource = data["@id"];

    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/comments?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: fixMetadata(response.data),
    } as IBasicApiResponse<IGetAllCommentResponse>;
  } catch (error) {
    return handleApiError<IGetAllCommentResponse>(error);
  }
};
