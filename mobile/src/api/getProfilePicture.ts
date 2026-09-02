import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";

export interface IGetProfilePictureApiPayload {
  peopleId: string;
  photoId: string;
}

export interface IGetProfilePictureApiResponse {
  blob?: Blob;
  url?: string;
}
export const getProfilePicture = async (data: IGetProfilePictureApiPayload) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/people/${data.peopleId}/photo/${data.photoId}`;
    const response = await http.get(url, { isBlob: true, fetchFirst: true });

    return {
      status: response.status,
      data: {
        blob: response.data,
        url: response.data && URL.createObjectURL(response.data),
      },
    } as IBasicApiResponse<IGetProfilePictureApiResponse>;
  } catch (error) {
    return handleApiError<IGetProfilePictureApiResponse>(error);
  }
};
