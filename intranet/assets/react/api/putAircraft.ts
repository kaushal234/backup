import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPutAircraftResponse } from "../types/IPutAircraftResponse";

export interface IPutAircraftApiPayload {
  id: string;
  name: string;
  manufacturer: string;
}

export const putAircraft = async (
  data: IPutAircraftApiPayload
): Promise<IApiResponse<IPutAircraftResponse>> => {
  try {
    const response = await client.put(`/sales/aircrafts/${data.id}`, data);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
