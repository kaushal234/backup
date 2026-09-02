import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllFeaturesResponse } from "../@type/IGetAllFeaturesResponse";

interface IGetFeaturesApiPayload {
  peopleUri: string;
  token: string;
}

interface IGetAllFeaturesApiParams {
  "groups.acls.user": string;
  normalization_groups_override: Array<"feature_list">;
}

const DEFAULT_QUERY_PARAMS: IGetAllFeaturesApiParams = {
  "groups.acls.user": "",
  normalization_groups_override: ["feature_list"],
};

export const getFeatures = async (data: IGetFeaturesApiPayload) => {
  try {
    const queryParams: IGetAllFeaturesApiParams = DEFAULT_QUERY_PARAMS;
    queryParams["groups.acls.user"] = data.peopleUri;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });

    const url = `${process.env.REACT_APP_API_BASE_URL}/features?${queryString}`;
    const response = await http.get(url, {
      fetchAlways: true,
      authRequired: false,
      headers: {
        Authorization: `Bearer ${data.token}`,
      },
    });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllFeaturesResponse>;
  } catch (error) {
    return handleApiError<IGetAllFeaturesResponse>(error);
  }
};
