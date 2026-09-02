import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IPostExtranetUserApiResponse } from "../@type/IPostExtranetUserApiResponse";
import { handleApiError } from "../utils/api";
import http from "./config";

export interface IPostExtranetUserApiPayload {
  extranetUser: IExtranetUser;
  customerRelationshipTeam: string;
  groupName: "role_ST";
}

interface IExtranetUser {
  firstname: string;
  lastname: string;
  email: string;
  username: string;
  phones: Array<IPhone>;
  disabled: false;
  extranetUserProfile: IExtranetUserProfile;
}

interface IPhone {
  type: "phone";
  number: string;
}

interface IExtranetUserProfile {
  country: string;
  department: string;
  division: string;
  jobTitle: string;
  language: string;
}

export const postExtranetUser = async (data: IPostExtranetUserApiPayload) => {
  try {
    const URL = `${process.env.REACT_APP_API_BASE_URL}/sales/extranet_users/with_crt_and_group`;
    const response = await http.post(URL, data);
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IPostExtranetUserApiResponse>;
  } catch (error) {
    return handleApiError<IPostExtranetUserApiResponse>(error);
  }
};
