import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { ILocation } from "../@type/IGetAllLocationResponse";

interface IGetLocationByIdApiPayload {
  id: string;
}

export const getLocationById = async (data: IGetLocationByIdApiPayload) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/locations/${data.id}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<ILocation>;
  } catch (error) {
    return handleApiError<ILocation>(error);
  }
};
