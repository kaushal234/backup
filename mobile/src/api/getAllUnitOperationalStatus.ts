import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllUnitOperationalStatusResponse } from "../@type/IGetAllUnitOperationalStatusResponse";

export const getAllUnitOperationalStatus = async () => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/unit_operational_statuses`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllUnitOperationalStatusResponse>;
  } catch (error) {
    return handleApiError<IGetAllUnitOperationalStatusResponse>(error);
  }
};
