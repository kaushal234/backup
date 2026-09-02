import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAircraftCompatibilityByIdResponse } from "../types/IGetAllAircraftCompatibilitiesResponse";

interface IGetAircraftCompatibilityByIdApiPayload {
  id: string;
}

export const getAircraftCompatibilityById = async (
  data: IGetAircraftCompatibilityByIdApiPayload
): Promise<IApiResponse<IGetAircraftCompatibilityByIdResponse>> => {
  try {
    const response = await client.get(
      `/sales/aircraft_compatibilities/${data.id}`
    );
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
