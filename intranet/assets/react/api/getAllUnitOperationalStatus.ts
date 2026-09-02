import { handleApiError } from "../utils/api";
import { client } from "../store";
import { IBasicApiResponse } from "../types/IBasicApiResponse";
import { IGetAllUnitOperationalStatusResponse } from "../types/IGetAllUnitOperationalStatusResponse";

export const getAllUnitOperationalStatus = async () => {
  try {
    const response = await client.get(`/unit_operational_statuses`);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllUnitOperationalStatusResponse>;
  } catch (error) {
    return handleApiError<IGetAllUnitOperationalStatusResponse>(error);
  }
};
