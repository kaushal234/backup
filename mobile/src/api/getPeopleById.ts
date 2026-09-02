import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetPeopleResponse } from "../@type/IGetPeopleResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

interface IGetPeopleByIdApiPayload {
  id: string;
}

export const getPeopleById = async (data: IGetPeopleByIdApiPayload) => {
  try {
    const url = `${process.env.REACT_APP_API_BASE_URL}/people/${data.id}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetPeopleResponse>;
  } catch (error) {
    return handleApiError<IGetPeopleResponse>(error);
  }
};
