import qs from "qs";
import { handleApiError, handlePaginationQueryParams } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPagination } from "../@type/IPagination";
import { IGetAllPeopleSearchResponse } from "../@type/IGetAllPeopleSearchResponse";

interface IGetAllPeopleApiPayload extends IPagination {
  searchText?: string;
}

interface IGetAllPeopleApiParams extends IPagination {
  q?: string;
  "order[lastname]": "asc";
  "order[firstname]"?: "asc";
  hidden: "0" | "1";
  disabled: "0" | "1";
}

const DEFAULT_QUERY_PARAMS: IGetAllPeopleApiParams = {
  "order[lastname]": "asc",
  hidden: "0",
  disabled: "0",
  page: 1,
  itemsPerPage: 10,
};

export const getAllPeopleSearch = async (data: IGetAllPeopleApiPayload) => {
  try {
    let queryParams: IGetAllPeopleApiParams = DEFAULT_QUERY_PARAMS;
    queryParams = handlePaginationQueryParams(data, queryParams);
    // filter
    if (data.searchText) {
      queryParams.q = data.searchText;
    }
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/people/search?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllPeopleSearchResponse>;
  } catch (error) {
    return handleApiError<IGetAllPeopleSearchResponse>(error);
  }
};
