import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IGetAllUserSettingResponse } from "../types/IGetAllUserSettingResponse";

export const getAllUserSetting = async (): Promise<
  IApiResponse<IGetAllUserSettingResponse>
> => {
  try {
    const response = await client.get("/user_settings");
    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
