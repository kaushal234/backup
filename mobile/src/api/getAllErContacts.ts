import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllContactResponse } from "../@type/IGetAllContactResponse";

interface IGetAllErContactApiPayload {
  equipmentRecord?: string;
}

interface IGetAllErContactApiParams {
  relatedToEquipmentRecord?: string;
  normalization_groups: Array<"extranet_user_acls">;
}

const DEFAULT_QUERY_PARAMS: IGetAllErContactApiParams = {
  normalization_groups: ["extranet_user_acls"],
};

export const getAllErContacts = async (data: IGetAllErContactApiPayload) => {
  try {
    const queryParams: IGetAllErContactApiParams = DEFAULT_QUERY_PARAMS;
    // filter
    queryParams.relatedToEquipmentRecord = data.equipmentRecord;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/sales/extranet_users?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });

    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllContactResponse>;
  } catch (error) {
    return handleApiError<IGetAllContactResponse>(error);
  }
};
