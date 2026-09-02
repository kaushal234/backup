import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";

export interface IDeletetUserSettingApiPayload {
  id: string;
}

export const deleteUserSetting = async (
  data: IDeletetUserSettingApiPayload
): Promise<IApiResponse<null>> => {
  try {
    const response = await client.delete(`/user_settings/${data.id}`);
    return {
      status: response.status,
      data: null,
    };
  } catch (error) {
    return handleError(error);
  }
};
