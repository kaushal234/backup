import qs from "qs";
import { handleApiError, handlePaginationQueryParams } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPagination } from "../@type/IPagination";
import { IGetAllPeopleResponse } from "../@type/IGetAllPeopleResponse";

interface IGetAllPeopleApiPayload extends IPagination {
  searchText?: string;
  isCsr?: boolean;
  hasGroups?: Array<"GG_SERVICE" | "GG_SERVICE_AGENTS">;
}

interface IGetAllPeopleApiParams extends IPagination {
  department?: Array<string>;
  q?: string;
  "order[lastname]": "asc";
  "order[firstname]"?: "asc";
  hidden: "0" | "1";
  disabled: "0" | "1";
  normalization_groups_override: Array<"people_list">;
  pagination: boolean;
  has_groups?: Array<"GG_SERVICE" | "GG_SERVICE_AGENTS">;
}

const DEFAULT_QUERY_PARAMS: IGetAllPeopleApiParams = {
  "order[lastname]": "asc",
  hidden: "0",
  disabled: "0",
  normalization_groups_override: ["people_list"],
  pagination: false,
};

const CSR_QUERY_PARAMS: IGetAllPeopleApiParams = {
  department: ["departments/1", "/departments/2"],
  hidden: "0",
  disabled: "0",
  normalization_groups_override: ["people_list"],
  "order[lastname]": "asc",
  "order[firstname]": "asc",
  pagination: false,
};

export const getAllPeople = async (data: IGetAllPeopleApiPayload) => {
  try {
    let queryParams: IGetAllPeopleApiParams = data.isCsr
      ? CSR_QUERY_PARAMS
      : DEFAULT_QUERY_PARAMS;
    queryParams = handlePaginationQueryParams(data, queryParams);
    // filter
    if (data.searchText) {
      queryParams.q = data.searchText;
    }
    if (data.hasGroups) {
      queryParams.has_groups = data.hasGroups;
    }
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/people?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllPeopleResponse>;
  } catch (error) {
    return handleApiError<IGetAllPeopleResponse>(error);
  }
};
