import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPostAircraftCompatibilityResponse } from "../types/IPostAircraftCompatibilityResponse";

export interface IPostAircraftCompatibilityApiPayload {
  products: Array<string>;
  aircrafts: Array<string>;
  files: Array<IPostAircraftCompatibilityFileLineApiPayload>;
}

export interface IPostAircraftCompatibilityFileLineApiPayload {
  file: FileList | null;
  type: string;
}

export const postAircraftCompatibility = async (
  data: IPostAircraftCompatibilityApiPayload
): Promise<IApiResponse<IPostAircraftCompatibilityResponse>> => {
  const formData = new FormData();
  const types: Array<string> = [];
  (data.files ?? []).forEach(({ file, type }, index) => {
    if (file !== null) {
      const fileToSend = file.item(0);
      if (fileToSend !== null) {
        formData.append(`${index}`, fileToSend);
      }
    }
    types.push(type);
  });

  formData.append("types", JSON.stringify(types));
  formData.append("products", JSON.stringify(data.products));
  formData.append("aircrafts", JSON.stringify(data.aircrafts));

  try {
    const response = await client.post(
      "/sales/aircraft_compatibilities",
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
