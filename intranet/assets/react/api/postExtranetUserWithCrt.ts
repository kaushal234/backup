import { client } from "../store";
import { handleError } from "../utils/utils";
import { IApiResponse } from "../types/IApiResponse";
import { IGetExtranetUserByIdResponse } from "../types/IGetExtranetUserByIdResponse";

export interface IPostExtranetUserWithCrtApiPayload {
  extranetUser: IExtranetUser;
  customerRelationshipTeam: string;
  groupName: string;
}

interface IExtranetUser {
  firstname: string;
  lastname: string;
  email: string;
  username: string;
  phones: Array<IPhone>;
  disabled: boolean;
  extranetUserProfile: IExtranetUserProfile;
}

export interface IPhone {
  type: string;
  number: string;
}
export interface IExtranetUserProfile {
  country: string;
  department: string;
  division: string;
  jobTitle: string;
  language?: string;
}

export const postExtranetUserWithCrt = async (
  data: IPostExtranetUserWithCrtApiPayload
): Promise<IApiResponse<IGetExtranetUserByIdResponse>> => {
  try {
    const response = await client.post(
      `/sales/extranet_users/with_crt_and_group`,
      data
    );
    return {
      status: 200,
      data: response.data,
    };
  } catch (error) {
    return handleError(error);
  }
};
