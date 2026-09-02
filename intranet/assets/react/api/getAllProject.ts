import qs from "qs";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllProjectResponse } from "../types/IGetAllProjectResponse";
import { handleError } from "../utils/utils";

export interface IGetAllProjectApiPayload {
  indicesFactor?: Array<string>;
  projectManager?: Array<string>;
  misOwner?: Array<string>;
  moduleKeyUsers?: Array<string>;
  misMembers?: Array<string>;
  tags?: Array<string>;
  businessUnit?: Array<string>;
  module?: Array<string>;
}

interface IGetAllProjectApiParams {
  indicesFactor?: Array<string>;
  projectManager?: Array<string>;
  misOwner?: Array<string>;
  moduleKeyUsers?: Array<string>;
  misMembers?: Array<string>;
  tags?: Array<string>;
  businessUnit?: Array<string>;
  module?: Array<string>;
  status: Array<string>;
  "order[id]": "desc";
}

const DEFAULT_QUERY_PARAMS: IGetAllProjectApiParams = {
  status: ["PENDING", "PHASE 0", "PHASE 1", "PHASE 2", "PHASE 3", "PHASE 4"],
  "order[id]": "desc",
};

export const getAllProject = async (
  data: IGetAllProjectApiPayload
): Promise<IApiResponse<IGetAllProjectResponse>> => {
  try {
    const queryParams: IGetAllProjectApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.indicesFactor = data.indicesFactor;
    queryParams.projectManager = data.projectManager;
    queryParams.misOwner = data.misOwner;
    queryParams.moduleKeyUsers = data.moduleKeyUsers;
    queryParams.misMembers = data.misMembers;
    queryParams.tags = data.tags;
    queryParams.businessUnit = data.businessUnit;
    queryParams.module = data.module;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/mis/projects?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
