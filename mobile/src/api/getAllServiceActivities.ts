import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllServiceActivitiesResponse } from "../@type/IGetAllServiceActivitiesResponse";

export const getAllServiceActivities = async () => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/service/service_activities`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllServiceActivitiesResponse>;
  } catch (error) {
    return handleApiError<IGetAllServiceActivitiesResponse>(error);
  }
};
