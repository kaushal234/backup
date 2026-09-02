import qs from "qs";
import { handleApiError } from "../utils/api";
import http from "./config";
import { IBasicApiResponse } from "../@type/IBasicApiResponse";
import { IGetAllCustomerRelationshipTeamResponse } from "../@type/IGetAllCustomerRelationshipTeamResponse";

interface IGetAllCustomerRelationshipTeamApiPayload {
  customer: string;
}

interface IGetAllCustomerRelationshipTeamApiParams {
  customer?: string;
}

export const getAllCustomerRelationshipTeam = async (
  data: IGetAllCustomerRelationshipTeamApiPayload
) => {
  try {
    const queryParams: IGetAllCustomerRelationshipTeamApiParams = {};
    queryParams.customer = data.customer;
    const queryString = qs.stringify(queryParams, { arrayFormat: "brackets" });
    const url = `${process.env.REACT_APP_API_BASE_URL}/sales/customer_relationship_teams?${queryString}`;
    const response = await http.get(url, { fetchFirst: true });
    return {
      status: response.status,
      data: response.data,
    } as IBasicApiResponse<IGetAllCustomerRelationshipTeamResponse>;
  } catch (error) {
    return handleApiError<IGetAllCustomerRelationshipTeamResponse>(error);
  }
};
