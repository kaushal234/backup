import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllProductTypeResponse } from "../@type/IGetAllProductTypeResponse";

interface IGetAllProductTypeApiPayload {
  searchText?: string;
}

interface IGetAllProductTypeApiParams {
  q?: string;
}

export const getAllProductTypes = async (
  data: IGetAllProductTypeApiPayload
) => {
  try {
    const queryParams: IGetAllProductTypeApiParams = {};
    if (data.searchText) {
      queryParams.q = data.searchText;
    }
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/sales/product_types?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllProductTypeResponse>;
  } catch (error) {
    return handleApiError<IGetAllProductTypeResponse>(error);
  }
};
