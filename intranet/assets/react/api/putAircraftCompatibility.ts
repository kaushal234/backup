import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPutAircraftCompatibilityResponse } from "../types/IPutAircraftCompatibilityResponse";

export interface IPutAircraftCompatibilityApiPayload {
  id: string;
  products: Array<string>;
  aircrafts: Array<string>;
}

export const putAircraftCompatibility = async (
  data: IPutAircraftCompatibilityApiPayload
): Promise<IApiResponse<IPutAircraftCompatibilityResponse>> => {
  const formData = new FormData();

  formData.append("products", JSON.stringify(data.products));
  formData.append("aircrafts", JSON.stringify(data.aircrafts));

  try {
    const response = await client.post(
      `/sales/aircraft_compatibilities/${data.id}`,
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
