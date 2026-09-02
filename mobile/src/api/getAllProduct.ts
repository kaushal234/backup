import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllProductResponse } from "../@type/IGetAllProductResponse";

interface IGetAllProductApiPayload {
  searchText?: string;
}

interface IGetAllProductApiParams {
  q?: string;
}

export const getAllProduct = async (data: IGetAllProductApiPayload) => {
  try {
    const queryParams: IGetAllProductApiParams = {};
    if (data.searchText) {
      queryParams.q = data.searchText;
    }
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/sales/products?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllProductResponse>;
  } catch (error) {
    return handleApiError<IGetAllProductResponse>(error);
  }
};
