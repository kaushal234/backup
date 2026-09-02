import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IGetLocationByIdResponse } from "../types/IGetLocationByIdResponse";

interface IGetLocationByIdApiPayload {
  id: string;
}

export const getLocationById = async (
  data: IGetLocationByIdApiPayload
): Promise<IApiResponse<IGetLocationByIdResponse>> => {
  try {
    const response = await client.get(`/locations/${data.id}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
