import qs from "qs";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllExtranetUsersResponse } from "../types/IGetAllExtranetUsersResponse";
import { IPagination } from "../types/IPagination";
import { handleError } from "../utils/utils";

interface IGetAllExtranetUsersRelatedToErApiPayload {
  equipmentRecordIri: string;
  searchText?: string;
}
interface IGetAllExtranetUsersRelatedToErApiParams extends IPagination {
  relatedToEquipmentRecord?: string;
  normalization_groups?: Array<string>;
  q?: string;
}

const DEFAULT_QUERY_PARAMS: IGetAllExtranetUsersRelatedToErApiParams = {
  itemsPerPage: 100,
  page: 1,
};

export const getAllExtranetUsersRelatedToEr = async (
  data: IGetAllExtranetUsersRelatedToErApiPayload
): Promise<IApiResponse<IGetAllExtranetUsersResponse>> => {
  try {
    const queryParams: IGetAllExtranetUsersRelatedToErApiParams = {
      ...DEFAULT_QUERY_PARAMS,
    };
    // filter
    queryParams.relatedToEquipmentRecord = data.equipmentRecordIri;
    queryParams.normalization_groups = ["extranet_user_acls"];
    queryParams.q = data.searchText;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(`/sales/extranet_users?${queryString}`);
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
