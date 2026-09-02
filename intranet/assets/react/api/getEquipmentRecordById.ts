import qs from "qs";
import { client } from "../store";
import { IApiResponse } from "../types/IApiResponse";
import { IGetEquipmentRecordByIdResponse } from "../types/IGetEquipmentRecordByIdResponse";
import { handleError } from "../utils/utils";
import { IPagination } from "../types/IPagination";

interface IGetEquipmentRecordByIdApiPayload {
  id: string;
  normalization_groups?: Array<string>;
}

interface IGetEquipmentRecordByIdApiParams extends IPagination {
  normalization_groups?: Array<string>;
}

export const getEquipmentRecordById = async (
  data: IGetEquipmentRecordByIdApiPayload
): Promise<IApiResponse<IGetEquipmentRecordByIdResponse>> => {
  try {
    const queryParams: IGetEquipmentRecordByIdApiParams = {};
    queryParams.normalization_groups = data.normalization_groups;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const response = await client.get(
      `/equipment_records/${data.id}?${queryString}`
    );
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
