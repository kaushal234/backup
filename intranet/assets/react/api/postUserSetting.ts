import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPostUserSettingResponse } from "../types/IPostUserSettingResponse";

export interface IPostUserSettingApiPayload {
  user: string;
  name: string;
  settings: object;
}

export const postUserSetting = async (
  data: IPostUserSettingApiPayload
): Promise<IApiResponse<IPostUserSettingResponse>> => {
  try {
    const response = await client.post("/user_settings", data);
    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
