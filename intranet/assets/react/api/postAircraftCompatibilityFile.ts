import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPostAircraftCompatibilityFileResponse } from "../types/IPostAircraftCompatibilityFileResponse";

export interface IPostAircraftCompatibilityFileApiPayload {
  id: number;
  file: FileList | null;
  type: string;
}

export const postAircraftCompatibilityFile = async (
  data: IPostAircraftCompatibilityFileApiPayload
): Promise<IApiResponse<IPostAircraftCompatibilityFileResponse>> => {
  const formData = new FormData();
  const file = data.file?.item(0);
  if (file !== null && file !== undefined) {
    formData.append("file", file);
  }

  formData.append("type", data.type);

  try {
    const response = await client.post(
      `/sales/aircraft_compatibilities/${data.id}/files`,
      formData
    );
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
