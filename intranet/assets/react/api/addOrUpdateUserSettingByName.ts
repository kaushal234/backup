import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IPostUserSettingResponse } from "../types/IPostUserSettingResponse";
import { getAllUserSetting } from "./getAllUserSetting";
import { deleteUserSetting } from "./deleteUserSetting";
import { postUserSetting } from "./postUserSetting";

export interface IAddOrUpdateUserSettingApiPayload {
  name: string;
  settings: object;
}

export const addOrUpdateUserSettingByName = async (
  data: IAddOrUpdateUserSettingApiPayload
): Promise<IApiResponse<IPostUserSettingResponse>> => {
  try {
    const savedSettings = await getAllUserSetting();
    const match = savedSettings.data?.["hydra:member"].find(
      (setting) => setting.name === data.name
    );
    if (match) {
      await deleteUserSetting({ id: `${match.id}` });
    }
    const response = await postUserSetting({
      ...data,
      user: window.user.iriId ?? "",
    });
    return {
      status: response.status,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
