import qs from "qs";
import { IPagination } from "../types/IPagination";
import { handleError, handlePaginationQueryParams } from "../utils/utils";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllPeopleResponse } from "../types/IGetAllPeopleResponse";
import { ISort } from "../types/ISort";

interface IGetAllPeopleApiPayload extends IPagination {
  searchText?: string;
  pagination?: boolean;
  "order[lastname]"?: ISort;
  "order[firstname]"?: ISort;
  firstname?: string;
  lastname?: string;
  mentor?: string;
  supervisor?: string;
  hasGroups?: Array<string>;
}

interface IGetAllPeopleApiParams extends IPagination {
  department?: Array<string>;
  q?: string;
  "order[lastname]"?: ISort;
  "order[firstname]"?: ISort;
  firstname?: string;
  lastname?: string;
  mentor?: string;
  supervisor?: string;
  has_groups?: Array<string>;
  hidden: "0" | "1";
  disabled: "0" | "1";
  normalization_groups_override: Array<"people_list">;
  pagination: boolean;
}

const DEFAULT_QUERY_PARAMS: IGetAllPeopleApiParams = {
  "order[lastname]": "asc",
  hidden: "0",
  disabled: "0",
  normalization_groups_override: ["people_list"],
  pagination: false,
};

export const getAllPeople = async (
  data: IGetAllPeopleApiPayload
): Promise<IApiResponse<IGetAllPeopleResponse>> => {
  try {
    let queryParams: IGetAllPeopleApiParams = DEFAULT_QUERY_PARAMS;
    queryParams = handlePaginationQueryParams(data, queryParams);
    // filter
    if (data.searchText) {
      queryParams.q = data.searchText;
    }
    queryParams.firstname = data.firstname;
    queryParams.lastname = data.lastname;
    queryParams.mentor = data.mentor;
    queryParams.supervisor = data.supervisor;
    queryParams.has_groups = data.hasGroups;
    // sort
    if (data["order[lastname]"]) {
      queryParams["order[lastname]"] = data["order[lastname]"];
    }
    if (data["order[firstname]"]) {
      delete queryParams["order[lastname]"];
      queryParams["order[firstname]"] = data["order[firstname]"];
    }
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/people?${queryString}`);

    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
