import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPostAircraftResponse } from "../types/IPostAircraftResponse";

export interface IPostAircraftApiPayload {
  name: string;
  manufacturer: string;
}

export const postAircraft = async (
  data: IPostAircraftApiPayload
): Promise<IApiResponse<IPostAircraftResponse>> => {
  try {
    const response = await client.post("/sales/aircrafts", data);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
