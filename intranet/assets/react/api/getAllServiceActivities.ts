import { handleApiError } from "../utils/api";
import { client } from "../store";
import { IBasicApiResponse } from "../types/IBasicApiResponse";
import { IGetAllServiceActivitiesResponse } from "../types/IGetAllServiceActivitiesResponse";

export const getAllServiceActivities = async () => {
  try {
    const response = await client.get(`/service/service_activities`);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllServiceActivitiesResponse>;
  } catch (error) {
    return handleApiError<IGetAllServiceActivitiesResponse>(error);
  }
};
