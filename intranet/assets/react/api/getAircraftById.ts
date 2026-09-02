import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAircraftByIdResponse } from "../types/IGetAllAircraftsResponse";

interface IGetAircraftByIdApiPayload {
  id: string;
}

export const getAircraftById = async (
  data: IGetAircraftByIdApiPayload
): Promise<IApiResponse<IGetAircraftByIdResponse>> => {
  try {
    const response = await client.get(`/sales/aircrafts/${data.id}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
